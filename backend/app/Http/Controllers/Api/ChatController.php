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
            if (ob_get_level()) {
                ob_end_clean();
            }

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
        $limit = $request->input('limit', 10);
        $userId = $request->user()->id;

        $search = $request->input('search');

        $paginator = \App\Models\ChatHistory::where('user_id', $userId)
            ->whereNotIn('role', ['tool', 'system'])
            ->where(function ($query) {
                $query->whereNotNull('content')
                    ->where('content', '!=', '');
            })
            ->when($search, function ($query, $search) {
                return $query->where('content', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        // Transform collection but keep pagination meta
        $messages = collect($paginator->items())
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $messages,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'has_more' => $paginator->hasMorePages(), // Keep for backward compat if needed, standard is checking current < last
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
