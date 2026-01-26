<?php

namespace App\Services\Workout;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutSchedule;
use App\Services\Integration\EnergyBalanceService;
use Carbon\Carbon;

/**
 * Workout Service
 *
 * Core workout business logic including schedule parsing and workout logging.
 */
class WorkoutService
{
    public function __construct(
        protected EnergyBalanceService $energyBalanceService
    ) {}

    /**
     * Calculate calories burned for a routine with actual values.
     *
     * @param Routine $routine Routine model
     * @param array $actualValues Array of [exercise_id => actual_value]
     * @return float Total calories burned
     */
    public function calculateRoutineCalories(Routine $routine, array $actualValues): float
    {
        $totalCalories = 0;

        foreach ($routine->exercises as $exercise) {
            $actualValue = $actualValues[$exercise->id] ?? $exercise->pivot->target_value;
            $totalCalories += $exercise->calculateCalories($actualValue);
        }

        return $totalCalories;
    }

    /**
     * Parse schedule config to determine next workout date.
     *
     * Supports:
     * - Weekly: {"type": "weekly", "days": [1,3,5], "time": "07:00"}
     * - Interval: {"type": "interval", "every_n_days": 3, "time": "18:00"}
     * - Specific: {"type": "specific_dates", "dates": ["2026-02-01", "2026-02-15"]}
     *
     * @param array $config Schedule configuration
     * @param Carbon $fromDate Starting date
     * @return Carbon|null Next workout date
     */
    public function parseScheduleConfig(array $config, Carbon $fromDate): ?Carbon
    {
        $type = $config['type'] ?? 'weekly';

        return match ($type) {
            'weekly' => $this->getNextWeeklyDate($config, $fromDate),
            'interval' => $this->getNextIntervalDate($config, $fromDate),
            'specific_dates' => $this->getNextSpecificDate($config, $fromDate),
            default => null,
        };
    }

    /**
     * Get next workout date for weekly pattern.
     */
    protected function getNextWeeklyDate(array $config, Carbon $fromDate): ?Carbon
    {
        $days = $config['days'] ?? []; // 1=Mon, 7=Sun
        $time = $config['time'] ?? '00:00';

        for ($i = 1; $i <= 7; $i++) {
            $checkDate = $fromDate->copy()->addDays($i);
            if (in_array($checkDate->dayOfWeekIso, $days)) {
                [$hour, $minute] = explode(':', $time);
                return $checkDate->setTime((int)$hour, (int)$minute);
            }
        }

        return null;
    }

    /**
     * Get next workout date for interval pattern.
     */
    protected function getNextIntervalDate(array $config, Carbon $fromDate): ?Carbon
    {
        $everyNDays = $config['every_n_days'] ?? 1;
        $time = $config['time'] ?? '00:00';

        [$hour, $minute] = explode(':', $time);
        return $fromDate->copy()
            ->addDays($everyNDays)
            ->setTime((int)$hour, (int)$minute);
    }

    /**
     * Get next workout date from specific dates.
     */
    protected function getNextSpecificDate(array $config, Carbon $fromDate): ?Carbon
    {
        $dates = $config['dates'] ?? [];

        foreach ($dates as $dateString) {
            $date = Carbon::parse($dateString);
            if ($date->isAfter($fromDate)) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Get upcoming workouts for a user in a date range.
     *
     * @param User $user User model
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Upcoming workouts
     */
    public function getUpcomingWorkouts(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $schedules = WorkoutSchedule::where('user_id', $user->id)
            ->active()
            ->with('routine')
            ->get();

        $upcomingWorkouts = [];

        foreach ($schedules as $schedule) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $nextDate = $this->parseScheduleConfig($schedule->schedule_config, $currentDate);

                if (!$nextDate || $nextDate->gt($endDate)) {
                    break;
                }

                if ($nextDate->gte($startDate)) {
                    $upcomingWorkouts[] = [
                        'schedule_id' => $schedule->id,
                        'routine_id' => $schedule->routine_id,
                        'routine_name' => $schedule->routine->name,
                        'scheduled_date' => $nextDate->toDateTimeString(),
                        'estimated_duration' => $schedule->routine->estimated_duration,
                    ];
                }

                $currentDate = $nextDate;
            }
        }

        // Sort by date
        usort($upcomingWorkouts, fn($a, $b) => strcmp($a['scheduled_date'], $b['scheduled_date']));

        return $upcomingWorkouts;
    }

    /**
     * Log workout completion and update daily summary.
     *
     * @param User $user User model
     * @param array $data Workout data
     * @return WorkoutLog Created log
     */
    public function logWorkoutCompletion(User $user, array $data): WorkoutLog
    {
        $routine = isset($data['routine_id']) ? Routine::find($data['routine_id']) : null;

        // Calculate calories from exercises
        $caloriesBurned = 0;
        if ($routine && isset($data['exercises_completed'])) {
            $actualValues = collect($data['exercises_completed'])
                ->pluck('actual_value', 'exercise_id')
                ->toArray();

            $caloriesBurned = $this->calculateRoutineCalories($routine, $actualValues);
        }

        // Create workout log
        $log = WorkoutLog::create([
            'user_id' => $user->id,
            'routine_id' => $data['routine_id'] ?? null,
            'started_at' => $data['started_at'],
            'completed_at' => $data['completed_at'] ?? now(),
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'calories_burned' => $data['calories_burned'] ?? $caloriesBurned,
            'exercises_completed' => $data['exercises_completed'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Update daily summary
        $date = Carbon::parse($log->started_at);
        $this->energyBalanceService->updateDailySummary($user->id, $date);

        return $log;
    }
}
