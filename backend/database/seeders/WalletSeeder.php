<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();
        if (!$user) return;

        $wallets = [
            [
                'name' => 'Cash',
                'balance' => 5000000,
                'currency' => 'VND',
                'description' => 'Daily expenses',
                'is_default' => true,
                'icon' => '💵',
                'color' => '#10b981',
            ],
            [
                'name' => 'Vietcombank',
                'balance' => 50000000,
                'currency' => 'VND',
                'description' => 'Salary account',
                'is_default' => false,
                'icon' => '🏦',
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Momo',
                'balance' => 2000000,
                'currency' => 'VND',
                'description' => 'E-wallet for bills',
                'is_default' => false,
                'icon' => '📱',
                'color' => '#ec4899',
            ],
            [
                'name' => 'Emergency Fund',
                'balance' => 1000,
                'currency' => 'USD',
                'description' => 'Foreign currency savings',
                'is_default' => false,
                'icon' => '💰',
                'color' => '#f59e0b',
            ],
        ];

        foreach ($wallets as $wallet) {
            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'name' => $wallet['name']],
                $wallet
            );
        }
    }
}
