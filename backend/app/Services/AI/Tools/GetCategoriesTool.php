<?php

namespace App\Services\AI\Tools;

use App\Models\Category;

class GetCategoriesTool extends AITool
{
    public function name(): string
    {
        return 'get_categories';
    }

    public function description(): string
    {
        return 'Get the list of available transaction categories for the user. Use this tool when you need to know the valid categories to create a transaction.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['income', 'expense'],
                    'description' => 'Filter categories by type (optional)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $type = $arguments['type'] ?? null;

        $query = Category::availableFor($userId)->active();

        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->select(['id', 'name', 'type'])->get();

        if ($categories->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No categories found. Users can create custom categories in Settings.',
            ];
        }

        $list = "Available Categories:\n";
        foreach ($categories as $cat) {
            $typeLabel = $cat->type === 'income' ? 'Income' : 'Expense';
            $list .= "- {$cat->name} ({$typeLabel})\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
