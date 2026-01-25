<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminCategoryService
{
    /**
     * Get system categories
     */
    public function getCategories(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Category::system()
            ->with('parent')
            ->applyFilters($filters)
            ->paginate($perPage);
    }

    /**
     * Create system category
     */
    public function createCategory(array $data): Category
    {
        return Category::create([
            'user_id' => null, // Always null for system
            'scope' => Category::SCOPE_SYSTEM,
            'name' => $data['name'],
            'type' => $data['type'],
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'],
            'type' => $data['type'], // Allow type change? Maybe risky if transactions exist
            'icon' => $data['icon'] ?? $category->icon,
            'color' => $data['color'] ?? $category->color,
            'parent_id' => $data['parent_id'] ?? $category->parent_id,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category;
    }

    /**
     * Delete category
     */
    public function deleteCategory(Category $category): void
    {
        // Should probably check for usage before hard delete, or use SoftDeletes
        // For now, simple delete. DB constraints might prevent if transactions exist (onDelete restrict).
        $category->delete();
    }
}
