<?php

namespace App\Services\AI\Tools;

use App\Models\UserStats;

/**
 * Tool to get user's physical stats.
 */
class GetUserStatsTool extends AITool
{
    public function name(): string
    {
        return 'get_user_stats';
    }

    public function description(): string
    {
        return 'Get the user\'s physical statistics including weight, height, age, and activity level. Used for BMR/TDEE calculations.';
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
        $stats = UserStats::where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (!$stats) {
            return [
                'success' => true,
                'result' => 'No physical stats recorded yet. Please update your stats in Settings.',
            ];
        }

        $activityLabels = [
            'sedentary' => 'Sedentary (little/no exercise)',
            'lightly_active' => 'Lightly Active (1-3 days/week)',
            'moderately_active' => 'Moderately Active (3-5 days/week)',
            'very_active' => 'Very Active (6-7 days/week)',
            'extra_active' => 'Extra Active (physical job + exercise)',
        ];

        $activityLabel = $activityLabels[$stats->activity_level] ?? $stats->activity_level;

        $result = "Your Physical Stats:\n";
        $result .= "⚖️ Weight: {$stats->weight} kg\n";
        $result .= "📏 Height: {$stats->height} cm\n";
        $result .= "🎂 Age: {$stats->age} years\n";
        $result .= "👤 Gender: " . ucfirst($stats->gender) . "\n";
        $result .= "🏃 Activity: {$activityLabel}\n";
        $result .= "📅 Recorded: {$stats->recorded_at->format('d/m/Y')}";

        return [
            'success' => true,
            'result' => $result,
        ];
    }
}
