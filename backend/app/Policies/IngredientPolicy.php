<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;

/**
 * Ingredient Policy
 *
 * Authorization policy for Ingredient model.
 *
 * @package App\Policies
 */
class IngredientPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Ingredient $ingredient
     * @return bool
     */
    public function view(User $user, Ingredient $ingredient): bool
    {
        return $ingredient->user_id === $user->id || $ingredient->is_global;
    }

    public function create(User $__): bool
    {
        return true;
    }

    public function update(User $user, Ingredient $ingredient): bool
    {
        return $ingredient->user_id === $user->id;
    }

    public function delete(User $user, Ingredient $ingredient): bool
    {
        return $ingredient->user_id === $user->id;
    }
}
