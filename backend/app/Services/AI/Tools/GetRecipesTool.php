<?php

namespace App\Services\AI\Tools;

use App\Models\Recipe;

/**
 * Tool to get user's recipes.
 */
class GetRecipesTool extends AITool
{
    public function name(): string
    {
        return 'get_recipes';
    }

    public function description(): string
    {
        return 'Get the list of user recipes with nutritional information. Use for meal planning or viewing cooking instructions.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search recipes by name',
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
        $limit = min((int) ($arguments['limit'] ?? 10), 20);

        $query = Recipe::where('user_id', $userId);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $recipes = $query->limit($limit)->get();

        if ($recipes->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No recipes found. Create some recipes to use for meal planning.',
            ];
        }

        $list = "🍳 Recipes:\n";
        foreach ($recipes as $recipe) {
            $list .= "- {$recipe->name}";
            if ($recipe->calories) {
                $list .= " ({$recipe->calories} kcal/serving)";
            }
            if ($recipe->prep_time || $recipe->cooking_time) {
                $total = ($recipe->prep_time ?? 0) + ($recipe->cooking_time ?? 0);
                $list .= " | ⏱️ {$total}min";
            }
            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
