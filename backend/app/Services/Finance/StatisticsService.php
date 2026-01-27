<?php

namespace App\Services\Finance;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\SavingsGoal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Statistics Service
 *
 * Service for calculating financial statistics.
 *
 * @package App\Services\Finance
 */
class StatisticsService
{
    /**
     * Get comprehensive statistics for a user within a time period
     *
     * @param int $userId
     * @param string $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStatistics(int $userId, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        [$start, $end, $label] = $this->parsePeriod($period, $startDate, $endDate);

        // Generate cache key
        $cacheKey = "user:{$userId}:stats:{$period}:" . md5($start . $end);
        $ttl = $this->getCacheTTL($period);

        return Cache::remember($cacheKey, $ttl, function () use ($userId, $start, $end, $label) {
            return [
                'period' => [
                    'start' => $start,
                    'end' => $end,
                    'label' => $label,
                ],
                'summary' => $this->calculateSummary($userId, $start, $end),
                'by_category' => $this->calculateCategoryBreakdown($userId, $start, $end),
                'daily_trend' => $this->calculateDailyTrend($userId, $start, $end),
                'top_expenses' => $this->calculateTopExpenses($userId, $start, $end),
                'savings_goals_progress' => $this->calculateSavingsGoalsProgress($userId),
            ];
        });
    }

    /**
     * Invalidate statistics cache for a user
     */
    public function invalidateCache(int $userId): void
    {
        // Redis supports tags, so we can flush all stats cache for this user
        Cache::tags(["user:{$userId}:stats"])->flush();
    }

    /**
     * Parse period string into start/end dates
     */
    private function parsePeriod(string $period, ?string $startDate, ?string $endDate): array
    {
        $now = now();

        return match ($period) {
            'this_month' => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
                $now->format('F Y'),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth()->toDateString(),
                $now->copy()->subMonth()->endOfMonth()->toDateString(),
                $now->copy()->subMonth()->format('F Y'),
            ],
            'this_year' => [
                $now->copy()->startOfYear()->toDateString(),
                $now->copy()->endOfYear()->toDateString(),
                $now->format('Y'),
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear()->toDateString(),
                $now->copy()->subYear()->endOfYear()->toDateString(),
                $now->copy()->subYear()->format('Y'),
            ],
            'custom' => [
                $startDate ?? $now->copy()->startOfMonth()->toDateString(),
                $endDate ?? $now->copy()->endOfMonth()->toDateString(),
                'Custom Range',
            ],
            default => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
                $now->format('F Y'),
            ],
        };
    }

    /**
     * Get cache TTL based on period
     */
    private function getCacheTTL(string $period): int
    {
        // Current month changes frequently, cache for 1 hour
        if ($period === 'this_month') {
            return 3600;
        }

        // Historical data, cache for 24 hours
        return 86400;
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(int $userId, string $start, string $end): array
    {
        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->get();

        $income = $transactions->where('type', Transaction::TYPE_INCOME)->sum('amount');
        $expense = $transactions->where('type', Transaction::TYPE_EXPENSE)->sum('amount');

        return [
            'total_income' => (float) $income,
            'total_expense' => (float) $expense,
            'net_savings' => (float) ($income - $expense),
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Calculate breakdown by category
     */
    private function calculateCategoryBreakdown(int $userId, string $start, string $end): array
    {
        $results = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->with('category')
            ->select('category_id', 'type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id', 'type')
            ->get();

        $totalExpense = $results->where('type', Transaction::TYPE_EXPENSE)->sum('total');

        return $results->map(function ($item) use ($totalExpense) {
            $percentage = $item->type === Transaction::TYPE_EXPENSE && $totalExpense > 0
                ? round(($item->total / $totalExpense) * 100, 2)
                : 0;

            return [
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name ?? 'Uncategorized',
                'icon' => $item->category?->icon ?? '📝',
                'type' => $item->type,
                'total' => (float) $item->total,
                'percentage' => $percentage,
                'transaction_count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Calculate daily trend
     */
    private function calculateDailyTrend(int $userId, string $start, string $end): array
    {
        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->select('transaction_date', 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_date', 'type')
            ->orderBy('transaction_date')
            ->get();

        // Group by date
        $byDate = [];
        foreach ($transactions as $transaction) {
            // Convert Carbon to string if needed
            $date = $transaction->transaction_date instanceof \Carbon\Carbon
                ? $transaction->transaction_date->toDateString()
                : (string) $transaction->transaction_date;

            if (!isset($byDate[$date])) {
                $byDate[$date] = ['date' => $date, 'income' => 0, 'expense' => 0];
            }

            if ($transaction->type === Transaction::TYPE_INCOME) {
                $byDate[$date]['income'] = (float) $transaction->total;
            } else {
                $byDate[$date]['expense'] = (float) $transaction->total;
            }
        }

        return array_values($byDate);
    }

    /**
     * Calculate top expenses
     */
    private function calculateTopExpenses(int $userId, string $start, string $end, int $limit = 5): array
    {
        return Transaction::where('user_id', $userId)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('transaction_date', [$start, $end])
            ->with('category')
            ->select('category_id', DB::raw('SUM(amount) as amount'), DB::raw('COUNT(*) as transactions'))
            ->groupBy('category_id')
            ->orderBy('amount', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'category_name' => $item->category?->name ?? 'Uncategorized',
                    'icon' => $item->category?->icon ?? '📝',
                    'amount' => (float) $item->amount,
                    'transactions' => $item->transactions,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate savings goals progress
     */
    private function calculateSavingsGoalsProgress(int $userId): array
    {
        $goals = SavingsGoal::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->get();

        return $goals->map(function ($goal) {
            $progress = $goal->target_amount > 0
                ? round(($goal->current_amount / $goal->target_amount) * 100, 2)
                : 0;

            // Calculate monthly required amount if deadline exists
            $monthlyRequired = 0;
            if ($goal->deadline) {
                $monthsRemaining = now()->diffInMonths($goal->deadline, false);
                if ($monthsRemaining > 0) {
                    $remaining = $goal->target_amount - $goal->current_amount;
                    $monthlyRequired = $remaining / $monthsRemaining;
                }
            }

            return [
                'goal_id' => $goal->id,
                'name' => $goal->name,
                'current_progress' => $progress,
                'monthly_required' => round($monthlyRequired, 2),
                'status' => $goal->status,
            ];
        })->toArray();
    }
}
