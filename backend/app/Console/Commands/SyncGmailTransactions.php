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

            try {
                // Determine 'since' timestamp
                // If reset, it will be null -> default to 30 days ago
                $since = $user->last_gmail_sync_at ? $user->last_gmail_sync_at : now()->subDays(30);

                $this->info("Fetching emails since: " . $since->toDateTimeString());

                $pageToken = null;
                $pageCount = 0;
                $maxPages = 5; // Safety limit to prevent timeouts in one run (e.g. 100 emails max)

                do {
                    $pageCount++;
                    // Fetch emails with pagination
                    $result = $gmailService->getBankEmails($user, 20, $since, $pageToken);
                    $emails = $result['messages'];
                    $pageToken = $result['nextPageToken'];

                    $this->info("Page $pageCount: Found " . count($emails) . " new relevant emails.");

                    if (!empty($emails)) {
                        foreach ($emails as $email) {
                            // Deduplication check (Double check, though Service also checks)
                            if (Transaction::where('gmail_message_id', $email['message_id'])->exists()) {
                                continue;
                            }

                            $this->info("Processing Email: {$email['subject']}");

                            // Determine Today's Date for AI context
                            $today = now()->format('Y-m-d');

                            // Construct Prompt for AI Agent
                            $systemPrompt = <<<PROMPT
You are an intelligent Financial Assistant. Your task is to process a bank notification email and create a transaction record in the system.

1. **Analyze** the email content provided by the user.
2. **Extract** details: Amount, Type (Income/Expense), Description, Date (use email date if available, otherwise today: {$today}).
3. **Reasoning**:
   - "Biên lai chuyển tiền" usually means I sent money -> Expense.
   - "Số dư tăng" -> Income.
   - "Số dư giảm" -> Expense.
4. **Action**:
   - First, call `get_wallets` to identify the user's wallet (e.g., "Tiền mặt", "Chi tiêu").
   - Call `get_categories` to find a relevant category (e.g., "Thu nhập khác", "Chi tiêu khác").
   - Finally, call `create_transaction` with the valid wallet name and category name you found.
   - Amount must be a positive number.
   - Description should be concise.
   - **CRITICAL**: Use the `gmail_message_id` provided in the context if the tool supports it, or ensure duplication is handled.
   - If you cannot determine the details, do nothing or log a warning.
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

                            // Execute AI Agent
                            $response = $chatService->runWithTools($user->id, $systemPrompt, $userMessage);

                            $this->info("AI Response: " . substr($response, 0, 100));

                            // Manual Linkage Hack (if tools didn't handle ID)
                            $latestTx = Transaction::where('user_id', $user->id)
                                ->latest()
                                ->first();

                            if ($latestTx && $latestTx->created_at->diffInSeconds(now()) < 15) {
                                $latestTx->update(['gmail_message_id' => $email['message_id']]);
                                $this->info("Transaction created and linked to Email ID.");
                            } else {
                                $this->warn("AI processing check: No new transaction detected immediately.");
                            }
                        }
                    }

                    // Break if max pages reached
                    if ($pageCount >= $maxPages) {
                        $this->warn("Max pages reached ($maxPages). Stopping sync for now.");
                        break;
                    }
                } while ($pageToken);

                // Only update the sync timestamp if we successfully processed everything (nextPageToken is null)
                // and we didn't hit the max pages limit (which leaves work unfinished)
                if ($pageToken === null && $pageCount < $maxPages) {
                    $user->update(['last_gmail_sync_at' => now()]);
                    $this->info("Sync fully completed. Updated last_gmail_sync_at.");
                } else {
                    $this->info("Sync partial or limited. Keeping old timestamp to resume next time.");
                }
            } catch (\Exception $e) {
                Log::error("Gmail Sync Failed: " . $e->getMessage());
                $this->error($e->getMessage());
            }
        }

        $this->info('Gmail Sync Completed.');
    }
}
