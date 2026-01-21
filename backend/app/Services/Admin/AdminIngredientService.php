<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminIngredientService
{
    /**
     * Get global ingredients
     */
    public function getIngredients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Ingredient::global()
            ->when(isset($filters['search']), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->orderBy('name')
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
