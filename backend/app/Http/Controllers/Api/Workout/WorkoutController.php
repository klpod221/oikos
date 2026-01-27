<?php

namespace App\Http\Controllers\Api\Workout;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Routine;
use App\Models\WorkoutLog;
use App\Models\WorkoutSchedule;
use App\Services\Workout\WorkoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Workout Controller
 *
 * Handles workout-related API endpoints: exercises, routines, schedules, and logs.
 */
class WorkoutController extends Controller
{
    public function __construct(
        protected WorkoutService $workoutService
    ) {}

    /**
     * Get available exercises (global + user's custom)
     */
    public function getExercises(Request $request)
    {
        $exercises = Exercise::availableFor(Auth::id())
            ->orderBy('name')
            ->paginate(20);

        return response()->json($exercises);
    }

    /**
     * Create custom exercise
     */
    public function storeExercise(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'type' => 'required|in:reps,time',
            'calories_per_unit' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
        ]);

        $exercise = Exercise::create([
            ...$validated,
            'user_id' => Auth::id(),
            'is_global' => false,
        ]);

        return response()->json($exercise, 201);
    }

    /**
     * Get user's routines
     */
    public function getRoutines(Request $request)
    {
        $routines = Routine::where('user_id', Auth::id())
            ->with('exercises')
            ->orderBy('name')
            ->get();

        return response()->json($routines);
    }

    /**
     * Get single routine with exercises
     */
    public function getRoutine($id)
    {
        $routine = Routine::where('user_id', Auth::id())
            ->with('exercises')
            ->findOrFail($id);

        return response()->json($routine);
    }

    /**
     * Create workout routine
     */
    public function storeRoutine(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:1',
            'exercises' => 'required|array|min:1',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.order' => 'required|integer|min:0',
            'exercises.*.target_value' => 'required|integer|min:1',
            'exercises.*.rest_time' => 'nullable|integer|min:0',
        ]);

        $routine = Routine::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'estimated_duration' => $validated['estimated_duration'],
        ]);

        foreach ($validated['exercises'] as $exercise) {
            $routine->exercises()->attach($exercise['exercise_id'], [
                'order' => $exercise['order'],
                'target_value' => $exercise['target_value'],
                'rest_time' => $exercise['rest_time'] ?? 30,
            ]);
        }

        return response()->json($routine->load('exercises'), 201);
    }

    /**
     * Get workout schedules
     */
    public function getSchedules(Request $request)
    {
        $schedules = WorkoutSchedule::where('user_id', Auth::id())
            ->with('routine')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Create workout schedule
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'routine_id' => 'required|exists:routines,id',
            'name' => 'nullable|string|max:255',
            'schedule_config' => 'required|array',
            'schedule_config.type' => 'required|in:weekly,interval,specific_dates',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $schedule = WorkoutSchedule::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($schedule->load('routine'), 201);
    }

    /**
     * Get upcoming workouts
     */
    public function getUpcomingWorkouts(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $upcomingWorkouts = $this->workoutService->getUpcomingWorkouts(
            Auth::user(),
            \Carbon\Carbon::parse($validated['start_date']),
            \Carbon\Carbon::parse($validated['end_date'])
        );

        return response()->json($upcomingWorkouts);
    }

    /**
     * Log workout completion
     */
    public function logWorkout(Request $request)
    {
        $validated = $request->validate([
            'routine_id' => 'nullable|exists:routines,id',
            'started_at' => 'required|date',
            'completed_at' => 'nullable|date|after:started_at',
            'duration_seconds' => 'nullable|integer|min:1',
            'calories_burned' => 'nullable|numeric|min:0',
            'exercises_completed' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $log = $this->workoutService->logWorkoutCompletion(Auth::user(), $validated);

        return response()->json($log, 201);
    }

    /**
     * Get workout logs (history)
     */
    public function getWorkoutLogs(Request $request)
    {
        $query = WorkoutLog::where('user_id', Auth::id())
            ->with('routine');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }

        $logs = $query->orderBy('started_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Update workout routine
     */
    public function updateRoutine(Request $request, $id)
    {
        $routine = Routine::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:1',
            'exercises' => 'sometimes|array|min:1',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.order' => 'required|integer|min:0',
            'exercises.*.target_value' => 'required|integer|min:1',
            'exercises.*.rest_time' => 'nullable|integer|min:0',
        ]);

        $routine->update([
            'name' => $validated['name'] ?? $routine->name,
            'description' => $validated['description'] ?? $routine->description,
            'estimated_duration' => $validated['estimated_duration'] ?? $routine->estimated_duration,
        ]);

        if (isset($validated['exercises'])) {
            $routine->exercises()->detach();
            foreach ($validated['exercises'] as $exercise) {
                $routine->exercises()->attach($exercise['exercise_id'], [
                    'order' => $exercise['order'],
                    'target_value' => $exercise['target_value'],
                    'rest_time' => $exercise['rest_time'] ?? 30,
                ]);
            }
        }

        return response()->json($routine->load('exercises'));
    }

    /**
     * Delete workout routine
     */
    public function deleteRoutine($id)
    {
        $routine = Routine::where('user_id', Auth::id())->findOrFail($id);
        $routine->exercises()->detach();
        $routine->delete();

        return response()->json(['message' => 'Routine deleted']);
    }

    /**
     * Delete workout schedule
     */
    public function deleteSchedule($id)
    {
        $schedule = WorkoutSchedule::where('user_id', Auth::id())->findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted']);
    }
}
