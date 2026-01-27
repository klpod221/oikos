<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SystemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Income categories
            ['name' => 'Lương', 'type' => 'income', 'icon' => '💰', 'color' => '#22c55e'],
            ['name' => 'Nghề tự do', 'type' => 'income', 'icon' => '💻', 'color' => '#10b981'],
            ['name' => 'Đầu tư', 'type' => 'income', 'icon' => '📈', 'color' => '#14b8a6'],
            ['name' => 'Quà tặng', 'type' => 'income', 'icon' => '🎁', 'color' => '#06b6d4'],
            ['name' => 'Thưởng', 'type' => 'income', 'icon' => '🎉', 'color' => '#0ea5e9'],
            ['name' => 'Thu nhập khác', 'type' => 'income', 'icon' => '💵', 'color' => '#3b82f6'],

            // Expense categories
            ['name' => 'Ăn uống', 'type' => 'expense', 'icon' => '🍔', 'color' => '#ef4444'],
            ['name' => 'Đi chợ', 'type' => 'expense', 'icon' => '🛒', 'color' => '#f97316'],
            ['name' => 'Di chuyển', 'type' => 'expense', 'icon' => '🚗', 'color' => '#f59e0b'],
            ['name' => 'Hóa đơn & Tiện ích', 'type' => 'expense', 'icon' => '💡', 'color' => '#eab308'],
            ['name' => 'Tiền thuê nhà', 'type' => 'expense', 'icon' => '🏠', 'color' => '#84cc16'],
            ['name' => 'Mua sắm', 'type' => 'expense', 'icon' => '🛍️', 'color' => '#a855f7'],
            ['name' => 'Giải trí', 'type' => 'expense', 'icon' => '🎬', 'color' => '#d946ef'],
            ['name' => 'Sức khỏe', 'type' => 'expense', 'icon' => '🏥', 'color' => '#ec4899'],
            ['name' => 'Giáo dục', 'type' => 'expense', 'icon' => '📚', 'color' => '#f43f5e'],
            ['name' => 'Du lịch', 'type' => 'expense', 'icon' => '✈️', 'color' => '#6366f1'],
            ['name' => 'Bảo hiểm', 'type' => 'expense', 'icon' => '🛡️', 'color' => '#8b5cf6'],
            ['name' => 'Dịch vụ đăng ký', 'type' => 'expense', 'icon' => '📺', 'color' => '#0891b2'],
            ['name' => 'Chăm sóc cá nhân', 'type' => 'expense', 'icon' => '💇', 'color' => '#be185d'],
            ['name' => 'Chi phí khác', 'type' => 'expense', 'icon' => '📝', 'color' => '#64748b'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                    'scope' => Category::SCOPE_SYSTEM,
                ],
                [
                    'type' => $category['type'],
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'scope' => Category::SCOPE_SYSTEM,
                    'user_id' => null,
                ]
            );
        }
    }
}
