<?php

namespace App\Services\Integration;

use App\Models\DailySummary;
use App\Models\NutritionLog;
use App\Models\UserGoals;
use App\Models\UserStats;
use App\Models\WorkoutLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Energy Balance Service
 *
 * Calculates BMR, TDEE, and energy balance for users.
 * Implements the Mifflin-St Jeor equation for BMR calculation.
 */
class EnergyBalanceService
{
    /**
     * Calculate Basal Metabolic Rate (BMR) using Mifflin-St Jeor equation.
     *
     * Formula:
     * Men: BMR = (10 × weight_kg) + (6.25 × height_cm) - (5 × age) + 5
     * Women: BMR = (10 × weight_kg) + (6.25 × height_cm) - (5 × age) - 161
     *
     * @param UserStats $stats User physical statistics
     * @return float BMR in calories
     */
    public function calculateBMR(UserStats $stats): float
    {
        $base = (10 * $stats->weight) + (6.25 * $stats->height) - (5 * $stats->age);

        return match ($stats->gender) {
            'male' => $base + 5,
            'female' => $base - 161,
            'other' => $base - 78, // Average between male and female
            default => $base,
        };
    }

    /**
     * Calculate Total Daily Energy Expenditure (TDEE).
     *
     * TDEE = BMR × Activity Multiplier + Workout Calories
     *
     * Activity Multipliers:
     * - sedentary: 1.2
     * - lightly_active: 1.375
     * - moderately_active: 1.55
     * - very_active: 1.725
     * - extra_active: 1.9
     *
     * @param UserStats $stats User stats with activity level
     * @param float $workoutCalories Additional calories from workouts
     * @return float TDEE in calories
     */
    public function calculateTDEE(UserStats $stats, float $workoutCalories = 0): float
    {
        $bmr = $this->calculateBMR($stats);
        $multiplier = $stats->getActivityMultiplier();

        $activityCalories = $bmr * ($multiplier - 1);

        return $bmr + $activityCalories + $workoutCalories;
    }

    /**
     * Update or create daily summary for a user.
     *
     * Recalculates all metrics from scratch based on logs.
     *
     * @param int $userId User ID
     * @param Carbon $date Date to calculate
     * @return DailySummary Updated summary
     */
    public function updateDailySummary(int $userId, Carbon $date): DailySummary
    {
        // Get latest user stats
        $stats = UserStats::where('user_id', $userId)
            ->where('recorded_at', '<=', $date)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (!$stats) {
            throw new RuntimeException('User stats not found. Please create user stats first.');
        }

        // Calculate BMR
        $bmr = $this->calculateBMR($stats);

        // Get workout calories for the day
        $workoutCalories = WorkoutLog::where('user_id', $userId)
            ->whereDate('started_at', $date)
            ->sum('calories_burned');

        // Calculate TDEE
        $multiplier = $stats->getActivityMultiplier();
        $activityCalories = $bmr * ($multiplier - 1);
        $tdee = $bmr + $activityCalories + $workoutCalories;

        // Get nutrition intake for the day
        $nutrition = NutritionLog::where('user_id', $userId)
            ->where('date', $date)
            ->select([
                DB::raw('SUM(calories) as total_calories'),
                DB::raw('SUM(protein) as total_protein'),
                DB::raw('SUM(carbs) as total_carbs'),
                DB::raw('SUM(fat) as total_fat'),
            ])
            ->first();

        $totalCalories = $nutrition->total_calories ?? 0;
        $totalProtein = $nutrition->total_protein ?? 0;
        $totalCarbs = $nutrition->total_carbs ?? 0;
        $totalFat = $nutrition->total_fat ?? 0;

        // Calculate energy balance
        $energyBalance = $totalCalories - $tdee;

        // Update or create summary
        return DailySummary::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            [
                'bmr' => $bmr,
                'activity_calories' => $activityCalories,
                'workout_calories' => $workoutCalories,
                'tdee' => $tdee,
                'total_calories' => $totalCalories,
                'total_protein' => $totalProtein,
                'total_carbs' => $totalCarbs,
                'total_fat' => $totalFat,
                'energy_balance' => $energyBalance,
            ]
        );
    }

    /**
     * Get energy balance for a specific date.
     *
     * @param int $userId User ID
     * @param Carbon $date Date
     * @return DailySummary|null Daily summary
     */
    public function getEnergyBalance(int $userId, Carbon $date): ?DailySummary
    {
        return DailySummary::where('user_id', $userId)
            ->where('date', $date)
            ->first();
    }

    /**
     * Check goal warnings for a user on a specific date.
     *
     * Returns array of warnings if goals are not met.
     *
     * @param int $userId User ID
     * @param Carbon $date Date to check
     * @return array Warnings array
     */
    public function checkGoalWarnings(int $userId, Carbon $date): array
    {
        $goals = UserGoals::where('user_id', $userId)->first();
        if (!$goals) {
            return [];
        }

        $summary = $this->getEnergyBalance($userId, $date);
        if (!$summary) {
            return [];
        }

        $warnings = [];

        $this->checkCalorieWarning($goals, $summary, $warnings);
        $this->checkProteinWarning($goals, $summary, $warnings);
        $this->checkEnergyBalanceWarning($goals, $summary, $warnings);

        return $warnings;
    }

    /**
     * Check calorie target warning.
     */
    private function checkCalorieWarning(UserGoals $goals, DailySummary $summary, array &$warnings): void
    {
        if (!$goals->target_calories) {
            return;
        }

        $calorieDiff = abs($summary->total_calories - $goals->target_calories);
        $caloriePercent = ($calorieDiff / $goals->target_calories) * 100;

        if ($caloriePercent <= 10) {
            return;
        }

        $status = $summary->total_calories < $goals->target_calories ? 'below' : 'above';
        $warnings[] = [
            'type' => 'calories',
            'message' => "Daily calories {$status} target by {$calorieDiff} kcal ({$caloriePercent}%)",
            'severity' => $caloriePercent > 20 ? 'high' : 'medium',
        ];
    }

    /**
     * Check protein target warning.
     */
    private function checkProteinWarning(UserGoals $goals, DailySummary $summary, array &$warnings): void
    {
        if (!$goals->target_protein || $goals->isMacroTargetMet('protein', $summary->total_protein)) {
            return;
        }

        $proteinDiff = $goals->target_protein - $summary->total_protein;
        $warnings[] = [
            'type' => 'protein',
            'message' => "Protein intake {$proteinDiff}g below target",
            'severity' => $goals->goal_type === 'gain_muscle' ? 'high' : 'medium',
        ];
    }

    /**
     * Check energy balance vs goal warning.
     */
    private function checkEnergyBalanceWarning(UserGoals $goals, DailySummary $summary, array &$warnings): void
    {
        if ($goals->goal_type === 'lose_weight' && $summary->energy_balance > 0) {
            $warnings[] = [
                'type' => 'energy_balance',
                'message' => 'Caloric surplus detected. Should maintain deficit for weight loss.',
                'severity' => 'medium',
            ];
        }

        if ($goals->goal_type === 'gain_muscle' && $summary->energy_balance < -200) {
            $warnings[] = [
                'type' => 'energy_balance',
                'message' => 'Large caloric deficit may hinder muscle gain.',
                'severity' => 'medium',
            ];
        }
    }

    /**
     * Get energy balance trend over a date range.
     *
     * @param int $userId User ID
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEnergyBalanceTrend(int $userId, Carbon $startDate, Carbon $endDate)
    {
        return DailySummary::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }
}
