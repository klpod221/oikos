<?php

namespace App\Services\Nutrition;

use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Recipe Service
 *
 * Service for managing recipes and recalculating nutrition.
 *
 * @package App\Services\Nutrition
 */
class RecipeService
{
    /**
     * Get recipes with filtering
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRecipes(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Recipe::where('user_id', $userId)
            ->with(['ingredients'])
            ->applyFilters($filters)
            ->paginate($perPage);
    }

    public function createRecipe(int $userId, array $data): Recipe
    {
        return DB::transaction(function () use ($userId, $data) {
            $ingredientsData = $data['ingredients'] ?? [];
            unset($data['ingredients']);

            $data['user_id'] = $userId;
            $recipe = Recipe::create($data);

            if (!empty($ingredientsData)) {
                $this->syncIngredients($recipe, $ingredientsData);
            }

            return $recipe->fresh(['ingredients']);
        });
    }

    public function updateRecipe(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $ingredientsData = $data['ingredients'] ?? null;
            if (isset($data['ingredients'])) {
                unset($data['ingredients']);
            }

            $recipe->update($data);

            if ($ingredientsData !== null) {
                $this->syncIngredients($recipe, $ingredientsData);
            }

            return $recipe->fresh(['ingredients']);
        });
    }

    public function deleteRecipe(Recipe $recipe): void
    {
        $recipe->delete();
    }

    protected function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        // Format for sync: [id => ['quantity' => 10, 'unit' => 'g'], ...]
        $syncData = [];
        foreach ($ingredients as $item) {
            $syncData[$item['id']] = [
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
            ];
        }
        $recipe->ingredients()->sync($syncData);

        // Recalculate nutrition
        $this->calculateNutrition($recipe);
    }

    protected function calculateNutrition(Recipe $recipe): void
    {
        $nutrition = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
        ];

        // Reload ingredients with pivot
        $recipe->load('ingredients');

        foreach ($recipe->ingredients as $ingredient) {
            // formula: ingredient_val * pivot_quantity.
            // Assumption: Ingredient value is 'per unit' or baseline 1.
            $qty = (float) $ingredient->pivot->quantity;

            $nutrition['calories'] += ($ingredient->calories ?? 0) * $qty;
            $nutrition['protein'] += ($ingredient->protein ?? 0) * $qty;
            $nutrition['carbs'] += ($ingredient->carbs ?? 0) * $qty;
            $nutrition['fat'] += ($ingredient->fat ?? 0) * $qty;
        }

        $recipe->update($nutrition);
    }
}
