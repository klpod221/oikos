<?php

namespace App\Services\AI\Tools;

use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\Log;

class CreateTransactionTool extends AITool
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function name(): string
    {
        return 'create_transaction';
    }

    public function description(): string
    {
        return 'Create a new financial transaction (income or expense). Use this when the user wants to record a transaction.';
    }

    public function dependsOn(): array
    {
        return ['get_categories', 'get_wallets'];
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'amount' => [
                    'type' => 'number',
                    'description' => 'Transaction amount (VND)',
                ],
                'note' => [
                    'type' => 'string',
                    'description' => 'Note/Description for the transaction',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['income', 'expense'],
                    'description' => 'Transaction type: income or expense',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Category name. IMPORTANT: You MUST use get_categories first to find a valid category name.',
                ],
                'wallet' => [
                    'type' => 'string',
                    'description' => 'Wallet name. IMPORTANT: You MUST use get_wallets first to find a valid wallet name.',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Transaction date in YYYY-MM-DD format.',
                ],
            ],
            'required' => ['amount', 'type', 'category', 'wallet'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $amount = (float) ($arguments['amount'] ?? 0);
            $type = $arguments['type'] ?? 'expense';
            $note = $arguments['note'] ?? '';
            $categoryName = $arguments['category'] ?? null;
            $walletName = $arguments['wallet'] ?? null;
            $date = $arguments['date'] ?? now()->toDateString();

            if ($amount <= 0) {
                return [
                    'success' => false,
                    'result' => 'Amount must be greater than 0',
                ];
            }

            // Resolve wallet
            $wallet = $this->resolveWallet($userId, $walletName);
            if (!$wallet) {
                return [
                    'success' => false,
                    'result' => 'User has no wallet',
                ];
            }

            // Resolve category
            $categoryId = $this->resolveCategory($userId, $type, $categoryName);

            if (!$categoryId) {
                return [
                    'success' => false,
                    'result' => 'Category not found. Please try again.',
                ];
            }

            // Create transaction via service
            $transaction = $this->transactionService->createTransaction($userId, [
                'wallet_id' => $wallet->id,
                'category_id' => $categoryId,
                'amount' => $amount,
                'type' => $type,
                'description' => $note,
                'transaction_date' => $date,
            ]);

            $typeLabel = $type === 'income' ? 'income' : 'expense';
            $formattedAmount = number_format($amount, 0, ',', '.');

            // Fetch category name for clearer response
            $category = \App\Models\Category::find($categoryId);
            $catName = $category ? $category->name : 'Khác';

            return [
                'success' => true,
                'result' => "Created {$typeLabel} transaction for {$formattedAmount}đ into category '{$catName}'" . ($note ? " (Note: {$note})" : ''),
                'data' => $transaction,
            ];
        } catch (\Exception $e) {
            Log::error('Tool execution failed', [
                'tool' => 'create_transaction',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'result' => 'Failed to create transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve category ID based on name or type.
     */
    private function resolveCategory(int $userId, string $type, ?string $name): ?int
    {
        $query = \App\Models\Category::availableFor($userId)
            ->where('type', $type)
            ->where('is_active', true);

        if ($name) {
            $cat = (clone $query)->where('name', 'like', "%{$name}%")->first();
            if ($cat) {
                return $cat->id;
            }
        }

        $fallback = (clone $query)->where(function ($q) {
            $q->where('name', 'like', '%Khác%')
                ->orWhere('name', 'like', '%General%')
                ->orWhere('name', 'like', '%Chung%');
        })->first();

        if ($fallback) {
            return $fallback->id;
        }

        $any = $query->first();
        return $any ? $any->id : null;
    }

    /**
     * Resolve wallet by name or get default.
     */
    private function resolveWallet(int $userId, ?string $name): ?\App\Models\Wallet
    {
        $query = \App\Models\Wallet::where('user_id', $userId);

        if ($name) {
            $wallet = (clone $query)->where('name', 'like', "%{$name}%")->first();
            if ($wallet) {
                return $wallet;
            }
        }

        // Fall back to default wallet or first wallet
        $default = (clone $query)->where('is_default', true)->first();
        if ($default) {
            return $default;
        }

        $first = $query->first();
        if ($first) {
            return $first;
        }

        return null;
    }
}
