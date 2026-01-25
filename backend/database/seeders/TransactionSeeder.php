<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();
        if (!$user) {
            return;
        }

        $cash = Wallet::where('user_id', $user->id)->where('name', 'Tiền mặt')->first();
        $bank = Wallet::where('user_id', $user->id)->where('name', 'Vietcombank')->first();

        // Get some categories
        $food = Category::where('name', 'Ăn uống')->first();
        $transport = Category::where('name', 'Di chuyển')->first();
        $salary = Category::where('name', 'Lương')->first();
        $shopping = Category::where('name', 'Mua sắm')->first();

        if (!$cash || !$bank) {
            return;
        }

        $transactions = [
            [
                'wallet_id' => $bank->id,
                'category_id' => $salary->id ?? null,
                'type' => 'income',
                'amount' => 30000000,
                'description' => 'Lương tháng',
                'transaction_date' => now()->startOfMonth()->addDays(5)->toDateString(),
            ],
            [
                'wallet_id' => $cash->id,
                'category_id' => $food->id ?? null,
                'type' => 'expense',
                'amount' => 50000,
                'description' => 'Ăn trưa tại Phở 24',
                'transaction_date' => now()->subDays(1)->toDateString(),
            ],
            [
                'wallet_id' => $cash->id,
                'category_id' => $transport->id ?? null,
                'type' => 'expense',
                'amount' => 30000,
                'description' => 'GrabBike đi làm',
                'transaction_date' => now()->subDays(2)->toDateString(),
            ],
            [
                'wallet_id' => $bank->id,
                'category_id' => $shopping->id ?? null,
                'type' => 'expense',
                'amount' => 500000,
                'description' => 'Áo thun mới',
                'transaction_date' => now()->subDays(3)->toDateString(),
            ],
            [
                'wallet_id' => $bank->id,
                'category_id' => $food->id ?? null,
                'type' => 'expense',
                'amount' => 1500000,
                'description' => 'Mua sắm siêu thị',
                'transaction_date' => now()->subWeek()->toDateString(),
            ],
        ];

        foreach ($transactions as $data) {
            Transaction::create(array_merge($data, ['user_id' => $user->id]));
        }
    }
}
