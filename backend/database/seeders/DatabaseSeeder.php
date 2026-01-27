<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            UserStatsSeeder::class, // Stats for admin
            SystemCategorySeeder::class,
            WalletSeeder::class,
            TransactionSeeder::class,
            SavingsGoalSeeder::class,
            IngredientSeeder::class,
            RecipeSeeder::class,
            MealPlanSeeder::class,
            ExerciseSeeder::class,
            RoutineSeeder::class,
        ]);
    }
}
