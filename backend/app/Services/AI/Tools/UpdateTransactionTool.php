<?php

namespace App\Services\AI\Tools;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * Tool to update an existing transaction.
 */
class UpdateTransactionTool extends AITool
{
    public function name(): string
    {
        return 'update_transaction';
    }

    public function description(): string
    {
        return 'Update an existing transaction. Requires the transaction ID. Use get_transactions first to find the transaction ID.';
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
                    'description' => 'ID of the transaction to update',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'New amount (optional)',
                ],
                'note' => [
                    'type' => 'string',
                    'description' => 'New note/description (optional)',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'New category name (optional)',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'New date in YYYY-MM-DD format (optional)',
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

            $updates = [];

            if (isset($arguments['amount'])) {
                $updates['amount'] = (float) $arguments['amount'];
            }

            if (isset($arguments['note'])) {
                $updates['description'] = $arguments['note'];
            }

            if (isset($arguments['date'])) {
                $updates['transaction_date'] = $arguments['date'];
            }

            if (isset($arguments['category'])) {
                $categoryId = $this->resolveCategory($userId, $transaction->type, $arguments['category']);
                if ($categoryId) {
                    $updates['category_id'] = $categoryId;
                }
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'result' => 'No updates provided.',
                ];
            }

            $transaction->update($updates);

            return [
                'success' => true,
                'result' => "Transaction #{$transactionId} updated successfully.",
            ];
        } catch (\Exception $e) {
            Log::error('UpdateTransactionTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to update transaction: ' . $e->getMessage(),
            ];
        }
    }

    private function resolveCategory(int $userId, string $type, string $name): ?int
    {
        $category = \App\Models\Category::availableFor($userId)
            ->where('type', $type)
            ->where('is_active', true)
            ->where('name', 'like', "%{$name}%")
            ->first();

        return $category?->id;
    }
}
