<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use App\Services\Google\GmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGmailTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-gmail-transactions {--reset : Reset last sync time to fetch old emails}';

    // ...

    public function handle(GmailService $gmailService, \App\Services\AI\ChatService $chatService)
    {
        $this->info('Starting Gmail Sync (AI Agent Mode)...');

        if ($this->option('reset')) {
            $this->info('Resetting last_gmail_sync_at for all users...');
            User::query()->update(['last_gmail_sync_at' => null]);
        }

        $users = User::whereNotNull('google_access_token')->get();

        foreach ($users as $user) {
            /** @var User $user */
            $this->info("Processing user: {$user->email}");

            // Phase 3: Check if Android Notification is active for this user
            // We assume if they have RECENT logs (last 24h), they are active.
            // Or ideally use a setting. For now, let's use recent logs as heuristic
            // to avoid complex settings if not requested.
            // Actually, user explicitly asked "if android notification is turned on, then do not read from mail".
            // Let's implement a check using SystemSetting or User attribute if available,
            // but since we don't have that column yet, let's use a heuristic:
            // "If we have ANY logs in notification_logs in the last 7 days, assume Android is primary and SKIP email."

            $hasAndroidActivity = \App\Models\NotificationLog::where('user_id', $user->id)
                ->where('created_at', '>', now()->subDays(7))
                ->exists();

            if ($hasAndroidActivity) {
                $this->info("User has active Android Notifications. Skipping Email Sync to prevent duplicates.");
                continue;
            }

            try {
                // Determine 'since' timestamp
                // If reset, it will be null -> default to 30 days ago
                $since = $user->last_gmail_sync_at ? $user->last_gmail_sync_at : now()->subDays(30);

                $this->info("Fetching emails since: " . $since->toDateTimeString());

                $pageToken = null;
                $pageCount = 0;
                $maxPages = 5;
                $latestEmailDate = null; // Track the newest date encountered

                do {
                    $pageCount++;
                    // Fetch emails with pagination
                    $result = $gmailService->getBankEmails($user, 20, $since, $pageToken);
                    $emails = $result['messages'];
                    $pageToken = $result['nextPageToken'];

                    $this->info("Page $pageCount: Found " . count($emails) . " new relevant emails.");

                    if (!empty($emails)) {
                        foreach ($emails as $email) {
                            // Track Latest Date
                            $emailDate = \Carbon\Carbon::parse($email['date']);

                            if (!$latestEmailDate || $emailDate->gt($latestEmailDate)) {
                                $latestEmailDate = $emailDate;
                            }

                            // Deduplication check
                            if (Transaction::where('gmail_message_id', $email['message_id'])->exists()) {
                                continue;
                            }

                            $this->info("Processing Email: {$email['subject']}");

                            // ... AI Processing ...
                            // Determine Today's Date for AI context
                            $today = now()->format('Y-m-d');
                            $systemPrompt = <<<PROMPT
You are an intelligent Financial Assistant. Your task is to process a bank notification email and create a transaction record in the system.
1. **Analyze** the email content provided by the user.
2. **Extract** details: Amount, Type (Income/Expense), Description, Date (use email date if available, otherwise today: {$today}).
3. **Reasoning**:
   - "Biên lai chuyển tiền" usually means I sent money -> Expense.
   - "Số dư tăng" -> Income.
   - "Số dư giảm" -> Expense.
4. **Action**:
   - First, call `get_wallets` to identify the user's wallet.
   - Call `get_categories` to find a relevant category.
   - Finally, call `create_transaction` with the valid wallet name and category name.
   - **CRITICAL**: Use the `gmail_message_id` provided in the context.
PROMPT;

                            $userMessage = <<<MSG
Email Subject: {$email['subject']}
Email Date: {$email['date']}
Email Snippet: {$email['snippet']}
Email Body:
{$email['body']}
Message ID: {$email['message_id']}
Please create a transaction for this email.
MSG;

                            try {
                                $response = $chatService->runWithTools($user->id, $systemPrompt, $userMessage);
                                $this->info("AI Response: " . substr($response, 0, 100));
                            } catch (\Exception $aiEx) {
                                $this->error("AI Failed for email {$email['message_id']}: " . $aiEx->getMessage());
                            }
                        }
                    }

                    // Break if max pages reached
                    if ($pageCount >= $maxPages) {
                        $this->warn("Max pages reached ($maxPages). Stopping sync for now.");
                        break;
                    }
                } while ($pageToken);

                // Update timestamp to the LATEST email date we processed + 1 second
                // This ensures next run starts AFTER this email.
                if ($latestEmailDate) {
                    $user->update(['last_gmail_sync_at' => $latestEmailDate->addSecond()]);
                    $this->info("Updated last_gmail_sync_at to: " . $latestEmailDate->toDateTimeString());
                } else {
                    // If no new emails found, update to NOW so we don't query old range again unnecessarily?
                    // Or keep old check.
                    // Better to set to NOW if we successfully checked up to NOW.
                    // But if pageToken was null (no next page), we are caught up.
                    if ($pageToken === null) {
                        $user->update(['last_gmail_sync_at' => now()]);
                        $this->info("Caught up. Updated last_gmail_sync_at to NOW.");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gmail Sync Failed: " . $e->getMessage());
                $this->error($e->getMessage());
            }
        }

        $this->info('Gmail Sync Completed.');
    }
}
