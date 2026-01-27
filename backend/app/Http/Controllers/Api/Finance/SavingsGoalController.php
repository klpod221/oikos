<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\SavingsGoalRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Services\Finance\SavingsGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Savings Goal Controller
 *
 * Handles management of savings goals.
 *
 * @package App\Http\Controllers\Api\Finance
 */
class SavingsGoalController extends Controller
{
    public function __construct(
        protected SavingsGoalService $goalService
    ) {
    }

    /**
     * List Savings Goals
     *
     * Get a list of all savings goals.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $goals = $this->goalService->getGoals($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => SavingsGoalResource::collection($goals),
        ]);
    }

    /**
     * Create Savings Goal
     *
     * Create a new savings goal.
     */
    public function store(SavingsGoalRequest $request): JsonResponse
    {
        $goal = $this->goalService->createGoal(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Savings Goal created successfully',
            'data' => new SavingsGoalResource($goal),
        ], 201);
    }

    /**
     * Get Savings Goal
     *
     * Get details of a specific savings goal.
     */
    public function show(SavingsGoal $savingsGoal): JsonResponse
    {
        Gate::authorize('view', $savingsGoal);

        return response()->json([
            'success' => true,
            'data' => new SavingsGoalResource($savingsGoal),
        ]);
    }

    /**
     * Update Savings Goal
     *
     * Update details of a savings goal.
     */
    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal): JsonResponse
    {
        Gate::authorize('update', $savingsGoal);

        $savingsGoal = $this->goalService->updateGoal($savingsGoal, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Savings Goal updated successfully',
            'data' => new SavingsGoalResource($savingsGoal),
        ]);
    }

    /**
     * Delete Savings Goal
     *
     * Delete a savings goal.
     */
    public function destroy(SavingsGoal $savingsGoal): JsonResponse
    {
        Gate::authorize('delete', $savingsGoal);

        $this->goalService->deleteGoal($savingsGoal);

        return response()->json([
            'success' => true,
            'message' => 'Savings Goal deleted successfully',
        ]);
    }
}
