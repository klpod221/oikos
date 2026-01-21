<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();
        if (!$user) return;

        $recipes = [
            [
                'name' => 'Grilled Chicken Salad',
                'description' => 'Healthy and packed with protein.',
                'instructions' => "1. Season chicken breast with salt, pepper, and herbs.\n2. Grill for 6-7 mins per side.\n3. Chop lettuce, tomatoes, cucumbers.\n4. Slice chicken and serve over salad with olive oil dressing.",
                'prep_time' => 15,
                'cooking_time' => 15,
                'servings' => 2,
                'calories' => 350,
                'protein' => 40,
                'carbs' => 10,
                'fat' => 15,
            ],
            [
                'name' => 'Oatmeal with Banana',
                'description' => 'Perfect breakfast for energy.',
                'instructions' => "1. Cook oats with milk or water.\n2. Slice banana.\n3. Top oatmeal with banana and honey.",
                'prep_time' => 5,
                'cooking_time' => 10,
                'servings' => 1,
                'calories' => 450,
                'protein' => 15,
                'carbs' => 70,
                'fat' => 10,
            ],
            [
                'name' => 'Salmon & Broccoli',
                'description' => 'Simple and nutritious dinner.',
                'instructions' => "1. Season salmon.\n2. Steam broccoli.\n3. Pan sear salmon skin-down until crispy.\n4. Serve together.",
                'prep_time' => 10,
                'cooking_time' => 15,
                'servings' => 2,
                'calories' => 500,
                'protein' => 35,
                'carbs' => 12,
                'fat' => 25,
            ],
        ];

        foreach ($recipes as $recipe) {
            Recipe::create(array_merge($recipe, ['user_id' => $user->id]));
            // Future: Attach ingredients to recipe via pivot table if implemented
        }
    }
}
