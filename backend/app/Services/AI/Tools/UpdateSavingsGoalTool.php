<?php

namespace App\Services\AI\Tools;

use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Log;

/**
 * Tool to update a savings goal progress.
 */
class UpdateSavingsGoalTool extends AITool
{
    public function name(): string
    {
        return 'update_savings_goal';
    }

    public function description(): string
    {
        return 'Update a savings goal - add/subtract amount or change status. Use get_savings_goals first to find the goal.';
    }

    public function dependsOn(): array
    {
        return ['get_savings_goals'];
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'goal_name' => [
                    'type' => 'string',
                    'description' => 'Name of the savings goal to update',
                ],
                'add_amount' => [
                    'type' => 'number',
                    'description' => 'Amount to add to current savings',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['in_progress', 'completed', 'cancelled'],
                    'description' => 'New status for the goal',
                ],
            ],
            'required' => ['goal_name'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $goalName = $arguments['goal_name'] ?? '';
            $addAmount = (float) ($arguments['add_amount'] ?? 0);
            $status = $arguments['status'] ?? null;

            if (empty($goalName)) {
                return [
                    'success' => false,
                    'result' => 'Goal name is required.',
                ];
            }

            $goal = SavingsGoal::where('user_id', $userId)
                ->where('name', 'like', "%{$goalName}%")
                ->first();

            if (!$goal) {
                return [
                    'success' => false,
                    'result' => "Savings goal '{$goalName}' not found.",
                ];
            }

            $messages = [];

            if ($addAmount != 0) {
                $goal->current_amount += $addAmount;
                $amountFormatted = number_format(abs($addAmount), 0, ',', '.');
                $action = $addAmount > 0 ? 'Added' : 'Subtracted';
                $messages[] = "{$action} {$amountFormatted}đ";

                // Auto-complete if target reached
                if ($goal->current_amount >= $goal->target_amount && $goal->status === 'in_progress') {
                    $goal->status = SavingsGoal::STATUS_COMPLETED;
                    $messages[] = "🎉 Goal completed!";
                }
            }

            if ($status && $status !== $goal->status) {
                $goal->status = $status;
                $messages[] = "Status changed to {$status}";
            }

            $goal->save();

            $current = number_format($goal->current_amount, 0, ',', '.');
            $target = number_format($goal->target_amount, 0, ',', '.');
            $progress = $goal->getProgressPercentage();

            $result = "Updated '{$goal->name}': {$current}/{$target}đ ({$progress}%)";
            if (!empty($messages)) {
                $result .= "\n- " . implode("\n- ", $messages);
            }

            return [
                'success' => true,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('UpdateSavingsGoalTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to update savings goal: ' . $e->getMessage(),
            ];
        }
    }
}
