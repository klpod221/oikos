<?php

namespace App\Services\AI\Tools;

use App\Models\UserGoals;

/**
 * Tool to get user's fitness and nutrition goals.
 */
class GetUserGoalsTool extends AITool
{
    public function name(): string
    {
        return 'get_user_goals';
    }

    public function description(): string
    {
        return 'Get the user\'s fitness and nutrition goals, including target weight, calorie goals, and macro targets.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass(),
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $goals = UserGoals::where('user_id', $userId)->first();

        if (!$goals) {
            return [
                'success' => true,
                'result' => 'No fitness/nutrition goals set yet. Would you like to set some goals?',
            ];
        }

        $result = "Your Goals:\n";
        $result .= "🎯 Goal Type: {$goals->goal_type}\n";

        if ($goals->target_weight) {
            $result .= "⚖️ Target Weight: {$goals->target_weight} kg\n";
        }

        if ($goals->target_calories) {
            $result .= "🔥 Daily Calories: {$goals->target_calories} kcal\n";
        }

        if ($goals->target_protein) {
            $result .= "🥩 Protein: {$goals->target_protein}g\n";
        }

        if ($goals->target_carbs) {
            $result .= "🍚 Carbs: {$goals->target_carbs}g\n";
        }

        if ($goals->target_fat) {
            $result .= "🥑 Fat: {$goals->target_fat}g\n";
        }

        $result .= "🏋️ Weekly Workouts: {$goals->weekly_workout_target} sessions";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
