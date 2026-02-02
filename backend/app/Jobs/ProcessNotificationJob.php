<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AI\ChatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationLog;

    /**
     * Create a new job instance.
     */
    public function __construct(NotificationLog $notificationLog)
    {
        $this->notificationLog = $notificationLog;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatService $chatService): void
    {
        $log = $this->notificationLog;

        // Skip if already processed
        if ($log->status !== 'pending') {
            return;
        }

        try {
            $user = User::find($log->user_id);
            if (!$user) {
                $log->update(['status' => 'failed', 'error' => 'User not found']);
                return;
            }

            // Construct Prompt
            $pkg = $log->package_name;
            $title = $log->title ?? 'Notification';
            $content = $log->content;

            $systemPrompt = <<<PROMPT
You are a helpful banking assistant. The user received a notification from their bank app.
Extract the transaction details and use the 'create_transaction' tool to record it.
If the notification is NOT a transaction (e.g., ads, login alert), just reply "Not a transaction".

Notification Details:
- App: $pkg
- Title: $title
- Content: $content
PROMPT;

            $userMessage = "Process this notification: $content";

            // Call AI
            // We use runWithTools which is non-streaming and suitable for background jobs
            $response = $chatService->runWithTools($user->id, $systemPrompt, $userMessage);

            $log->update([
                'status' => 'processed',
                'result' => $response
            ]);

            Log::info("Notification processed for User {$user->id}: {$response}");
        } catch (\Exception $e) {
            Log::error("Failed to process notification log {$log->id}: " . $e->getMessage());
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
        }
    }
}
