<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Finance\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * List Categories
     *
     * Get a list of categories (system default + user custom).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Create Category
     *
     * Create a new custom category for the user.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Get Category
     *
     * Get details of a specific category.
     */
    public function show(Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Update Category
     *
     * Update a user's custom category. System categories cannot be updated.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);

        $category = $this->categoryService->updateCategory($category, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Delete Category
     *
     * Delete a user's custom category. System categories cannot be deleted.
     */
    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $this->categoryService->deleteCategory($category);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
