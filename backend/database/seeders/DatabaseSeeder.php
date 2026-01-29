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
            SystemCategorySeeder::class,
            // IngredientSeeder::class,
            // UserStatsSeeder::class,
            // WalletSeeder::class,
            // TransactionSeeder::class,
            // SavingsGoalSeeder::class,
            // RecipeSeeder::class,
            // MealPlanSeeder::class,
            // ExerciseSeeder::class,
            // RoutineSeeder::class,
        ]);
    }
}
