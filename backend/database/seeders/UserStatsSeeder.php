<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserGoals;
use App\Models\UserStats;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserStatsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();

        if (!$user) {
            return;
        }

        // Seed Stats
        UserStats::updateOrCreate(
            [
                'user_id' => $user->id,
                'recorded_at' => Carbon::today(),
            ],
            [
                'weight' => 70.5,
                'height' => 175,
                'age' => 28,
                'gender' => 'male',
                'activity_level' => 'moderately_active',
            ]
        );

        // Seed Goals
        UserGoals::updateOrCreate(
            ['user_id' => $user->id],
            [
                'goal_type' => 'gain_muscle',
                'target_calories' => 2500,
                'target_protein' => 160,
                'target_carbs' => 300,
                'target_fat' => 70,
                'weekly_workout_target' => 4,
            ]
        );
    }
}
