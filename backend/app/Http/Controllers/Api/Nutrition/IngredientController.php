<?php

namespace App\Http\Controllers\Api\Nutrition;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\IngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Services\Nutrition\IngredientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IngredientController extends Controller
{
    public function __construct(
        protected IngredientService $ingredientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $ingredients = $this->ingredientService->getIngredients(
            $request->user()->id,
            $request->only(['search', 'sort_by', 'sort_order']),
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => IngredientResource::collection($ingredients),
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page' => $ingredients->lastPage(),
                'per_page' => $ingredients->perPage(),
                'total' => $ingredients->total(),
            ],
        ]);
    }

    public function store(IngredientRequest $request): JsonResponse
    {
        $ingredient = $this->ingredientService->createIngredient(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Ingredient created successfully',
            'data' => new IngredientResource($ingredient),
        ], 201);
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        Gate::authorize('view', $ingredient);

        return response()->json([
            'success' => true,
            'data' => new IngredientResource($ingredient),
        ]);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient): JsonResponse
    {
        Gate::authorize('update', $ingredient);

        $ingredient = $this->ingredientService->updateIngredient($ingredient, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ingredient updated successfully',
            'data' => new IngredientResource($ingredient),
        ]);
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        Gate::authorize('delete', $ingredient);

        $this->ingredientService->deleteIngredient($ingredient);

        return response()->json([
            'success' => true,
            'message' => 'Ingredient deleted successfully',
        ]);
    }
}
