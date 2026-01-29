<?php

namespace App\Services\AI\Tools;

use App\Models\ShoppingList;

/**
 * Tool to get shopping lists.
 */
class GetShoppingListTool extends AITool
{
    public function name(): string
    {
        return 'get_shopping_list';
    }

    public function description(): string
    {
        return 'Get active shopping lists with their items.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'finalized', 'purchased', 'all'],
                    'description' => 'Filter by status (default: all)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $status = $arguments['status'] ?? 'all';

        $query = ShoppingList::where('user_id', $userId)->with('items.ingredient');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $lists = $query->orderBy('created_at', 'desc')->limit(5)->get();

        if ($lists->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No shopping lists found.',
            ];
        }

        $result = "🛒 Shopping Lists:\n\n";

        foreach ($lists as $list) {
            $statusIcon = match ($list->status) {
                'purchased' => '✅',
                'finalized' => '📋',
                default => '📝',
            };

            $result .= "{$statusIcon} **{$list->name}** ({$list->start_date->format('d/m')} - {$list->end_date->format('d/m')})\n";

            if ($list->items->isNotEmpty()) {
                foreach ($list->items->take(5) as $item) {
                    $ingName = $item->ingredient->name ?? 'Unknown';
                    $result .= "  - {$ingName}: {$item->quantity} {$item->unit}\n";
                }
                if ($list->items->count() > 5) {
                    $more = $list->items->count() - 5;
                    $result .= "  ... and {$more} more items\n";
                }
            }
            $result .= "\n";
        }

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
