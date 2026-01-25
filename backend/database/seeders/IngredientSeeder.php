<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();
        if (!$user) {
            return;
        }

        // Common Global Ingredients (User ID null or admin? Spec said is_global boolean)
        // Usually global ingredients don't belong to specific user, or belong to admin user with is_global=true
        // Here we assign to our admin user and mark is_global=true

        $ingredients = [
            ['name' => 'Ức gà', 'unit' => 'g', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Cơm trắng', 'unit' => 'g', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3, 'fiber' => 0.4, 'sugar' => 0.1],
            ['name' => 'Trứng (Lớn)', 'unit' => 'pc', 'calories' => 70, 'protein' => 6, 'carbs' => 0.6, 'fat' => 5, 'fiber' => 0, 'sugar' => 0.6],
            ['name' => 'Bông cải xanh', 'unit' => 'g', 'calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fat' => 0.4, 'fiber' => 2.6, 'sugar' => 1.7],
            ['name' => 'Dầu ô liu', 'unit' => 'ml', 'calories' => 884, 'protein' => 0, 'carbs' => 0, 'fat' => 100, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Chuối', 'unit' => 'pc', 'calories' => 105, 'protein' => 1.3, 'carbs' => 27, 'fat' => 0.3, 'fiber' => 3.1, 'sugar' => 14],
            ['name' => 'Yến mạch', 'unit' => 'g', 'calories' => 389, 'protein' => 16.9, 'carbs' => 66, 'fat' => 6.9, 'fiber' => 10.6, 'sugar' => 0],
            ['name' => 'Sữa tươi nguyên kem', 'unit' => 'ml', 'calories' => 61, 'protein' => 3.2, 'carbs' => 4.8, 'fat' => 3.3, 'fiber' => 0, 'sugar' => 5],
            ['name' => 'Phi lê cá hồi', 'unit' => 'g', 'calories' => 208, 'protein' => 20, 'carbs' => 0, 'fat' => 13, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Bơ', 'unit' => 'pc', 'calories' => 240, 'protein' => 3, 'carbs' => 12, 'fat' => 22, 'fiber' => 10, 'sugar' => 1],
        ];

        foreach ($ingredients as $ing) {
            Ingredient::firstOrCreate(
                ['name' => $ing['name'], 'user_id' => $user->id],
                array_merge($ing, ['user_id' => $user->id, 'is_global' => true])
            );
        }
    }
}
