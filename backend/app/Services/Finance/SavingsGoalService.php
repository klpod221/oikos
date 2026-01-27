<?php

namespace App\Services\Finance;

use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Collection;

/**
 * Savings Goal Service
 *
 * Service for managing savings goals.
 *
 * @package App\Services\Finance
 */
class SavingsGoalService
{
    /**
     * Get user savings goals
     *
     * @param int $userId
     * @return Collection
     */
    public function getGoals(int $userId): Collection
    {
        return SavingsGoal::where('user_id', $userId)
            ->orderBy('deadline', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create savings goal
     */
    public function createGoal(int $userId, array $data): SavingsGoal
    {
        $data['user_id'] = $userId;
        return SavingsGoal::create($data);
    }

    /**
     * Update savings goal
     */
    public function updateGoal(SavingsGoal $goal, array $data): SavingsGoal
    {
        $goal->update($data);
        return $goal;
    }

    /**
     * Delete savings goal
     */
    public function deleteGoal(SavingsGoal $goal): void
    {
        $goal->delete();
    }
}
