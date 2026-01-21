<?php

namespace App\Services\Nutrition;

use App\Models\MealPlan;
use Illuminate\Database\Eloquent\Collection;

class MealPlanService
{
    public function getMealPlans(int $userId, ?string $start = null, ?string $end = null): Collection
    {
        $query = MealPlan::where('user_id', $userId)
            ->with(['recipe']);

        if ($start) {
            $query->whereDate('date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('date', '<=', $end);
        }

        return $query->orderBy('date', 'asc')->orderBy('meal_type')->get();
    }

    public function createPlan(int $userId, array $data): MealPlan
    {
        $data['user_id'] = $userId;
        return MealPlan::create($data);
    }

    public function updatePlan(MealPlan $plan, array $data): MealPlan
    {
        $plan->update($data);
        return $plan;
    }

    public function deletePlan(MealPlan $plan): void
    {
        $plan->delete();
    }

    public function toggleStatus(MealPlan $plan, string $status): MealPlan
    {
        $plan->update(['status' => $status]);
        return $plan;
    }
}
