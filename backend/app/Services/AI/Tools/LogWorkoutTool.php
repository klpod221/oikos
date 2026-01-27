<?php

namespace App\Services\AI\Tools;

use App\Models\WorkoutLog;
use App\Models\Routine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Tool to log a completed workout.
 */
class LogWorkoutTool extends AITool
{
    public function name(): string
    {
        return 'log_workout';
    }

    public function description(): string
    {
        return 'Log a completed workout session. Records duration and calories burned.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'routine_name' => [
                    'type' => 'string',
                    'description' => 'Name of the routine performed (optional)',
                ],
                'duration_minutes' => [
                    'type' => 'integer',
                    'description' => 'Duration of workout in minutes',
                ],
                'calories_burned' => [
                    'type' => 'number',
                    'description' => 'Estimated calories burned (optional, will calculate if routine provided)',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes about the workout (optional)',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Workout date (YYYY-MM-DD). Default: today',
                ],
            ],
            'required' => ['duration_minutes'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $durationMinutes = (int) ($arguments['duration_minutes'] ?? 0);
            $routineName = $arguments['routine_name'] ?? null;
            $caloriesBurned = (float) ($arguments['calories_burned'] ?? 0);
            $notes = $arguments['notes'] ?? null;
            $date = $arguments['date'] ?? Carbon::today()->toDateString();

            if ($durationMinutes <= 0) {
                return ['success' => false, 'result' => 'Duration must be greater than 0.'];
            }

            $routineId = null;

            if ($routineName) {
                $routine = Routine::where('user_id', $userId)
                    ->where('name', 'like', "%{$routineName}%")
                    ->first();

                if ($routine) {
                    $routineId = $routine->id;
                    if ($caloriesBurned <= 0) {
                        $caloriesBurned = $routine->calculateEstimatedCalories();
                    }
                }
            }

            $log = WorkoutLog::create([
                'user_id' => $userId,
                'routine_id' => $routineId,
                'started_at' => Carbon::parse($date)->setTime(10, 0),
                'completed_at' => Carbon::parse($date)->setTime(10, 0)->addMinutes($durationMinutes),
                'duration_seconds' => $durationMinutes * 60,
                'calories_burned' => $caloriesBurned,
                'notes' => $notes,
            ]);

            $routineLabel = $routineName ? " ({$routineName})" : '';
            $caloriesLabel = $caloriesBurned > 0 ? ", 🔥 {$caloriesBurned} kcal" : '';

            return [
                'success' => true,
                'result' => "Logged workout{$routineLabel}: ⏱️ {$durationMinutes} min{$caloriesLabel}",
                'data' => $log,
            ];
        } catch (\Exception $e) {
            Log::error('LogWorkoutTool failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'result' => 'Failed to log workout: ' . $e->getMessage()];
        }
    }
}
