<?php

namespace App\Services\AI\Tools;

use App\Models\Exercise;

/**
 * Tool to search exercises.
 */
class GetExercisesTool extends AITool
{
    public function name(): string
    {
        return 'get_exercises';
    }

    public function description(): string
    {
        return 'Search for exercises with descriptions and calorie burn info.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search exercises by name',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['reps', 'time'],
                    'description' => 'Filter by exercise type (reps-based or time-based)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum results (default: 10)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $search = $arguments['search'] ?? '';
        $type = $arguments['type'] ?? null;
        $limit = min((int) ($arguments['limit'] ?? 10), 30);

        $query = Exercise::availableFor($userId);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type) {
            $query->where('type', $type);
        }

        $exercises = $query->limit($limit)->get();

        if ($exercises->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No exercises found. Try a different search.',
            ];
        }

        $list = "🏋️ Exercises:\n";
        foreach ($exercises as $ex) {
            $typeLabel = $ex->type === 'reps' ? 'reps' : 'min';
            $list .= "- {$ex->name} ({$typeLabel})";
            if ($ex->calories_per_unit) {
                $list .= " | ~{$ex->calories_per_unit} kcal/{$typeLabel}";
            }
            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
