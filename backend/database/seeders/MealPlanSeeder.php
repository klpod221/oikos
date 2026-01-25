<?php

namespace Database\Seeders;

use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MealPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'klpod221@gmail.com')->first();
        if (!$admin)
            return;

        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek();

        // Get some Vietnamese recipes
        $phoBo = Recipe::where('name', 'Phở Bò')->first();
        $banhMi = Recipe::where('name', 'Bánh Mì Ốp La')->first();
        $comTam = Recipe::where('name', 'Cơm Tấm Sườn Bì')->first();
        $bunCha = Recipe::where('name', 'Bún Chả')->first();

        $plans = [
            // Monday
            [
                'user_id' => $admin->id,
                'date' => $startOfWeek->copy()->format('Y-m-d'),
                'meal_type' => 'breakfast',
                'recipe_id' => $banhMi?->id,
                'description' => $banhMi ? null : 'Bánh mì pate',
                'status' => 'completed',
            ],
            [
                'user_id' => $admin->id,
                'date' => $startOfWeek->copy()->format('Y-m-d'),
                'meal_type' => 'lunch',
                'recipe_id' => $comTam?->id,
                'description' => $comTam ? null : 'Cơm tấm',
                'status' => 'completed',
            ],
            // Tuesday
            [
                'user_id' => $admin->id,
                'date' => $startOfWeek->copy()->addDay()->format('Y-m-d'),
                'meal_type' => 'breakfast',
                'recipe_id' => $phoBo?->id,
                'description' => $phoBo ? null : 'Phở bò',
                'status' => 'completed',
            ],
            [
                'user_id' => $admin->id,
                'date' => $startOfWeek->copy()->addDay()->format('Y-m-d'),
                'meal_type' => 'dinner',
                'recipe_id' => $bunCha?->id,
                'description' => $bunCha ? null : 'Bún chả',
                'status' => 'planned',
            ],
            // Wednesday (Manual entry)
            [
                'user_id' => $admin->id,
                'date' => $startOfWeek->copy()->addDays(2)->format('Y-m-d'),
                'meal_type' => 'breakfast',
                'recipe_id' => null,
                'description' => 'Xôi xéo',
                'status' => 'planned',
            ],
        ];

        foreach ($plans as $plan) {
            MealPlan::create($plan);
        }
    }
}
