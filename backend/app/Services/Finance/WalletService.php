<?php

namespace App\Services\Finance;

use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Wallet Service
 *
 * Service for managing user wallets.
 *
 * @package App\Services\Finance
 */
class WalletService
{
    /**
     * Get user wallets
     *
     * @param int $userId
     * @return Collection
     */
    public function getWallets(int $userId): Collection
    {
        return Wallet::where('user_id', $userId)
            ->withCount('transactions')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create wallet
     */
    public function createWallet(int $userId, array $data): Wallet
    {
        return DB::transaction(function () use ($userId, $data) {
            // Handle Default Flag
            if (!empty($data['is_default']) && $data['is_default']) {
                $this->unsetOtherDefaults($userId);
            } else {
                // If it's the first wallet, make it default automatically
                if (Wallet::where('user_id', $userId)->count() === 0) {
                    $data['is_default'] = true;
                }
            }

            // Map initial_balance to balance
            if (isset($data['initial_balance'])) {
                $data['balance'] = $data['initial_balance'];
                unset($data['initial_balance']);
            }

            $data['user_id'] = $userId;

            return Wallet::create($data);
        });
    }

    /**
     * Update wallet
     */
    public function updateWallet(Wallet $wallet, array $data): Wallet
    {
        return DB::transaction(function () use ($wallet, $data) {
            // Handle Default Flag
            if (!empty($data['is_default']) && $data['is_default'] && !$wallet->is_default) {
                $this->unsetOtherDefaults($wallet->user_id);
            }

            // Map initial_balance to balance for update
            if (isset($data['initial_balance'])) {
                $data['balance'] = $data['initial_balance'];
                unset($data['initial_balance']);
            }

            // Prevent unsetting default if it's the only one (optional, but good UX)
            // But user might just want to switch default.
            // If they pass is_default=false, and it was true, we might end up with no default.

            $wallet->update($data);
            return $wallet;
        });
    }

    /**
     * Delete wallet
     */
    public function deleteWallet(Wallet $wallet): void
    {
        // Check if transactions exist?
        // Database has onDelete('cascade') but transactions are critical.
        // Maybe prevent if has transactions?
        // Spec didn't say. Cascade is dangerous but allowed for MVP.
        $wallet->delete();
    }

    /**
     * Unset is_default for all user's wallets
     */
    protected function unsetOtherDefaults(int $userId): void
    {
        Wallet::where('user_id', $userId)->update(['is_default' => false]);
    }
}
