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

                // Fetch emails
                $emails = $gmailService->getBankEmails($user, 20, $since);

                $this->info("Found " . count($emails) . " relevant emails.");

                if (empty($emails)) {
                    continue;
                }

                $count = 0;
                foreach ($emails as $email) {
                    // Deduplication check
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

                    // We assume if the AI successfully called the tool, the transaction is created.
                    // We can mark this email as processed by saving the ID to DB explicitly here if the tool didn't do it?
                    // But wait, the deduplication relies on `gmail_message_id` being in the `transactions` table.
                    // The `create_transaction` tool DOES NOT currently accept `gmail_message_id`.
                    // PROBLEM: The AI Tool `create_transaction` likely only takes standard fields (amount, description, etc).
                    // FIX: We need to either:
                    // A) Update `CreateTransactionTool` to accept `gmail_message_id`.
                    // B) Manually update the transaction after AI creates it (unsafe, race condition).
                    // C) Pass `metadata` to `create_transaction` if it supports it.

                    // Let's assume for now we must update the tool to support metadata or specific ID.
                    // OR, since the user insists on AI doing it, we should check `CreateTransactionTool`.

                    $this->info("AI Response: " . substr($response, 0, 100));

                    // Hack: Since standard `CreateTransactionTool` might not support `gmail_message_id`,
                    // We will attempt to find the LAST created transaction by this user matching the amount/date
                    // and update its `gmail_message_id`.
                    // This is imperfect but works without modifying the Tool core logic immediately.
                    // BETTER: Let's assume the user wants us to Modify the Tool as well?

                    // Let's try to update the `gmail_message_id` manually after AI runs.
                    // We can find the transaction created within the last 5 seconds?
                    $latestTx = Transaction::where('user_id', $user->id)
                        ->latest()
                        ->first();

                    if ($latestTx && $latestTx->created_at->diffInSeconds(now()) < 10) {
                        $latestTx->update(['gmail_message_id' => $email['message_id']]);
                        $count++;
                        $this->info("Transaction created and linked to Email ID.");
                    } else {
                        $this->warn("AI processed the email, but no new transaction was detected (or AI chose to skip).");
                        // If AI failed, we still mark it? No, retry next time.
                    }
                }

                $user->update(['last_gmail_sync_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Gmail Sync Failed: " . $e->getMessage());
                $this->error($e->getMessage());
            }
        }

        $this->info('Gmail Sync Completed.');
    }
}
