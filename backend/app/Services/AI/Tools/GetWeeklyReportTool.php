<?php

namespace App\Services\AI\Tools;

use App\Models\Transaction;
use App\Models\NutritionLog;
use App\Models\WorkoutLog;
use Illuminate\Support\Carbon;

/**
 * Tool to get comprehensive weekly report.
 */
class GetWeeklyReportTool extends AITool
{
    public function name(): string
    {
        return 'get_weekly_report';
    }

    public function description(): string
    {
        return 'Get a comprehensive weekly report including finance summary, nutrition averages, and workout statistics.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'week_offset' => [
                    'type' => 'integer',
                    'description' => 'Week offset from current week. 0 = this week, -1 = last week. Default: 0',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $weekOffset = (int) ($arguments['week_offset'] ?? 0);
        $startOfWeek = Carbon::now()->addWeeks($weekOffset)->startOfWeek();
        $endOfWeek = Carbon::now()->addWeeks($weekOffset)->endOfWeek();

        $startDate = $startOfWeek->toDateString();
        $endDate = $endOfWeek->toDateString();

        // Finance
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Nutrition
        $nutrition = NutritionLog::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('AVG(calories) as avg_calories, AVG(protein) as avg_protein')
            ->first();

        // Workout
        $workouts = WorkoutLog::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->get();

        $workoutCount = $workouts->count();
        $totalCaloriesBurned = $workouts->sum('calories_burned');

        $result = "📊 Weekly Report ({$startOfWeek->format('d/m')} - {$endOfWeek->format('d/m')}):\n\n";

        // Finance section
        $result .= "💰 FINANCE:\n";
        $result .= "- Income: " . number_format($income, 0, ',', '.') . "đ\n";
        $result .= "- Expense: " . number_format($expense, 0, ',', '.') . "đ\n";
        $result .= "- Balance: " . number_format($income - $expense, 0, ',', '.') . "đ\n\n";

        // Nutrition section
        $result .= "🥗 NUTRITION (daily avg):\n";
        $result .= "- Calories: " . number_format($nutrition->avg_calories ?? 0, 0) . " kcal\n";
        $result .= "- Protein: " . number_format($nutrition->avg_protein ?? 0, 0) . "g\n\n";

        // Workout section
        $result .= "🏋️ WORKOUT:\n";
        $result .= "- Sessions: {$workoutCount}\n";
        $result .= "- Calories burned: " . number_format($totalCaloriesBurned, 0) . " kcal";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
