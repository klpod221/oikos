<?php

namespace Database\Seeders;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Seeder;

class SavingsGoalSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();
        if (!$user) return;

        $goals = [
            [
                'name' => 'MacBook Pro M4',
                'description' => 'For coding and video editing',
                'target_amount' => 50000000,
                'current_amount' => 15000000,
                'currency' => 'VND',
                'start_date' => now()->subMonths(2)->toDateString(),
                'deadline' => now()->addMonths(4)->toDateString(), // 6 months plan
                'status' => 'in_progress',
                'icon' => '💻',
                'color' => '#8b5cf6',
            ],
            [
                'name' => 'Japan Trip',
                'description' => 'Cherry blossom season',
                'target_amount' => 45000000,
                'current_amount' => 5000000,
                'currency' => 'VND',
                'start_date' => now()->subMonth()->toDateString(),
                'deadline' => now()->addMonths(10)->toDateString(),
                'status' => 'in_progress',
                'icon' => '🌸',
                'color' => '#ec4899',
            ],
            [
                'name' => 'Emergency Fund',
                'description' => '3 months of expenses',
                'target_amount' => 60000000,
                'current_amount' => 60000000,
                'currency' => 'VND',
                'start_date' => now()->subYear()->toDateString(),
                'deadline' => now()->subMonth()->toDateString(),
                'status' => 'completed',
                'icon' => '🛡️',
                'color' => '#10b981',
            ],
        ];

        foreach ($goals as $goal) {
            SavingsGoal::create(array_merge($goal, ['user_id' => $user->id]));
        }
    }
}
