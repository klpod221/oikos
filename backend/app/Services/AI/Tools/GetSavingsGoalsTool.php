<?php

namespace App\Services\AI\Tools;

use App\Models\SavingsGoal;

/**
 * Tool to get user's savings goals.
 */
class GetSavingsGoalsTool extends AITool
{
    public function name(): string
    {
        return 'get_savings_goals';
    }

    public function description(): string
    {
        return 'Get the list of user savings goals with their current progress.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['in_progress', 'completed', 'cancelled', 'all'],
                    'description' => 'Filter by status (default: in_progress)',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $status = $arguments['status'] ?? 'in_progress';

        $query = SavingsGoal::where('user_id', $userId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $goals = $query->get();

        if ($goals->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No savings goals found. Would you like to create one?',
            ];
        }

        $list = "💰 Savings Goals:\n";
        foreach ($goals as $goal) {
            $current = number_format($goal->current_amount, 0, ',', '.');
            $target = number_format($goal->target_amount, 0, ',', '.');
            $progress = $goal->getProgressPercentage();
            $statusIcon = match ($goal->status) {
                'completed' => '✅',
                'cancelled' => '❌',
                default => '🎯',
            };

            $list .= "{$statusIcon} {$goal->name}: {$current}/{$target}đ ({$progress}%)";

            if ($goal->deadline) {
                $list .= " - Due: {$goal->deadline->format('d/m/Y')}";
            }

            $list .= "\n";
        }

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
