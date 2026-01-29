<?php

namespace App\Services\AI\Tools;

use App\Models\WorkoutLog;
use Illuminate\Support\Carbon;

/**
 * Tool to get workout summary.
 */
class GetWorkoutSummaryTool extends AITool
{
    public function name(): string
    {
        return 'get_workout_summary';
    }

    public function description(): string
    {
        return 'Get a workout summary for a period showing total sessions, duration, and calories burned.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'enum' => ['this_week', 'last_week', 'this_month', 'custom'],
                    'description' => 'Time period (default: this_week)',
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date for custom period',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date for custom period',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $period = $arguments['period'] ?? 'this_week';

        [$startDate, $endDate] = match ($period) {
            'this_week' => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek()->toDateString(), Carbon::now()->subWeek()->endOfWeek()->toDateString()],
            'this_month' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            'custom' => [$arguments['start_date'] ?? Carbon::now()->startOfWeek()->toDateString(), $arguments['end_date'] ?? Carbon::now()->toDateString()],
            default => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
        };

        $logs = WorkoutLog::where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->get();

        if ($logs->isEmpty()) {
            return [
                'success' => true,
                'result' => "No workouts logged for {$startDate} to {$endDate}.",
            ];
        }

        $totalSessions = $logs->count();
        $totalDuration = $logs->sum('duration_seconds') / 60;
        $totalCalories = $logs->sum('calories_burned');
        $avgDuration = $totalDuration / $totalSessions;

        $result = "🏋️ Workout Summary ({$startDate} to {$endDate}):\n";
        $result .= "- 📊 Sessions: {$totalSessions}\n";
        $result .= "- ⏱️ Total Duration: " . number_format($totalDuration, 0) . " min\n";
        $result .= "- 📈 Avg per Session: " . number_format($avgDuration, 0) . " min\n";
        $result .= "- 🔥 Calories Burned: " . number_format($totalCalories, 0) . " kcal";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
