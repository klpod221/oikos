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
            ['name' => 'Salary', 'type' => 'income', 'icon' => '💰', 'color' => '#22c55e'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => '💻', 'color' => '#10b981'],
            ['name' => 'Investment', 'type' => 'income', 'icon' => '📈', 'color' => '#14b8a6'],
            ['name' => 'Gift', 'type' => 'income', 'icon' => '🎁', 'color' => '#06b6d4'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => '🎉', 'color' => '#0ea5e9'],
            ['name' => 'Other Income', 'type' => 'income', 'icon' => '💵', 'color' => '#3b82f6'],

            // Expense categories
            ['name' => 'Food & Dining', 'type' => 'expense', 'icon' => '🍔', 'color' => '#ef4444'],
            ['name' => 'Groceries', 'type' => 'expense', 'icon' => '🛒', 'color' => '#f97316'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => '🚗', 'color' => '#f59e0b'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => '💡', 'color' => '#eab308'],
            ['name' => 'Rent', 'type' => 'expense', 'icon' => '🏠', 'color' => '#84cc16'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => '🛍️', 'color' => '#a855f7'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => '🎬', 'color' => '#d946ef'],
            ['name' => 'Healthcare', 'type' => 'expense', 'icon' => '🏥', 'color' => '#ec4899'],
            ['name' => 'Education', 'type' => 'expense', 'icon' => '📚', 'color' => '#f43f5e'],
            ['name' => 'Travel', 'type' => 'expense', 'icon' => '✈️', 'color' => '#6366f1'],
            ['name' => 'Insurance', 'type' => 'expense', 'icon' => '🛡️', 'color' => '#8b5cf6'],
            ['name' => 'Subscriptions', 'type' => 'expense', 'icon' => '📺', 'color' => '#0891b2'],
            ['name' => 'Personal Care', 'type' => 'expense', 'icon' => '💇', 'color' => '#be185d'],
            ['name' => 'Other Expense', 'type' => 'expense', 'icon' => '📝', 'color' => '#64748b'],
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
