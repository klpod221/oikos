<?php

namespace App\Services\Nutrition;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Collection;

class IngredientService
{
    /**
     * Get available ingredients (Global + User Custom)
     */
    public function getIngredients(int $userId): Collection
    {
        return Ingredient::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_global', true);
        })
            ->orderBy('name')
            ->get();
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
