<?php

namespace App\Services\AI\Tools;

use App\Models\Transaction;

/**
 * Tool to query transactions with various filters.
 */
class GetTransactionsTool extends AITool
{
    public function name(): string
    {
        return 'get_transactions';
    }

    public function description(): string
    {
        return 'Get a list of user transactions. Use this tool to view transaction history with filters like date range, type, or category.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['income', 'expense'],
                    'description' => 'Filter by transaction type',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Filter by category name',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of transactions to return (default: 10)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $limit = min((int) ($arguments['limit'] ?? 10), 50);

        $query = Transaction::where('user_id', $userId)
            ->with(['category:id,name', 'wallet:id,name'])
            ->orderBy('transaction_date', 'desc');

        if (!empty($arguments['start_date'])) {
            $query->where('transaction_date', '>=', $arguments['start_date']);
        }

        if (!empty($arguments['end_date'])) {
            $query->where('transaction_date', '<=', $arguments['end_date']);
        }

        if (!empty($arguments['type'])) {
            $query->where('type', $arguments['type']);
        }

        if (!empty($arguments['category'])) {
            $query->whereHas('category', function ($q) use ($arguments) {
                $q->where('name', 'like', "%{$arguments['category']}%");
            });
        }

        $transactions = $query->limit($limit)->get();

        if ($transactions->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No transactions found with the given filters.',
            ];
        }

        $list = "Transactions:\n";
        foreach ($transactions as $tx) {
            $typeIcon = $tx->type === 'income' ? '➕' : '➖';
            $amount = number_format($tx->amount, 0, ',', '.');
            $category = $tx->category->name ?? 'Unknown';
            $list .= "{$typeIcon} {$tx->transaction_date->format('d/m')}: {$amount}đ - {$category}";
            if ($tx->description) {
                $list .= " ({$tx->description})";
            }
            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
            'data' => ['count' => $transactions->count()],
        ];
    }
}
