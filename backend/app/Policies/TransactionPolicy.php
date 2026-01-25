<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * Transaction Policy
 *
 * Authorization policy for Transaction model.
 *
 * @package App\Policies
 */
class TransactionPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
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
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
