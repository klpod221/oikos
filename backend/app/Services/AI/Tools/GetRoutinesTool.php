<?php

namespace App\Services\AI\Tools;

use App\Models\Routine;

/**
 * Tool to get workout routines.
 */
class GetRoutinesTool extends AITool
{
    public function name(): string
    {
        return 'get_routines';
    }

    public function description(): string
    {
        return 'Get user workout routines with their exercises.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search routines by name',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $search = $arguments['search'] ?? '';

        $query = Routine::where('user_id', $userId)->with('exercises');

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $routines = $query->get();

        if ($routines->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No workout routines found. Create some routines in the app.',
            ];
        }

        $list = "💪 Workout Routines:\n\n";
        foreach ($routines as $routine) {
            $duration = $routine->estimated_duration ? "{$routine->estimated_duration}min" : 'N/A';
            $calories = $routine->calculateEstimatedCalories();
            $list .= "**{$routine->name}** (⏱️ {$duration}, 🔥 ~{$calories} kcal)\n";

            if ($routine->exercises->isNotEmpty()) {
                foreach ($routine->exercises->take(5) as $ex) {
                    $target = $ex->pivot->target_value ?? '';
                    $unit = $ex->type === 'reps' ? 'reps' : 'sec';
                    $list .= "  - {$ex->name}: {$target} {$unit}\n";
                }
                if ($routine->exercises->count() > 5) {
                    $list .= "  ... and more\n";
                }
            }
            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
