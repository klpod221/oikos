<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Admin Ingredient Service
 *
 * Service for administrative management of global ingredients.
 *
 * @package App\Services\Admin
 */
class AdminIngredientService
{
    /**
     * Get global ingredients
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getIngredients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Ingredient::global()
            ->applyFilters($filters)
            ->paginate($perPage);
    }

    /**
     * Create global ingredient
     */
    public function createIngredient(array $data): Ingredient
    {
        // Enforce global flag and no user owner
        $data['is_global'] = true;
        $data['user_id'] = null;

        return Ingredient::create($data);
    }

    /**
     * Update ingredient
     */
    public function updateIngredient(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update($data);
        return $ingredient;
    }

    /**
     * Delete ingredient
     */
    public function deleteIngredient(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }
}
