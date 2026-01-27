<?php

namespace App\Services\AI;

use App\Services\AI\Tools\AITool;
use App\Services\AI\Tools\CreateTransactionTool;
use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\Log;

/**
 * Executor for AI function calling (tools).
 * Acts as a registry for all available tools.
 */
class ToolExecutor
{
    /** @var array<string, AITool> */
    private array $tools = [];

    public function __construct(TransactionService $transactionService)
    {
        // Register default tools
        $this->register(new CreateTransactionTool($transactionService));
    }

    /**
     * Register a tool.
     */
    public function register(AITool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * Get available tools definition for OpenAI API.
     *
     * @return array
     */
    public function getToolsDefinition(): array
    {
        $definitions = [];
        foreach ($this->tools as $tool) {
            $definitions[] = $tool->getDefinition();
        }
        return $definitions;
    }

    /**
     * Execute a tool call.
     *
     * @param string $toolName
     * @param array $arguments
     * @param int $userId
     * @return array{success: bool, result: string, data?: mixed}
     */
    public function execute(string $toolName, array $arguments, int $userId): array
    {
        Log::info('Executing tool', [
            'tool' => $toolName,
            'arguments' => $arguments,
            'user_id' => $userId,
        ]);

        if (!isset($this->tools[$toolName])) {
            return [
                'success' => false,
                'result' => "Unknown tool: {$toolName}",
            ];
        }

        return $this->tools[$toolName]->execute($arguments, $userId);
    }

    /**
     * Parse tool calls from OpenAI response.
     *
     * @param array $toolCalls
     * @return array<array{id: string, name: string, arguments: array}>
     */
    public function parseToolCalls(array $toolCalls): array
    {
        $parsed = [];

        foreach ($toolCalls as $toolCall) {
            $function = $toolCall['function'] ?? [];
            $arguments = $function['arguments'] ?? '{}';

            // Arguments come as JSON string
            if (is_string($arguments)) {
                $arguments = json_decode($arguments, true) ?? [];
            }

            $parsed[] = [
                'id' => $toolCall['id'] ?? uniqid('tool_'),
                'name' => $function['name'] ?? '',
                'arguments' => $arguments,
            ];
        }

        return $parsed;
    }
}
