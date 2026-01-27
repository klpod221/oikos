<?php

namespace App\Services\AI\Tools;

use App\Models\NutritionLog;
use Illuminate\Support\Carbon;

/**
 * Tool to get nutrition summary.
 */
class GetNutritionSummaryTool extends AITool
{
    public function name(): string
    {
        return 'get_nutrition_summary';
    }

    public function description(): string
    {
        return 'Get a nutrition summary showing total calories and macros for a date or period.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'description' => 'Date to get summary for (YYYY-MM-DD). Default: today',
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date for range (use instead of date)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date for range',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $date = $arguments['date'] ?? null;
        $startDate = $arguments['start_date'] ?? null;
        $endDate = $arguments['end_date'] ?? null;

        if ($startDate && $endDate) {
            $query = NutritionLog::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate]);
            $periodLabel = "{$startDate} to {$endDate}";
        } else {
            $date = $date ?? Carbon::today()->toDateString();
            $query = NutritionLog::where('user_id', $userId)->where('date', $date);
            $periodLabel = $date;
        }

        $summary = $query->selectRaw('
            SUM(calories) as total_calories,
            SUM(protein) as total_protein,
            SUM(carbs) as total_carbs,
            SUM(fat) as total_fat,
            COUNT(*) as meal_count
        ')->first();

        if (!$summary || $summary->meal_count == 0) {
            return [
                'success' => true,
                'result' => "No nutrition logs found for {$periodLabel}.",
            ];
        }

        $result = "🥗 Nutrition Summary ({$periodLabel}):\n";
        $result .= "- 🔥 Calories: " . number_format($summary->total_calories ?? 0, 0) . " kcal\n";
        $result .= "- 🥩 Protein: " . number_format($summary->total_protein ?? 0, 0) . "g\n";
        $result .= "- 🍚 Carbs: " . number_format($summary->total_carbs ?? 0, 0) . "g\n";
        $result .= "- 🥑 Fat: " . number_format($summary->total_fat ?? 0, 0) . "g\n";
        $result .= "- 📊 Meals logged: {$summary->meal_count}";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
