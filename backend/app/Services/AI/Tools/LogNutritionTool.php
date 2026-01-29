<?php

namespace App\Services\AI\Tools;

use App\Models\NutritionLog;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * Tool to log food intake.
 */
class LogNutritionTool extends AITool
{
    public function name(): string
    {
        return 'log_nutrition';
    }

    public function description(): string
    {
        return 'Log a food entry for nutrition tracking. Can log a recipe or ingredient with quantity.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'description' => 'Date of the meal (YYYY-MM-DD). Default: today',
                ],
                'meal_type' => [
                    'type' => 'string',
                    'enum' => ['breakfast', 'lunch', 'dinner', 'snack'],
                    'description' => 'Type of meal',
                ],
                'recipe_name' => [
                    'type' => 'string',
                    'description' => 'Name of recipe eaten (use this OR ingredient_name)',
                ],
                'ingredient_name' => [
                    'type' => 'string',
                    'description' => 'Name of ingredient eaten (use this OR recipe_name)',
                ],
                'servings' => [
                    'type' => 'number',
                    'description' => 'Number of servings (default: 1)',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Quantity in grams (for ingredients)',
                ],
            ],
            'required' => ['meal_type'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $date = $arguments['date'] ?? Carbon::today()->toDateString();
            $mealType = $arguments['meal_type'];
            $servings = (float) ($arguments['servings'] ?? 1);
            $quantity = (float) ($arguments['quantity'] ?? 0);

            $logData = [
                'user_id' => $userId,
                'date' => $date,
                'meal_type' => $mealType,
                'servings' => $servings,
            ];

            $itemName = '';

            if (!empty($arguments['recipe_name'])) {
                $recipe = Recipe::where('user_id', $userId)
                    ->where('name', 'like', "%{$arguments['recipe_name']}%")
                    ->first();

                if (!$recipe) {
                    return ['success' => false, 'result' => "Recipe '{$arguments['recipe_name']}' not found."];
                }

                $logData['recipe_id'] = $recipe->id;
                $logData['calories'] = ($recipe->calories ?? 0) * $servings;
                $logData['protein'] = ($recipe->protein ?? 0) * $servings;
                $logData['carbs'] = ($recipe->carbs ?? 0) * $servings;
                $logData['fat'] = ($recipe->fat ?? 0) * $servings;
                $itemName = $recipe->name;
            } elseif (!empty($arguments['ingredient_name'])) {
                $ingredient = Ingredient::availableFor($userId)
                    ->where('name', 'like', "%{$arguments['ingredient_name']}%")
                    ->first();

                if (!$ingredient) {
                    return ['success' => false, 'result' => "Ingredient '{$arguments['ingredient_name']}' not found."];
                }

                $multiplier = $quantity > 0 ? $quantity / 100 : $servings;
                $logData['ingredient_id'] = $ingredient->id;
                $logData['quantity'] = $quantity ?: 100;
                $logData['calories'] = ($ingredient->calories ?? 0) * $multiplier;
                $logData['protein'] = ($ingredient->protein ?? 0) * $multiplier;
                $logData['carbs'] = ($ingredient->carbs ?? 0) * $multiplier;
                $logData['fat'] = ($ingredient->fat ?? 0) * $multiplier;
                $itemName = $ingredient->name;
            } else {
                return ['success' => false, 'result' => 'Please specify recipe_name or ingredient_name.'];
            }

            $log = NutritionLog::create($logData);

            $calories = number_format($logData['calories'], 0);

            return [
                'success' => true,
                'result' => "Logged {$itemName} for {$mealType} on {$date} ({$calories} kcal).",
                'data' => $log,
            ];
        } catch (\Exception $e) {
            Log::error('LogNutritionTool failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'result' => 'Failed to log nutrition: ' . $e->getMessage()];
        }
    }
}
