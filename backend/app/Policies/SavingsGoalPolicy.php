<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

/**
 * Savings Goal Policy
 *
 * Authorization policy for SavingsGoal model.
 *
 * @package App\Policies
 */
class SavingsGoalPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param SavingsGoal $savingsGoal
     * @return bool
     */
    public function view(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
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
    public function update(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
    }
}
