<?php

namespace App\Http\Controllers\Api\Nutrition;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\MealPlanRequest;
use App\Http\Resources\MealPlanResource;
use App\Models\MealPlan;
use App\Services\Nutrition\MealPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MealPlanController extends Controller
{
    public function __construct(
        protected MealPlanService $mealPlanService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $plans = $this->mealPlanService->getMealPlans(
            $request->user()->id,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => MealPlanResource::collection($plans),
        ]);
    }

    public function store(MealPlanRequest $request): JsonResponse
    {
        $plan = $this->mealPlanService->createPlan(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Meal plan created successfully',
            'data' => new MealPlanResource($plan),
        ], 201);
    }

    public function show(MealPlan $mealPlan): JsonResponse
    {
        Gate::authorize('view', $mealPlan);

        return response()->json([
            'success' => true,
            'data' => new MealPlanResource($mealPlan->load('recipe')),
        ]);
    }

    public function update(MealPlanRequest $request, MealPlan $mealPlan): JsonResponse
    {
        Gate::authorize('update', $mealPlan);

        $mealPlan = $this->mealPlanService->updatePlan($mealPlan, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Meal plan updated successfully',
            'data' => new MealPlanResource($mealPlan),
        ]);
    }

    public function destroy(MealPlan $mealPlan): JsonResponse
    {
        Gate::authorize('delete', $mealPlan);

        $this->mealPlanService->deletePlan($mealPlan);

        return response()->json([
            'success' => true,
            'message' => 'Meal plan deleted successfully',
        ]);
    }
}
