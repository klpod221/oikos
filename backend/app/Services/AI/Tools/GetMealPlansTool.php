<?php

namespace App\Services\AI\Tools;

use App\Models\MealPlan;
use Illuminate\Support\Carbon;

/**
 * Tool to get meal plans.
 */
class GetMealPlansTool extends AITool
{
    public function name(): string
    {
        return 'get_meal_plans';
    }

    public function description(): string
    {
        return 'Get meal plans for a date range. Shows planned meals with recipes.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date (YYYY-MM-DD). Default: today',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date (YYYY-MM-DD). Default: 7 days from start',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $startDate = $arguments['start_date'] ?? Carbon::today()->toDateString();
        $endDate = $arguments['end_date'] ?? Carbon::parse($startDate)->addDays(6)->toDateString();

        $plans = MealPlan::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('recipe:id,name,calories')
            ->orderBy('date')
            ->orderByRaw("FIELD(meal_type, 'breakfast', 'lunch', 'dinner', 'snack')")
            ->get();

        if ($plans->isEmpty()) {
            return [
                'success' => true,
                'result' => "No meal plans found for {$startDate} to {$endDate}.",
            ];
        }

        $grouped = $plans->groupBy(fn($p) => $p->date->format('Y-m-d'));
        $result = "📅 Meal Plans ({$startDate} to {$endDate}):\n\n";

        foreach ($grouped as $date => $dayPlans) {
            $dateFormatted = Carbon::parse($date)->format('D d/m');
            $result .= "**{$dateFormatted}:**\n";

            foreach ($dayPlans as $plan) {
                $mealIcon = match ($plan->meal_type) {
                    'breakfast' => '🌅',
                    'lunch' => '☀️',
                    'dinner' => '🌙',
                    default => '🍎',
                };
                $recipeName = $plan->recipe->name ?? 'Unknown';
                $result .= "  {$mealIcon} " . ucfirst($plan->meal_type) . ": {$recipeName}\n";
            }
            $result .= "\n";
        }

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
