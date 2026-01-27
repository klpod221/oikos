<?php

namespace App\Services\Nutrition;

use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Nutrition Ingredient Service
 *
 * Service for managing ingredients (system global + user custom).
 *
 * @package App\Services\Nutrition
 */
class IngredientService
{
    /**
     * Get available ingredients (Global + User Custom)
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getIngredients(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Ingredient::availableFor($userId)
            ->applyFilters($filters)
            ->paginate($perPage);
    }

    /**
     * Create custom ingredient
     */
    public function createIngredient(int $userId, array $data): Ingredient
    {
        $data['user_id'] = $userId;
        $data['is_global'] = false;

        return Ingredient::create($data);
    }

    /**
     * Update custom ingredient
     */
    public function updateIngredient(Ingredient $ingredient, array $data): Ingredient
    {
        // Policy updates handled in controller/policy
        $ingredient->update($data);
        return $ingredient;
    }

    /**
     * Delete custom ingredient
     */
    public function deleteIngredient(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }
}
