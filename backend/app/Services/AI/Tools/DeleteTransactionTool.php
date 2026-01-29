<?php

namespace App\Services\AI\Tools;

use App\Models\Transaction;
use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\Log;

/**
 * Tool to delete a transaction.
 */
class DeleteTransactionTool extends AITool
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function name(): string
    {
        return 'delete_transaction';
    }

    public function description(): string
    {
        return 'Delete a transaction by ID. Use get_transactions first to find the transaction ID. This action cannot be undone.';
    }

    public function dependsOn(): array
    {
        return ['get_transactions'];
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'transaction_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the transaction to delete',
                ],
            ],
            'required' => ['transaction_id'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $transactionId = $arguments['transaction_id'] ?? null;

            if (!$transactionId) {
                return [
                    'success' => false,
                    'result' => 'Transaction ID is required.',
                ];
            }

            $transaction = Transaction::where('user_id', $userId)
                ->where('id', $transactionId)
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'result' => "Transaction #{$transactionId} not found.",
                ];
            }

            $amount = number_format($transaction->amount, 0, ',', '.');
            $type = $transaction->type;

            $this->transactionService->deleteTransaction($transaction);

            return [
                'success' => true,
                'result' => "Deleted {$type} transaction of {$amount}đ (ID: #{$transactionId}).",
            ];
        } catch (\Exception $e) {
            Log::error('DeleteTransactionTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to delete transaction: ' . $e->getMessage(),
            ];
        }
    }
}
