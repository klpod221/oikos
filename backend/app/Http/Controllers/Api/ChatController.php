<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for AI Chat with RAG, function calling, and SSE streaming.
 */
class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Send a message and stream the response via SSE.
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function send(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $userId = $request->user()->id;
        $message = $request->input('message');

        return new StreamedResponse(function () use ($userId, $message) {
            // Disable output buffering
            if (ob_get_level()) ob_end_clean();

            // Force flush
            ob_implicit_flush(true);
            flush();

            try {
                foreach ($this->chatService->streamChat($userId, $message) as $event) {
                    $this->sendSSE($event);
                }
            } catch (\Exception $e) {
                Log::error('Chat streaming error', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);

                $this->sendSSE([
                    'type' => 'error',
                    'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.',
                ]);

                $this->sendSSE(['type' => 'done']);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }

    /**
     * Clear chat history for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $userId = $request->user()->id;
        $deleted = $this->chatService->clearHistory($userId);

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared',
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Get chat history for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $limit = $request->input('limit', 50);
        $userId = $request->user()->id;

        $messages = \App\Models\ChatHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ]);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Send SSE event.
     *
     * @param array $data
     * @return void
     */
    private function sendSSE(array $data): void
    {
        echo "data: " . json_encode($data) . "\n\n";

        if (connection_aborted()) {
            exit;
        }

        flush();
    }
}
