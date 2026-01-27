<?php

namespace App\Services\AI\Tools;

use App\Models\MealPlan;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Tool to generate shopping list from meal plans.
 */
class GenerateShoppingListTool extends AITool
{
    public function name(): string
    {
        return 'generate_shopping_list';
    }

    public function description(): string
    {
        return 'Generate a shopping list from meal plans in a date range. Aggregates all required ingredients.';
    }

    public function dependsOn(): array
    {
        return ['get_meal_plans'];
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date (YYYY-MM-DD)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date (YYYY-MM-DD)',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Name for the shopping list (optional)',
                ],
            ],
            'required' => ['start_date', 'end_date'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $startDate = $arguments['start_date'];
            $endDate = $arguments['end_date'];
            $name = $arguments['name'] ?? "Shopping List {$startDate} to {$endDate}";

            // Get meal plans with recipes and ingredients
            $plans = MealPlan::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->with('recipe.ingredients')
                ->get();

            if ($plans->isEmpty()) {
                return [
                    'success' => false,
                    'result' => "No meal plans found for {$startDate} to {$endDate}.",
                ];
            }

            // Aggregate ingredients
            $ingredients = [];
            foreach ($plans as $plan) {
                if (!$plan->recipe) {
                    continue;
                }

                foreach ($plan->recipe->ingredients as $ing) {
                    $key = $ing->id;
                    $qty = (float) ($ing->pivot->quantity ?? 1);
                    $unit = $ing->pivot->unit ?? $ing->unit;

                    if (!isset($ingredients[$key])) {
                        $ingredients[$key] = [
                            'ingredient_id' => $ing->id,
                            'name' => $ing->name,
                            'quantity' => 0,
                            'unit' => $unit,
                        ];
                    }
                    $ingredients[$key]['quantity'] += $qty;
                }
            }

            if (empty($ingredients)) {
                return [
                    'success' => false,
                    'result' => 'No ingredients found in the meal plans.',
                ];
            }

            // Create shopping list
            $list = ShoppingList::create([
                'user_id' => $userId,
                'name' => $name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'draft',
            ]);

            // Add items
            foreach ($ingredients as $data) {
                ShoppingListItem::create([
                    'shopping_list_id' => $list->id,
                    'ingredient_id' => $data['ingredient_id'],
                    'quantity' => $data['quantity'],
                    'unit' => $data['unit'],
                    'is_purchased' => false,
                ]);
            }

            $result = "🛒 Created shopping list '{$name}' with " . count($ingredients) . " items:\n";
            foreach (array_slice($ingredients, 0, 10) as $data) {
                $result .= "- {$data['name']}: {$data['quantity']} {$data['unit']}\n";
            }
            if (count($ingredients) > 10) {
                $result .= "... and " . (count($ingredients) - 10) . " more items";
            }

            return [
                'success' => true,
                'result' => $result,
                'data' => $list,
            ];
        } catch (\Exception $e) {
            Log::error('GenerateShoppingListTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to generate shopping list: ' . $e->getMessage(),
            ];
        }
    }
}
