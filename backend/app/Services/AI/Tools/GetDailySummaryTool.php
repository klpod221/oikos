<?php

namespace App\Services\AI\Tools;

use App\Models\DailySummary;
use Illuminate\Support\Carbon;

/**
 * Tool to get daily summary with TDEE, nutrition, and energy balance.
 */
class GetDailySummaryTool extends AITool
{
    public function name(): string
    {
        return 'get_daily_summary';
    }

    public function description(): string
    {
        return 'Get the daily summary for a specific date, including BMR, TDEE, calorie intake, and energy balance. Use this for tracking daily nutrition and fitness progress.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'description' => 'Date to get summary for (YYYY-MM-DD). Defaults to today.',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $date = $arguments['date'] ?? Carbon::today()->toDateString();

        $summary = DailySummary::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$summary) {
            return [
                'success' => true,
                'result' => "No daily summary found for {$date}. The summary may not have been calculated yet.",
            ];
        }

        $balanceIcon = $summary->energy_balance >= 0 ? '📈 Surplus' : '📉 Deficit';
        $balanceAbs = abs($summary->energy_balance);

        $result = "Daily Summary for {$date}:\n";
        $result .= "🔥 BMR: " . number_format($summary->bmr, 0) . " kcal\n";
        $result .= "⚡ TDEE: " . number_format($summary->tdee, 0) . " kcal\n";
        $result .= "🏃 Workout: " . number_format($summary->workout_calories, 0) . " kcal burned\n";
        $result .= "🍽️ Intake: " . number_format($summary->total_calories, 0) . " kcal\n";
        $result .= "- Protein: " . number_format($summary->total_protein, 0) . "g\n";
        $result .= "- Carbs: " . number_format($summary->total_carbs, 0) . "g\n";
        $result .= "- Fat: " . number_format($summary->total_fat, 0) . "g\n";
        $result .= "{$balanceIcon}: " . number_format($balanceAbs, 0) . " kcal";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
