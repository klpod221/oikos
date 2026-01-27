<?php

namespace App\Services\AI;

use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\Log;

/**
 * Executor for AI function calling (tools).
 */
class ToolExecutor
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get available tools definition for OpenAI API.
     *
     * @return array
     */
    public function getToolsDefinition(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_transaction',
                    'description' => 'Tạo giao dịch tài chính mới (thu nhập hoặc chi tiêu). Sử dụng khi người dùng muốn ghi lại một khoản thu/chi.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'amount' => [
                                'type' => 'number',
                                'description' => 'Số tiền giao dịch (VND)',
                            ],
                            'note' => [
                                'type' => 'string',
                                'description' => 'Ghi chú cho giao dịch',
                            ],
                            'type' => [
                                'type' => 'string',
                                'enum' => ['income', 'expense'],
                                'description' => 'Loại giao dịch: income (thu nhập) hoặc expense (chi tiêu)',
                            ],
                        ],
                        'required' => ['amount', 'type'],
                    ],
                ],
            ],
        ];
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

        return match ($toolName) {
            'create_transaction' => $this->createTransaction($arguments, $userId),
            default => [
                'success' => false,
                'result' => "Unknown tool: {$toolName}",
            ],
        };
    }

    /**
     * Create a financial transaction.
     *
     * @param array $arguments
     * @param int $userId
     * @return array
     */
    private function createTransaction(array $arguments, int $userId): array
    {
        try {
            $amount = (float) ($arguments['amount'] ?? 0);
            $type = $arguments['type'] ?? 'expense';
            $note = $arguments['note'] ?? '';

            if ($amount <= 0) {
                return [
                    'success' => false,
                    'result' => 'Số tiền phải lớn hơn 0',
                ];
            }

            // Get default wallet
            $wallet = \App\Models\Wallet::where('user_id', $userId)->first();
            if (!$wallet) {
                return [
                    'success' => false,
                    'result' => 'Bạn chưa có ví tiền. Vui lòng tạo ví trước khi ghi giao dịch.',
                ];
            }

            // Create transaction via existing service
            $transaction = $this->transactionService->createTransaction($userId, [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => $type,
                'note' => $note,
                'transaction_date' => now()->toDateString(),
            ]);

            $typeLabel = $type === 'income' ? 'thu nhập' : 'chi tiêu';
            $formattedAmount = number_format($amount, 0, ',', '.');

            return [
                'success' => true,
                'result' => "Đã tạo giao dịch {$typeLabel} {$formattedAmount}đ" . ($note ? " với ghi chú: {$note}" : ''),
                'data' => $transaction,
            ];
        } catch (\Exception $e) {
            Log::error('Tool execution failed', [
                'tool' => 'create_transaction',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'result' => 'Không thể tạo giao dịch: ' . $e->getMessage(),
            ];
        }
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
