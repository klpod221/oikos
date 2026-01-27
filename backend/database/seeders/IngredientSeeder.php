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

        // Vietnamese Common Ingredients
        $ingredients = [
            // Proteins
            ['name' => 'Thịt ba chỉ heo', 'unit' => 'g', 'calories' => 260, 'protein' => 16, 'carbs' => 0, 'fat' => 21, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Thịt bò thăn', 'unit' => 'g', 'calories' => 250, 'protein' => 26, 'carbs' => 0, 'fat' => 15, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Ức gà', 'unit' => 'g', 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Tôm sú', 'unit' => 'g', 'calories' => 99, 'protein' => 24, 'carbs' => 0.2, 'fat' => 0.3, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Trứng gà', 'unit' => 'pc', 'calories' => 70, 'protein' => 6, 'carbs' => 0.6, 'fat' => 5, 'fiber' => 0, 'sugar' => 0.6],
            ['name' => 'Đậu hũ (Đậu phụ)', 'unit' => 'g', 'calories' => 76, 'protein' => 8, 'carbs' => 1.9, 'fat' => 4.8, 'fiber' => 0.3, 'sugar' => 0],

            // Carbs
            ['name' => 'Gạo tẻ', 'unit' => 'g', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3, 'fiber' => 0.4, 'sugar' => 0.1],
            ['name' => 'Bánh phở', 'unit' => 'g', 'calories' => 110, 'protein' => 3, 'carbs' => 20, 'fat' => 1, 'fiber' => 0.5, 'sugar' => 0],
            ['name' => 'Bún tươi', 'unit' => 'g', 'calories' => 110, 'protein' => 2, 'carbs' => 25, 'fat' => 0.2, 'fiber' => 0.5, 'sugar' => 0],
            ['name' => 'Bánh mì', 'unit' => 'pc', 'calories' => 250, 'protein' => 8, 'carbs' => 49, 'fat' => 3, 'fiber' => 2.5, 'sugar' => 4],
            ['name' => 'Khoai lang', 'unit' => 'g', 'calories' => 86, 'protein' => 1.6, 'carbs' => 20, 'fat' => 0.1, 'fiber' => 3, 'sugar' => 4.2],

            // Vegetables
            ['name' => 'Rau muống', 'unit' => 'g', 'calories' => 19, 'protein' => 2.6, 'carbs' => 3.1, 'fat' => 0.2, 'fiber' => 2.1, 'sugar' => 0],
            ['name' => 'Bông cải xanh', 'unit' => 'g', 'calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fat' => 0.4, 'fiber' => 2.6, 'sugar' => 1.7],
            ['name' => 'Cà rốt', 'unit' => 'g', 'calories' => 41, 'protein' => 0.9, 'carbs' => 9.6, 'fat' => 0.2, 'fiber' => 2.8, 'sugar' => 4.7],
            ['name' => 'Dưa leo', 'unit' => 'g', 'calories' => 15, 'protein' => 0.7, 'carbs' => 3.6, 'fat' => 0.1, 'fiber' => 0.5, 'sugar' => 1.7],
            ['name' => 'Cà chua', 'unit' => 'g', 'calories' => 18, 'protein' => 0.9, 'carbs' => 3.9, 'fat' => 0.2, 'fiber' => 1.2, 'sugar' => 2.6],

            // Condiments/Others
            ['name' => 'Nước mắm', 'unit' => 'ml', 'calories' => 10, 'protein' => 2, 'carbs' => 0, 'fat' => 0, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Dầu ăn', 'unit' => 'ml', 'calories' => 884, 'protein' => 0, 'carbs' => 0, 'fat' => 100, 'fiber' => 0, 'sugar' => 0],
            ['name' => 'Bí đao', 'unit' => 'g', 'calories' => 13, 'protein' => 0.4, 'carbs' => 3, 'fat' => 0.2, 'fiber' => 2.9, 'sugar' => 0],
            ['name' => 'Giá đỗ', 'unit' => 'g', 'calories' => 30, 'protein' => 3, 'carbs' => 6, 'fat' => 0.2, 'fiber' => 1.9, 'sugar' => 4],
            ['name' => 'Nước dừa tươi', 'unit' => 'ml', 'calories' => 19, 'protein' => 0.7, 'carbs' => 3.7, 'fat' => 0.2, 'fiber' => 1.1, 'sugar' => 2.6],
        ];

        foreach ($ingredients as $ing) {
            Ingredient::firstOrCreate(
                ['name' => $ing['name'], 'user_id' => $user->id],
                array_merge($ing, ['user_id' => $user->id, 'is_global' => true])
            );
        }
    }
}
