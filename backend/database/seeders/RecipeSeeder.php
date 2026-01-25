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
        if (!$user) {
            return;
        }

        $recipes = [
            [
                'name' => 'Salad ức gà nướng',
                'description' => 'Lành mạnh và giàu protein.',
                'instructions' => "1. Ướp ức gà với muối, tiêu và thảo mộc.\n2. Nướng 6-7 phút mỗi mặt.\n3. Cắt nhỏ xà lách, cà chua, dưa chuột.\n4. Thái thịt gà và trộn cùng salad với sốt dầu ô liu.",
                'prep_time' => 15,
                'cooking_time' => 15,
                'servings' => 2,
                'calories' => 350,
                'protein' => 40,
                'carbs' => 10,
                'fat' => 15,
            ],
            [
                'name' => 'Yến mạch chuối',
                'description' => 'Bữa sáng hoàn hảo đầy năng lượng.',
                'instructions' => "1. Nấu yến mạch với sữa hoặc nước.\n2. Cắt lát chuối.\n3. Cho chuối và mật ong lên trên yến mạch.",
                'prep_time' => 5,
                'cooking_time' => 10,
                'servings' => 1,
                'calories' => 450,
                'protein' => 15,
                'carbs' => 70,
                'fat' => 10,
            ],
            [
                'name' => 'Cá hồi & Bông cải xanh',
                'description' => 'Bữa tối đơn giản và bổ dưỡng.',
                'instructions' => "1. Ướp cá hồi.\n2. Hấp bông cải xanh.\n3. Áp chảo cá hồi mặt da xuống cho đến khi giòn.\n4. Dùng kèm với nhau.",
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
