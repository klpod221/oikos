<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Admin\AdminCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected AdminCategoryService $categoryService
    ) {}

    /**
     * List system categories
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories(
            $request->only(['search', 'type']),
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Create system category
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Show category details
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::system()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Update category
     */
    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::system()->findOrFail($id);
        $category = $this->categoryService->updateCategory($category, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::system()->findOrFail($id);

        try {
            $this->categoryService->deleteCategory($category);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category. It may be in use.',
            ], 400);
        }
    }
}
