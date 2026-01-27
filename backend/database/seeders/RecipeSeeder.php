<?php

namespace Database\Seeders;

use App\Models\Ingredient;
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
                'name' => 'Phở Bò',
                'description' => 'Món ăn quốc hồn quốc túy của Việt Nam với nước dùng đậm đà.',
                'instructions' => "1. Ninh xương bò 6-8 tiếng lấy nước dùng.\n2. Nướng gừng, hành tím, hoa hồi, quế cho thơm rồi thả vào nồi nước dùng.\n3. Trần bánh phở qua nước sôi.\n4. Xếp thịt bò tái/chín lên bánh phở.\n5. Chan nước dùng nóng hổi và thêm hành lá, rau thơm.",
                'prep_time' => 30,
                'cooking_time' => 360,
                'servings' => 4,
                'calories' => 450,
                'protein' => 25,
                'carbs' => 60,
                'fat' => 12,
            ],
            [
                'name' => 'Bánh Mì Ốp La',
                'description' => 'Bữa sáng nhanh gọn và đầy năng lượng.',
                'instructions' => "1. Làm nóng chảo với chút dầu ăn.\n2. Ốp la 2 quả trứng gà (chín hoặc lòng đào tùy thích).\n3. Nướng giòn bánh mì.\n4. Ăn kèm dưa leo, cà chua và chút nước tương.",
                'prep_time' => 5,
                'cooking_time' => 5,
                'servings' => 1,
                'calories' => 350,
                'protein' => 15,
                'carbs' => 40,
                'fat' => 14,
            ],
            [
                'name' => 'Cơm Tấm Sườn Bì',
                'description' => 'Đặc sản Sài Gòn không thể bỏ qua.',
                'instructions' => "1. Ướp sườn cốt lết với tỏi, hành, mật ong, nước mắm và nướng than hoa.\n2. Nấu gạo tấm thành cơm.\n3. Làm nước mắm chua ngọt.\n4. Bì heo trộn thính.\n5. Xếp cơm, sườn, bì, chả trứng, mỡ hành ra đĩa.",
                'prep_time' => 60,
                'cooking_time' => 30,
                'servings' => 2,
                'calories' => 650,
                'protein' => 35,
                'carbs' => 80,
                'fat' => 20,
            ],
            [
                'name' => 'Bún Chả',
                'description' => 'Món ăn đặc trưng của Hà Nội.',
                'instructions' => "1. Thịt ba chỉ và nạc vai xay ướp gia vị, nướng than hoa.\n2. Pha nước chấm chua ngọt với đu đủ, cà rốt.\n3. Luộc bún.\n4. Ăn kèm rau sống tươi mát.",
                'prep_time' => 40,
                'cooking_time' => 20,
                'servings' => 4,
                'calories' => 550,
                'protein' => 28,
                'carbs' => 65,
                'fat' => 18,
            ],
            [
                'name' => 'Gà Kho Gừng',
                'description' => 'Món ăn gia đình ấm cúng.',
                'instructions' => "1. Gà chặt miếng vừa ăn, ướp gia vị.\n2. Phi thơm gừng thái sợi.\n3. Cho gà vào xào săn, thêm nước màu và chút nước.\n4. Kho lửa nhỏ đến khi sệt lại.",
                'prep_time' => 15,
                'cooking_time' => 25,
                'servings' => 3,
                'calories' => 300,
                'protein' => 25,
                'carbs' => 5,
                'fat' => 15,
            ],
            [
                'name' => 'Canh Chua Cá Lóc',
                'description' => 'Thanh mát giải nhiệt ngày hè.',
                'instructions' => "1. Cá lóc làm sạch, cắt khúc.\n2. Nấu nước sôi, cho cá vào nấu chín.\n3. Thêm me chua, thơm, cà chua, đậu bắp, bạc hà, giá đỗ.\n4. Nêm gia vị chua ngọt vừa ăn, thêm rau om, ngò gai.",
                'prep_time' => 20,
                'cooking_time' => 15,
                'servings' => 4,
                'calories' => 150,
                'protein' => 18,
                'carbs' => 10,
                'fat' => 4,
            ],
            [
                'name' => 'Rau Muống Xào Tỏi',
                'description' => 'Món rau quốc dân, đơn giản mà đưa cơm.',
                'instructions' => "1. Rau muống nhặt sạch, luộc sơ qua nước sôi.\n2. Phi thơm tỏi đập dập với dầu ăn.\n3. Cho rau vào xào lửa lớn, nêm gia vị vừa ăn.\n4. Tắt bếp, vắt thêm chút chanh nếu thích.",
                'prep_time' => 10,
                'cooking_time' => 5,
                'servings' => 2,
                'calories' => 120,
                'protein' => 4,
                'carbs' => 8,
                'fat' => 9,
            ],
            [
                'name' => 'Dưa Giá Đỗ',
                'description' => 'Món ăn kèm thanh mát, giòn ngon.',
                'instructions' => "1. Rửa sạch giá đỗ, hẹ, cà rốt thái sợi.\n2. Pha nước muối đường chua ngọt.\n3. Ngâm hỗn hợp rau vào nước muối đường khoảng 4-6 tiếng là dùng được.",
                'prep_time' => 15,
                'cooking_time' => 0,
                'servings' => 4,
                'calories' => 45,
                'protein' => 3,
                'carbs' => 9,
                'fat' => 0.2,
            ],
            [
                'name' => 'Thịt Kho Tàu',
                'description' => 'Món ăn không thể thiếu trong ngày Tết.',
                'instructions' => "1. Thịt ba chỉ cắt miếng to, ướp gia vị và nước mắm.\n2. Luộc trứng vịt, bóc vỏ.\n3. Kho thịt với nước dừa tươi đun liu riu đến khi thịt mềm rục.\n4. Cho trứng vào kho cùng thêm 15 phút.",
                'prep_time' => 30,
                'cooking_time' => 90,
                'servings' => 4,
                'calories' => 600,
                'protein' => 30,
                'carbs' => 5,
                'fat' => 45,
            ],
            [
                'name' => 'Canh Bí Đao Nấu Tôm',
                'description' => 'Món canh thanh nhiệt, ngọt mát.',
                'instructions' => "1. Bí đao gọt vỏ, cắt miếng vừa ăn.\n2. Tôm băm nhỏ hoặc giã dập, ướp gia vị.\n3. Đun sôi nước, cho tôm vào nấu chín.\n4. Thả bí đao vào nấu đến khi vừa chín tới, thêm hành ngò.",
                'prep_time' => 10,
                'cooking_time' => 15,
                'servings' => 3,
                'calories' => 80,
                'protein' => 12,
                'carbs' => 6,
                'fat' => 2,
            ],
        ];

        foreach ($recipes as $data) {
            $recipe = Recipe::create(array_merge($data, ['user_id' => $user->id]));

            // Define ingredients mappings (Recipe Name => [Ingredient Name => Quantity])
            $ingredientsMap = [
                'Phở Bò' => [
                    'Bánh phở' => 200, // g
                    'Thịt bò thăn' => 100, // g
                ],
                'Bánh Mì Ốp La' => [
                    'Bánh mì' => 1, // pc
                    'Trứng gà' => 2, // pc
                    'Dưa leo' => 50, // g
                ],
                'Cơm Tấm Sườn Bì' => [
                    'Gạo tẻ' => 150, // g
                    'Thịt ba chỉ heo' => 100, // g
                    'Nước mắm' => 20, // ml
                ],
                'Bún Chả' => [
                    'Bún tươi' => 200, // g
                    'Thịt ba chỉ heo' => 150, // g
                    'Nước mắm' => 30, // ml
                ],
                'Gà Kho Gừng' => [
                    'Ức gà' => 200, // g
                    'Nước mắm' => 10, // ml
                    'Dầu ăn' => 5, // ml
                ],
                'Canh Chua Cá Lóc' => [
                    'Nước mắm' => 15, // ml
                ],
                'Rau Muống Xào Tỏi' => [
                    'Rau muống' => 300, // g
                    'Dầu ăn' => 10, // ml
                ],
                'Dưa Giá Đỗ' => [
                    'Giá đỗ' => 300, // g
                    'Cà rốt' => 50, // g
                ],
                'Thịt Kho Tàu' => [
                    'Thịt ba chỉ heo' => 300, // g
                    'Trứng gà' => 2, // pc
                    'Nước dừa tươi' => 300, // ml
                    'Nước mắm' => 30, // ml
                ],
                'Canh Bí Đao Nấu Tôm' => [
                    'Bí đao' => 200, // g
                    'Tôm sú' => 100, // g
                ],
                'Salad ức gà nướng' => [
                    'Ức gà' => 150, // g
                    'Dầu ô liu' => 10, // ml
                ],
                'Yến mạch chuối' => [
                    'Yến mạch' => 50, // g
                    'Chuối' => 1, // pc
                    'Sữa tươi nguyên kem' => 200, // ml
                ],
                'Cá hồi & Bông cải xanh' => [
                    'Phi lê cá hồi' => 200, // g
                    'Bông cải xanh' => 150, // g
                    'Dầu ô liu' => 10, // ml
                ],
            ];

            if (isset($ingredientsMap[$recipe->name])) {
                foreach ($ingredientsMap[$recipe->name] as $ingName => $qty) {
                    $ingredient = Ingredient::where('name', $ingName)->first();
                    if ($ingredient) {
                        $recipe->ingredients()->attach($ingredient->id, [
                            'quantity' => $qty,
                            'unit' => $ingredient->unit,
                        ]);
                    }
                }
            }
        }
    }
}
