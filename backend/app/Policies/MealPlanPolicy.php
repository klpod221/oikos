<?php

namespace App\Policies;

use App\Models\MealPlan;
use App\Models\User;

/**
 * Meal Plan Policy
 *
 * Authorization policy for MealPlan model.
 *
 * @package App\Policies
 */
class MealPlanPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param MealPlan $mealPlan
     * @return bool
     */
    public function view(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }

    public function create(User $__): bool
    {
        return true;
    }

    public function update(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }

    public function delete(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }
}
