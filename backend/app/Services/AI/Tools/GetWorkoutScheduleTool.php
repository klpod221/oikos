<?php

namespace App\Services\AI\Tools;

use App\Models\WorkoutSchedule;
use Illuminate\Support\Carbon;

/**
 * Tool to get workout schedule.
 */
class GetWorkoutScheduleTool extends AITool
{
    public function name(): string
    {
        return 'get_workout_schedule';
    }

    public function description(): string
    {
        return 'Get the workout schedule for a date range.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date (YYYY-MM-DD). Default: today',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date (YYYY-MM-DD). Default: 7 days from start',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $startDate = $arguments['start_date'] ?? Carbon::today()->toDateString();
        $endDate = $arguments['end_date'] ?? Carbon::parse($startDate)->addDays(6)->toDateString();

        $schedules = WorkoutSchedule::where('user_id', $userId)
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with('routine')
            ->orderBy('scheduled_date')
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'success' => true,
                'result' => "No workouts scheduled for {$startDate} to {$endDate}.",
            ];
        }

        $result = "📅 Workout Schedule ({$startDate} to {$endDate}):\n\n";

        foreach ($schedules as $schedule) {
            $date = Carbon::parse($schedule->scheduled_date)->format('D d/m');
            $routineName = $schedule->routine->name ?? 'Custom Workout';
            $statusIcon = match ($schedule->status ?? 'scheduled') {
                'completed' => '✅',
                'skipped' => '⏭️',
                default => '📋',
            };
            $result .= "{$statusIcon} {$date}: {$routineName}\n";
        }

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
