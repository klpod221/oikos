<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

/**
 * Wallet Policy
 *
 * Authorization policy for Wallet model.
 *
 * @package App\Policies
 */
class WalletPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Wallet $wallet
     * @return bool
     */
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
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
    public function update(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
