<?php

namespace App\Services\Finance;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Category Service
 *
 * Service for managing user categories (income/expense).
 *
 * @package App\Services\Finance
 */
class CategoryService
{
    /**
     * Get available categories for user (System + Custom)
     *
     * @param int $userId
     * @return Collection
     */
    public function getCategories(int $userId): Collection
    {
        return Category::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('scope', Category::SCOPE_SYSTEM);
        })
            ->whereNull('parent_id')
            ->with([
                'children' => function ($q) use ($userId) {
                    $q->where(function ($sub) use ($userId) {
                        $sub->where('user_id', $userId)
                            ->orWhere('scope', Category::SCOPE_SYSTEM);
                    })->orderBy('sort_order');
                }
            ])
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create custom category
     */
    public function createCategory(int $userId, array $data): Category
    {
        $data['user_id'] = $userId;
        $data['scope'] = 'custom';
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        return Category::create($data);
    }

    /**
     * Update custom category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete custom category
     */
    public function deleteCategory(Category $category): void
    {
        $category->delete();
    }
}
