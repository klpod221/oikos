<?php

namespace App\Services\AI\Tools;

use App\Models\SavingsGoal;
use Illuminate\Support\Facades\Log;

/**
 * Tool to create a new savings goal.
 */
class CreateSavingsGoalTool extends AITool
{
    public function name(): string
    {
        return 'create_savings_goal';
    }

    public function description(): string
    {
        return 'Create a new savings goal for the user. Useful for tracking progress towards financial targets.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Name of the goal (e.g., "New Laptop", "Vacation")',
                ],
                'target_amount' => [
                    'type' => 'number',
                    'description' => 'Target amount to save (VND)',
                ],
                'deadline' => [
                    'type' => 'string',
                    'description' => 'Target date in YYYY-MM-DD format (optional)',
                ],
                'initial_amount' => [
                    'type' => 'number',
                    'description' => 'Amount already saved (default: 0)',
                ],
            ],
            'required' => ['name', 'target_amount'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $name = $arguments['name'] ?? '';
            $targetAmount = (float) ($arguments['target_amount'] ?? 0);
            $deadline = $arguments['deadline'] ?? null;
            $initialAmount = (float) ($arguments['initial_amount'] ?? 0);

            if (empty($name)) {
                return [
                    'success' => false,
                    'result' => 'Goal name is required.',
                ];
            }

            if ($targetAmount <= 0) {
                return [
                    'success' => false,
                    'result' => 'Target amount must be greater than 0.',
                ];
            }

            $goal = SavingsGoal::create([
                'user_id' => $userId,
                'name' => $name,
                'target_amount' => $targetAmount,
                'current_amount' => $initialAmount,
                'currency' => 'VND',
                'start_date' => now()->toDateString(),
                'deadline' => $deadline,
                'status' => SavingsGoal::STATUS_IN_PROGRESS,
            ]);

            $target = number_format($targetAmount, 0, ',', '.');
            $result = "Created savings goal '{$name}' with target {$target}đ.";

            if ($deadline) {
                $result .= " Deadline: {$deadline}";
            }

            return [
                'success' => true,
                'result' => $result,
                'data' => $goal,
            ];
        } catch (\Exception $e) {
            Log::error('CreateSavingsGoalTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to create savings goal: ' . $e->getMessage(),
            ];
        }
    }
}
