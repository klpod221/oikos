<?php

namespace App\Services\AI;

use App\Models\ChatHistory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Generator;

/**
 * Main chat orchestrator with RAG, function calling, and streaming.
 */
class ChatService
{
    private EmbeddingService $embeddingService;
    private VectorSearchService $vectorSearchService;
    private ToolExecutor $toolExecutor;

    private string $apiUrl;
    private string $apiKey;
    private string $model;
    private int $memoryLimit;

    public function __construct(
        EmbeddingService $embeddingService,
        VectorSearchService $vectorSearchService,
        ToolExecutor $toolExecutor
    ) {
        $this->embeddingService = $embeddingService;
        $this->vectorSearchService = $vectorSearchService;
        $this->toolExecutor = $toolExecutor;

        $this->apiUrl = config('services.openai.url', 'http://localhost:8045/v1');
        $this->apiKey = config('services.openai.key', 'dummy-key');
        $this->model = config('services.openai.model', 'gemini-2.5-flash');
        $this->memoryLimit = config('services.openai.memory_limit', 10);
    }

    /**
     * Process a chat message with RAG and streaming response.
     *
     * @param int $userId
     * @param string $userMessage
     * @return Generator
     */
    public function streamChat(int $userId, string $userMessage): Generator
    {
        // 1. Save user message
        $this->saveMessage($userId, 'user', $userMessage);

        // 2. Build messages array with memory
        $messages = $this->buildMessages($userId);

        // 3. Get RAG context
        $ragContext = $this->getRAGContext($userMessage);
        if ($ragContext) {
            $messages = $this->injectRAGContext($messages, $ragContext);
        }

        // 4. Stream completion with potential tool calls
        yield from $this->streamWithTools($userId, $messages);
    }

    /**
     * Build messages array with conversation history.
     *
     * @param int $userId
     * @return array
     */
    private function buildMessages(int $userId): array
    {
        $messages = [];

        // System prompt
        $messages[] = [
            'role' => 'system',
            'content' => $this->getSystemPrompt(),
        ];

        // Fetch last N messages
        $history = ChatHistory::getRecentMessages($userId, $this->memoryLimit);
        foreach ($history as $msg) {
            $messages[] = $msg->toOpenAIFormat();
        }

        return $messages;
    }

    /**
     * Get system prompt.
     *
     * @return string
     */
    private function getSystemPrompt(): string
    {
        return <<<PROMPT
Bạn là OikOS Assistant, trợ lý AI thông minh giúp người dùng quản lý tài chính cá nhân, dinh dưỡng và sức khỏe.

Khả năng của bạn:
- Tạo giao dịch thu/chi khi người dùng yêu cầu (sử dụng tool create_transaction)
- Trả lời câu hỏi về tài chính, dinh dưỡng, và sức khỏe
- Sử dụng thông tin từ knowledge base khi có liên quan

Quy tắc:
- Trả lời bằng tiếng Việt
- Ngắn gọn, thân thiện
- Xác nhận khi thực hiện hành động
- Hỏi lại nếu thông tin không đủ
PROMPT;
    }

    /**
     * Get RAG context for the query.
     *
     * @param string $query
     * @return string|null
     */
    private function getRAGContext(string $query): ?string
    {
        try {
            if ($this->isSimpleGreeting($query)) {
                return null;
            }

            $vector = $this->embeddingService->embed($query);
            $results = $this->vectorSearchService->search($vector);

            return empty($results) ? null : $this->vectorSearchService->formatAsContext($results);
        } catch (\Exception $e) {
            Log::warning('RAG context retrieval failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if message is a simple greeting.
     *
     * @param string $message
     * @return bool
     */
    private function isSimpleGreeting(string $message): bool
    {
        $greetings = ['hi', 'hello', 'xin chào', 'chào', 'hey', 'alo'];
        $normalized = mb_strtolower(trim($message));

        foreach ($greetings as $greeting) {
            if ($normalized === $greeting || str_starts_with($normalized, $greeting . ' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inject RAG context into messages.
     *
     * @param array $messages
     * @param string $context
     * @return array
     */
    private function injectRAGContext(array $messages, string $context): array
    {
        foreach ($messages as &$message) {
            if ($message['role'] === 'system') {
                $message['content'] .= "\n\n" . $context;
                break;
            }
        }

        return $messages;
    }

    /**
     * Stream completion with tool execution loop.
     *
     * @param int $userId
     * @param array $messages
     * @return Generator
     */
    private function streamWithTools(int $userId, array $messages): Generator
    {
        $maxIterations = 5;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;
            // Enable streaming
            $response = $this->callCompletionAPI($messages, true);
            $stream = $response['stream'];

            $accumulatedContent = '';
            $accumulatedToolCalls = [];

            foreach ($stream as $chunk) {
                // Handle content
                if (!empty($chunk['content'])) {
                    $accumulatedContent .= $chunk['content'];
                    yield ['type' => 'content', 'content' => $chunk['content']];
                }

                // Handle tool calls
                if (!empty($chunk['tool_calls'])) {
                    foreach ($chunk['tool_calls'] as $toolCallDelta) {
                        $index = $toolCallDelta['index'];

                        if (!isset($accumulatedToolCalls[$index])) {
                            $accumulatedToolCalls[$index] = [
                                'id' => $toolCallDelta['id'] ?? null,
                                'type' => $toolCallDelta['type'] ?? 'function',
                                'function' => [
                                    'name' => $toolCallDelta['function']['name'] ?? '',
                                    'arguments' => '',
                                ]
                            ];
                        }

                        if (isset($toolCallDelta['function']['arguments'])) {
                            $accumulatedToolCalls[$index]['function']['arguments'] .= $toolCallDelta['function']['arguments'];
                        }
                    }
                }
            }

            // If no content and no tool calls, skip (shouldn't happen unless empty stream)
            if (empty($accumulatedContent) && empty($accumulatedToolCalls)) {
                yield ['type' => 'done'];
                break;
            }

            // Prepare tool calls array
            $toolCalls = [];
            if (!empty($accumulatedToolCalls)) {
                $toolCalls = array_values($accumulatedToolCalls);
            }

            // Save assistant message to DB (combining content and tool calls)
            $metadata = [];
            if (!empty($toolCalls)) {
                $metadata['tool_calls'] = $toolCalls;
            }
            $this->saveMessage($userId, 'assistant', $accumulatedContent, $metadata);

            // If we have tool calls, process them and loop
            if (!empty($toolCalls)) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => !empty($accumulatedContent) ? $accumulatedContent : null,
                    'tool_calls' => $toolCalls,
                ];

                $toolResults = $this->executeToolCalls($toolCalls, $userId);

                foreach ($toolResults as $result) {
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $result['id'],
                        'content' => $result['result'],
                    ];
                    $this->saveMessage($userId, 'tool', $result['result'], [
                        'tool_call_id' => $result['id'],
                    ]);
                }

                continue;
            }

            yield ['type' => 'done'];
            break;
        }
    }

    /**
     * Call the OpenAI-compatible completion API.
     *
     * @param array $messages
     * @param bool $stream
     * @return array
     */
    private function callCompletionAPI(array $messages, bool $stream = false): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $this->toolExecutor->getToolsDefinition(),
            'tool_choice' => 'auto',
            'stream' => $stream,
        ];

        if ($stream) {
            return ['stream' => $this->streamFromAPI($payload)];
        }

        return $this->fetchCompletion($payload);
    }

    /**
     * Fetch completion from API (non-streaming).
     *
     * @param array $payload
     * @return array
     */
    private function fetchCompletion(array $payload): array
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->apiUrl}/chat/completions", $payload);

            if (!$response->successful()) {
                Log::error('Completion API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['content' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại.'];
            }

            /** @var array $data */
            $data = $response->json();
            $message = $data['choices'][0]['message'] ?? [];

            return [
                'content' => $message['content'] ?? '',
                'tool_calls' => $message['tool_calls'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Completion API error', ['error' => $e->getMessage()]);
            return ['content' => 'Xin lỗi, không thể kết nối đến AI. Vui lòng thử lại.'];
        }
    }

    /**
     * Stream response from API using native PHP streams.
     *
     * @param array $payload
     * @return Generator
     */
    private function streamFromAPI(array $payload): Generator
    {
        $stream = $this->openStreamConnection($payload);

        if (!$stream) {
            yield 'Xin lỗi, không thể kết nối đến AI.';
            return;
        }

        try {
            $buffer = '';
            while (!feof($stream)) {
                $chunk = fread($stream, 1024);
                if ($chunk === false) {
                    break;
                }

                $buffer .= $chunk;
                $status = yield from $this->processBuffer($buffer);


                if ($status === false) {
                    break;
                }
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * Open stream connection.
     *
     * @param array $payload
     * @return resource|null
     */
    private function openStreamConnection(array $payload)
    {
        $url = "{$this->apiUrl}/chat/completions";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->apiKey}",
            "Accept: text/event-stream",
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($payload),
                'timeout' => 120,
                'ignore_errors' => true,
            ]
        ]);

        $stream = @fopen($url, 'r', false, $context);

        if (!$stream) {
            Log::error('Stream API connection failed');
            return null;
        }

        if (!$this->checkStreamStatus($stream)) {
            fclose($stream);
            return null;
        }

        return $stream;
    }

    /**
     * Process buffer and yield lines.
     *
     * @param string $buffer
     * @return Generator<string, mixed, mixed, bool> Returns false if stream should stop
     */
    private function processBuffer(string &$buffer): Generator
    {
        while (($newlinePos = strpos($buffer, "\n")) !== false) {
            $line = substr($buffer, 0, $newlinePos);
            $buffer = substr($buffer, $newlinePos + 1);
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $content = $this->parseStreamLine($line);
            if ($content === false) {
                return false;
            }

            if ($content !== null) {
                yield $content;
            }
        }

        return true;
    }

    /**
     * Check if stream opened successfully with 200 OK.
     */
    private function checkStreamStatus($stream): bool
    {
        $meta = stream_get_meta_data($stream);
        $statusLine = $meta['wrapper_data'][0] ?? '';

        if (!str_contains($statusLine, '200')) {
            Log::error('Stream API error status', ['status' => $statusLine]);
            return false;
        }

        return true;
    }

    /**
     * Parse a single line from SSE stream.
     * Returns:
     * - array: ['content' => ..., 'tool_calls' => ...]
     * - null: Continue/Ignore
     * - false: Stop stream ([DONE])
     */
    private function parseStreamLine(string $line): array|null|bool
    {
        if (!str_starts_with($line, 'data: ')) {
            return null;
        }

        $json = substr($line, 6);
        if ($json === '[DONE]') {
            return false;
        }

        $data = json_decode($json, true);
        $delta = $data['choices'][0]['delta'] ?? [];

        return [
            'content' => $delta['content'] ?? null,
            'tool_calls' => $delta['tool_calls'] ?? null,
        ];
    }

    /**
     * Execute tool calls.
     *
     * @param array $toolCalls
     * @param int $userId
     * @return array
     */
    private function executeToolCalls(array $toolCalls, int $userId): array
    {
        $results = [];
        $parsed = $this->toolExecutor->parseToolCalls($toolCalls);

        foreach ($parsed as $call) {
            $result = $this->toolExecutor->execute($call['name'], $call['arguments'], $userId);
            $results[] = [
                'id' => $call['id'],
                'result' => $result['result'],
            ];
        }

        return $results;
    }

    /**
     * Save message to chat history.
     *
     * @param int $userId
     * @param string $role
     * @param string $content
     * @param array $metadata
     * @return ChatHistory
     */
    private function saveMessage(int $userId, string $role, string $content, array $metadata = []): ChatHistory
    {
        return ChatHistory::create([
            'user_id' => $userId,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata ?: null,
        ]);
    }

    /**
     * Clear chat history for a user.
     *
     * @param int $userId
     * @return int Number of deleted messages
     */
    public function clearHistory(int $userId): int
    {
        return ChatHistory::where('user_id', $userId)->delete();
    }
}
