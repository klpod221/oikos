<?php

namespace App\Services\Finance;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\StatisticsService;

class TransactionService
{
    /**
     * Get transactions with filtering
     */
    public function getTransactions(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::where('user_id', $userId)
            ->with(['category', 'wallet'])
            ->applyFilters($filters)
            ->paginate($perPage);
    }

    /**
     * Create transaction and update wallet balance
     */
    public function createTransaction(int $userId, array $data): Transaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $data['user_id'] = $userId;

            $transaction = Transaction::create($data);

            // Update wallet balance
            $wallet = Wallet::where('user_id', $userId)->findOrFail($data['wallet_id']);

            if ($transaction->type === Transaction::TYPE_INCOME) {
                $wallet->addBalance((float) $transaction->amount);
            } else {
                $wallet->subtractBalance((float) $transaction->amount);
            }

            // Invalidate statistics cache
            app(StatisticsService::class)->invalidateCache($userId);

            return $transaction;
        });
    }

    /**
     * Update transaction and adjust wallet balances
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // 1. Revert effect of old transaction
            $oldWallet = $transaction->wallet;
            if ($transaction->type === Transaction::TYPE_INCOME) {
                $oldWallet->subtractBalance((float) $transaction->amount);
            } else {
                $oldWallet->addBalance((float) $transaction->amount);
            }

            // 2. Update transaction details
            $transaction->update($data);

            // Refresh model to get updated attributes
            $transaction->refresh();

            // 3. Apply effect of new transaction
            // We need to fetch the wallet again in case wallet_id changed
            $newWallet = $transaction->wallet;

            if ($transaction->type === Transaction::TYPE_INCOME) {
                $newWallet->addBalance((float) $transaction->amount);
            } else {
                $newWallet->subtractBalance((float) $transaction->amount);
            }

            // Invalidate statistics cache
            app(StatisticsService::class)->invalidateCache($transaction->user_id);

            return $transaction;
        });
    }

    /**
     * Delete transaction and revert wallet balance
     */
    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;

            // Revert balance
            if ($transaction->type === Transaction::TYPE_INCOME) {
                $wallet->subtractBalance((float) $transaction->amount);
            } else {
                $wallet->addBalance((float) $transaction->amount);
            }

            $transaction->delete();

            // Invalidate statistics cache
            app(StatisticsService::class)->invalidateCache($transaction->user_id);
        });
    }
}
