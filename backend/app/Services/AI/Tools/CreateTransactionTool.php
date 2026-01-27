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
                    'description' => 'Category name (e.g., Food, Travel). Optional if not explicitly mentioned.',
                ],
            ],
            'required' => ['amount', 'type'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $amount = (float) ($arguments['amount'] ?? 0);
            $type = $arguments['type'] ?? 'expense';
            $note = $arguments['note'] ?? '';
            $categoryName = $arguments['category'] ?? null;

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

            // Resolve category
            $categoryId = $this->resolveCategory($userId, $type, $categoryName); // Removed $note

            if (!$categoryId) {
                return [
                    'success' => false,
                    'result' => 'Không tìm thấy danh mục phù hợp. Vui lòng thử lại.',
                ];
            }

            // Create transaction via service
            $transaction = $this->transactionService->createTransaction($userId, [
                'wallet_id' => $wallet->id,
                'category_id' => $categoryId,
                'amount' => $amount,
                'type' => $type,
                'description' => $note,
                'transaction_date' => now()->toDateString(),
            ]);

            $typeLabel = $type === 'income' ? 'thu nhập' : 'chi tiêu';
            $formattedAmount = number_format($amount, 0, ',', '.');

            // Fetch category name for clearer response
            $category = \App\Models\Category::find($categoryId);
            $catName = $category ? $category->name : 'Khác';

            return [
                'success' => true,
                'result' => "Đã tạo giao dịch {$typeLabel} {$formattedAmount}đ vào mục '{$catName}'" . ($note ? " (Ghi chú: {$note})" : ''),
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
     * Resolve category ID based on name or type.
     */
    private function resolveCategory(int $userId, string $type, ?string $name): ?int
    {
        $query = \App\Models\Category::availableFor($userId)
            ->where('type', $type)
            ->where('is_active', true);

        // 1. Precise match if name provided
        if ($name) {
            $cat = (clone $query)->where('name', 'like', "%{$name}%")->first();
            if ($cat) {
                return $cat->id;
            }
        }

        // 2. Fuzzy match from note if no name
        // (Simplified for now, similar to previous logic)

        // 3. Find "General" or "Other" category as fallback
        $fallback = (clone $query)->where(function ($q) {
            $q->where('name', 'like', '%Khác%')
                ->orWhere('name', 'like', '%General%')
                ->orWhere('name', 'like', '%Chung%');
        })->first();

        if ($fallback) {
            return $fallback->id;
        }

        // 4. Any first category of that type
        $any = $query->first();
        return $any ? $any->id : null;
    }
}
