<?php

namespace App\Http\Controllers\Api\Nutrition;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Services\Nutrition\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Recipe Controller
 *
 * Handles management of recipes.
 *
 * @package App\Http\Controllers\Api\Nutrition
 */
class RecipeController extends Controller
{
    public function __construct(
        protected RecipeService $recipeService
    ) {
    }

    /**
     * List Recipes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $recipes = $this->recipeService->getRecipes(
            $request->user()->id,
            $request->only(['search', 'sort_by', 'sort_order']),
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => RecipeResource::collection($recipes),
            'meta' => [
                'current_page' => $recipes->currentPage(),
                'last_page' => $recipes->lastPage(),
                'per_page' => $recipes->perPage(),
                'total' => $recipes->total(),
            ],
        ]);
    }

    public function store(RecipeRequest $request): JsonResponse
    {
        $recipe = $this->recipeService->createRecipe(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Recipe created successfully',
            'data' => new RecipeResource($recipe),
        ], 201);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        Gate::authorize('view', $recipe);

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($recipe->load('ingredients')),
        ]);
    }

    public function update(RecipeRequest $request, Recipe $recipe): JsonResponse
    {
        Gate::authorize('update', $recipe);

        $recipe = $this->recipeService->updateRecipe($recipe, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Recipe updated successfully',
            'data' => new RecipeResource($recipe),
        ]);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        Gate::authorize('delete', $recipe);

        $this->recipeService->deleteRecipe($recipe);

        return response()->json([
            'success' => true,
            'message' => 'Recipe deleted successfully',
        ]);
    }
}
