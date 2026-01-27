<?php

namespace App\Services\AI\Tools;

use App\Models\Ingredient;

/**
 * Tool to search ingredients.
 */
class GetIngredientsTool extends AITool
{
    public function name(): string
    {
        return 'get_ingredients';
    }

    public function description(): string
    {
        return 'Search for ingredients with nutritional information. Useful for meal planning and calorie tracking.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search term to find ingredients',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum results to return (default: 10)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $search = $arguments['search'] ?? '';
        $limit = min((int) ($arguments['limit'] ?? 10), 30);

        $query = Ingredient::availableFor($userId);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $ingredients = $query->limit($limit)->get();

        if ($ingredients->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No ingredients found. Try a different search term.',
            ];
        }

        $list = "🥗 Ingredients:\n";
        foreach ($ingredients as $ing) {
            $list .= "- {$ing->name} ({$ing->unit})";
            if ($ing->calories) {
                $list .= " | {$ing->calories} kcal";
            }
            if ($ing->protein) {
                $list .= ", P:{$ing->protein}g";
            }
            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
