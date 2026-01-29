<?php

namespace App\Services\AI;

use App\Models\ChatHistory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Generator;

/**
 * Main chat orchestrator with function calling and streaming.
 */
class ChatService
{
    private ToolExecutor $toolExecutor;

    private string $apiUrl;
    private string $apiKey;
    private string $model;
    private int $memoryLimit;

    public function __construct(
        ToolExecutor $toolExecutor
    ) {
        $this->toolExecutor = $toolExecutor;

        $this->apiUrl = config('services.openai.url', 'http://localhost:8045/v1');
        $this->apiKey = config('services.openai.key', 'dummy-key');
        $this->model = config('services.openai.model', 'gemini-2.5-flash');
        $this->memoryLimit = config('services.openai.memory_limit', 10);
    }

    /**
     * Process a chat message with streaming response.
     *
     * @param int $userId
     * @param string $userMessage
     * @return Generator
     */
    public function streamChat(int $userId, string $userMessage): Generator
    {
        $this->saveMessage($userId, 'user', $userMessage);

        $messages = $this->buildMessages($userId);

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
You are OikOS Assistant, an intelligent AI assistant helping users manage personal finance, nutrition, and health.

## Available Capabilities

### 💰 Finance Management
- View/create/update/delete transactions with filters (date, type, category)
- View wallet balances and create new wallets
- Get financial summaries for any period
- Manage savings goals with progress tracking

### 🥗 Nutrition Tracking
- Search ingredients with nutritional info
- View and manage recipes
- Create and view meal plans
- Log food intake for nutrition tracking
- Get nutrition summaries (calories, protein, carbs, fat)
- Generate shopping lists from meal plans

### 🏋️ Fitness & Workouts
- Search exercises with calorie burn info
- View workout routines
- Check workout schedule
- Log completed workouts
- Get workout statistics

### 📊 User & Summary
- View/update user physical stats (weight, height, activity level)
- View fitness and nutrition goals
- Get daily summary (BMR, TDEE, energy balance)
- Get comprehensive weekly reports

## Instructions
1. Use tools to perform actions - they give you real data access.
2. Check tool descriptions for dependencies (e.g., get_transactions before update_transaction).
3. Chain tools when needed: e.g., get_categories → create_transaction.
4. ALWAYS reply in the user's language (Vietnamese or English).
5. Keep responses concise, friendly, and actionable.
6. For financial amounts, use VND format with thousand separators.
PROMPT;
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
     * Run chat in background with tools (Non-streaming).
     * Used for background jobs like Gmail Sync.
     *
     * @param int $userId
     * @param string $systemPrompt
     * @param string $userMessage
     * @return string
     */
    public function runWithTools(int $userId, string $systemPrompt, string $userMessage): string
    {
        // 1. Prepare Messages
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        // 2. Loop for Tool Execution (Max 5 turns)
        $maxIterations = 5;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;

            // Call API
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $this->toolExecutor->getToolsDefinition(),
                'tool_choice' => 'auto',
                'stream' => false,
            ];

            $response = $this->fetchCompletion($payload);
            $content = $response['content'];
            $toolCalls = $response['tool_calls'];

            // If no tool calls, we are done.
            if (empty($toolCalls)) {
                return $content;
            }

            // Append Assistant Response to history for next turn
            $messages[] = [
                'role' => 'assistant',
                'content' => $content,
                'tool_calls' => $toolCalls,
            ];

            // Execute Tools
            $toolResults = $this->executeToolCalls($toolCalls, $userId);

            // Append Tool Results
            foreach ($toolResults as $result) {
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $result['id'],
                    'content' => $result['result'],
                ];
            }

            // Loop back to give AI a chance to summarize or do more actions
        }

        return "Max iterations reached.";
    }

    /**
     * Generate a completion (public wrapper for AI services).
     *
     * @param string $systemPrompt
     * @param string $userMessage
     * @return string|null
     */
    public function generateCompletion(string $systemPrompt, string $userMessage): ?string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
        ];

        $result = $this->fetchCompletion($payload);
        return $result['content'] ?? null;
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
                return [
                    'content' => 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại.',
                    'tool_calls' => [],
                ];
            }

            // Log Response for debugging
            // Log::info('AI Response', $response->json());

            /** @var array $data */
            $data = $response->json();
            $message = $data['choices'][0]['message'] ?? [];

            return [
                'content' => $message['content'] ?? '',
                'tool_calls' => $message['tool_calls'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Completion API error', ['error' => $e->getMessage()]);
            return [
                'content' => 'Xin lỗi, không thể kết nối đến AI. Vui lòng thử lại.',
                'tool_calls' => [],
            ];
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
    /**
     * Execute tool calls.
     * Handles both raw OpenAI format and parsed internal format.
     *
     * @param array $toolCalls
     * @param int $userId
     * @return array
     */
    private function executeToolCalls(array $toolCalls, int $userId): array
    {
        $results = [];
        $parsed = [];

        // Determine if we need to parse
        // Check first item
        $first = reset($toolCalls);
        if ($first && isset($first['function'])) {
            // It's Raw OpenAI format
            $parsed = $this->toolExecutor->parseToolCalls($toolCalls);
        } else {
            // It's already parsed or some other format
            $parsed = $toolCalls;
        }

        foreach ($parsed as $call) {
            if (!isset($call['name']) || !isset($call['arguments'])) {
                Log::warning("Skipping invalid tool call structure", ['call' => $call]);
                continue;
            }

            $result = $this->toolExecutor->execute($call['name'], $call['arguments'], $userId);

            $results[] = [
                'id' => $call['id'] ?? uniqid('tool_exec_'),
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
