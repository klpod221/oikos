<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

/**
 * Recipe Policy
 *
 * Authorization policy for Recipe model.
 *
 * @package App\Policies
 */
class RecipePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Recipe $recipe
     * @return bool
     */
    public function view(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function create(User $__): bool
    {
        return true;
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }
}
