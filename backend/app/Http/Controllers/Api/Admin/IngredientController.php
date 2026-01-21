<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Services\Admin\AdminIngredientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function __construct(
        protected AdminIngredientService $ingredientService
    ) {}

    /**
     * List global ingredients
     */
    public function index(Request $request): JsonResponse
    {
        $ingredients = $this->ingredientService->getIngredients(
            $request->only(['search']),
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => IngredientResource::collection($ingredients),
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page' => $ingredients->lastPage(),
                'total' => $ingredients->total(),
            ],
        ]);
    }

    /**
     * Create global ingredient
     */
    public function store(IngredientRequest $request): JsonResponse
    {
        $ingredient = $this->ingredientService->createIngredient($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ingredient created successfully',
            'data' => new IngredientResource($ingredient),
        ], 201);
    }

    /**
     * Show ingredient details
     */
    public function show(int $id): JsonResponse
    {
        $ingredient = Ingredient::global()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new IngredientResource($ingredient),
        ]);
    }

    /**
     * Update ingredient
     */
    public function update(IngredientRequest $request, int $id): JsonResponse
    {
        $ingredient = Ingredient::global()->findOrFail($id);
        $ingredient = $this->ingredientService->updateIngredient($ingredient, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ingredient updated successfully',
            'data' => new IngredientResource($ingredient),
        ]);
    }

    /**
     * Delete ingredient
     */
    public function destroy(int $id): JsonResponse
    {
        $ingredient = Ingredient::global()->findOrFail($id);

        try {
            $this->ingredientService->deleteIngredient($ingredient);

            return response()->json([
                'success' => true,
                'message' => 'Ingredient deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete ingredient. It may be in use.',
            ], 400);
        }
    }
}
