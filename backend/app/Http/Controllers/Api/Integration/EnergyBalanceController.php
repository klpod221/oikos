<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Models\UserGoals;
use App\Models\UserStats;
use App\Services\Integration\EnergyBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Energy Balance Controller
 *
 * Handles BMR/TDEE calculations and energy balance dashboard.
 */
class EnergyBalanceController extends Controller
{
    public function __construct(
        protected EnergyBalanceService $energyBalanceService
    ) {}

    /**
     * Get energy balance for a specific date
     */
    public function getEnergyBalance(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        // Update summary first
        $this->energyBalanceService->updateDailySummary(
            Auth::id(),
            \Carbon\Carbon::parse($validated['date'])
        );

        $summary = $this->energyBalanceService->getEnergyBalance(
            Auth::id(),
            \Carbon\Carbon::parse($validated['date'])
        );

        return response()->json($summary);
    }

    /**
     * Get energy balance trend
     */
    public function getTrend(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $trend = $this->energyBalanceService->getEnergyBalanceTrend(
            Auth::id(),
            \Carbon\Carbon::parse($validated['start_date']),
            \Carbon\Carbon::parse($validated['end_date'])
        );

        return response()->json($trend);
    }

    /**
     * Get goal warnings for a date
     */
    public function getGoalWarnings(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $warnings = $this->energyBalanceService->checkGoalWarnings(
            Auth::id(),
            \Carbon\Carbon::parse($validated['date'])
        );

        return response()->json($warnings);
    }

    /**
     * Update user stats
     */
    public function updateUserStats(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'age' => 'required|integer|min:1|max:150',
            'gender' => 'required|in:male,female,other',
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active,extra_active',
            'recorded_at' => 'nullable|date',
        ]);

        $stats = UserStats::create([
            ...$validated,
            'user_id' => Auth::id(),
            'recorded_at' => $validated['recorded_at'] ?? now()->toDateString(),
        ]);

        return response()->json($stats, 201);
    }

    /**
     * Get latest user stats
     */
    public function getUserStats()
    {
        $stats = UserStats::where('user_id', Auth::id())
            ->orderBy('recorded_at', 'desc')
            ->first();

        return response()->json($stats);
    }

    /**
     * Update user goals
     */
    public function updateUserGoals(Request $request)
    {
        $validated = $request->validate([
            'goal_type' => 'required|in:maintain,lose_weight,gain_muscle,improve_fitness',
            'target_weight' => 'nullable|numeric|min:1',
            'target_calories' => 'nullable|integer|min:0',
            'target_protein' => 'nullable|numeric|min:0',
            'target_carbs' => 'nullable|numeric|min:0',
            'target_fat' => 'nullable|numeric|min:0',
            'weekly_workout_target' => 'nullable|integer|min:0',
        ]);

        $goals = UserGoals::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json($goals);
    }

    /**
     * Get user goals
     */
    public function getUserGoals()
    {
        $goals = UserGoals::where('user_id', Auth::id())->first();

        return response()->json($goals);
    }
}
