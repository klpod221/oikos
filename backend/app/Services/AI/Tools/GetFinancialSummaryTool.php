<?php

namespace App\Services\AI\Tools;

use App\Models\Transaction;
use Illuminate\Support\Carbon;

/**
 * Tool to get financial summary for a period.
 */
class GetFinancialSummaryTool extends AITool
{
    public function name(): string
    {
        return 'get_financial_summary';
    }

    public function description(): string
    {
        return 'Get a financial summary (total income, expense, and balance) for a specific period. Useful for budget tracking and spending analysis.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'enum' => ['today', 'this_week', 'this_month', 'last_month', 'custom'],
                    'description' => 'Time period for summary (default: this_month)',
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date for custom period (YYYY-MM-DD)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date for custom period (YYYY-MM-DD)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        [$startDate, $endDate] = $this->resolvePeriod($arguments);

        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $balance = $income - $expense;
        $balanceIcon = $balance >= 0 ? '📈' : '📉';

        $result = "Financial Summary ({$startDate} to {$endDate}):\n";
        $result .= "- 📥 Income: " . number_format($income, 0, ',', '.') . "đ\n";
        $result .= "- 📤 Expense: " . number_format($expense, 0, ',', '.') . "đ\n";
        $result .= "- {$balanceIcon} Balance: " . number_format($balance, 0, ',', '.') . "đ";

        return [
            'success' => true,
            'result' => $result,
            'data' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance,
            ],
        ];
    }

    private function resolvePeriod(array $arguments): array
    {
        $period = $arguments['period'] ?? 'this_month';

        return match ($period) {
            'today' => [Carbon::today()->toDateString(), Carbon::today()->toDateString()],
            'this_week' => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'this_month' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth()->toDateString(), Carbon::now()->subMonth()->endOfMonth()->toDateString()],
            'custom' => [$arguments['start_date'] ?? Carbon::now()->startOfMonth()->toDateString(), $arguments['end_date'] ?? Carbon::now()->toDateString()],
            default => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
        };
    }
}
