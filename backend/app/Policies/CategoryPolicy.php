<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Category Policy
 *
 * Authorization policy for Category model.
 *
 * @package App\Policies
 */
class CategoryPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function view(User $user, Category $category): bool
    {
        return $category->user_id === $user->id || $category->scope === 'system';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $__): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }
}
