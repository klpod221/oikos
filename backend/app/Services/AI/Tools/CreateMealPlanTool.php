<?php

namespace App\Services\AI\Tools;

use App\Models\MealPlan;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;

/**
 * Tool to create a meal plan.
 */
class CreateMealPlanTool extends AITool
{
    public function name(): string
    {
        return 'create_meal_plan';
    }

    public function description(): string
    {
        return 'Create a meal plan entry. Assigns a recipe to a specific meal on a date. Use get_recipes first to find recipe names.';
    }

    public function dependsOn(): array
    {
        return ['get_recipes'];
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'description' => 'Date for the meal plan (YYYY-MM-DD)',
                ],
                'meal_type' => [
                    'type' => 'string',
                    'enum' => ['breakfast', 'lunch', 'dinner', 'snack'],
                    'description' => 'Type of meal',
                ],
                'recipe_name' => [
                    'type' => 'string',
                    'description' => 'Name of the recipe to plan',
                ],
            ],
            'required' => ['date', 'meal_type', 'recipe_name'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $date = $arguments['date'] ?? null;
            $mealType = $arguments['meal_type'] ?? null;
            $recipeName = $arguments['recipe_name'] ?? null;

            if (!$date || !$mealType || !$recipeName) {
                return [
                    'success' => false,
                    'result' => 'Missing required fields: date, meal_type, or recipe_name.',
                ];
            }

            $recipe = Recipe::where('user_id', $userId)
                ->where('name', 'like', "%{$recipeName}%")
                ->first();

            if (!$recipe) {
                return [
                    'success' => false,
                    'result' => "Recipe '{$recipeName}' not found.",
                ];
            }

            $plan = MealPlan::create([
                'user_id' => $userId,
                'date' => $date,
                'meal_type' => $mealType,
                'recipe_id' => $recipe->id,
                'status' => 'planned',
            ]);

            return [
                'success' => true,
                'result' => "Added '{$recipe->name}' as {$mealType} on {$date}.",
                'data' => $plan,
            ];
        } catch (\Exception $e) {
            Log::error('CreateMealPlanTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to create meal plan: ' . $e->getMessage(),
            ];
        }
    }
}
