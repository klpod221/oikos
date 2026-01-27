<?php

namespace App\Services\AI\Tools;

use App\Models\UserStats;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * Tool to update user physical stats.
 */
class UpdateUserStatsTool extends AITool
{
    public function name(): string
    {
        return 'update_user_stats';
    }

    public function description(): string
    {
        return 'Update user physical stats like weight, height, or activity level. Creates a new record for tracking history.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'weight' => [
                    'type' => 'number',
                    'description' => 'Weight in kg',
                ],
                'height' => [
                    'type' => 'number',
                    'description' => 'Height in cm',
                ],
                'age' => [
                    'type' => 'integer',
                    'description' => 'Age in years',
                ],
                'gender' => [
                    'type' => 'string',
                    'enum' => ['male', 'female', 'other'],
                    'description' => 'Gender',
                ],
                'activity_level' => [
                    'type' => 'string',
                    'enum' => ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active'],
                    'description' => 'Activity level',
                ],
            ],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            // Get current stats as base
            $currentStats = UserStats::where('user_id', $userId)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $newData = [
                'user_id' => $userId,
                'weight' => $arguments['weight'] ?? $currentStats?->weight ?? 70,
                'height' => $arguments['height'] ?? $currentStats?->height ?? 170,
                'age' => $arguments['age'] ?? $currentStats?->age ?? 25,
                'gender' => $arguments['gender'] ?? $currentStats?->gender ?? 'other',
                'activity_level' => $arguments['activity_level'] ?? $currentStats?->activity_level ?? 'moderately_active',
                'recorded_at' => Carbon::today(),
            ];

            $stats = UserStats::create($newData);

            $updates = [];
            if (isset($arguments['weight'])) {
                $updates[] = "Weight: {$arguments['weight']} kg";
            }
            if (isset($arguments['height'])) {
                $updates[] = "Height: {$arguments['height']} cm";
            }
            if (isset($arguments['age'])) {
                $updates[] = "Age: {$arguments['age']}";
            }
            if (isset($arguments['activity_level'])) {
                $updates[] = "Activity: {$arguments['activity_level']}";
            }

            $updateList = !empty($updates) ? implode(', ', $updates) : 'Stats recorded';

            return [
                'success' => true,
                'result' => "Updated stats: {$updateList}",
                'data' => $stats,
            ];
        } catch (\Exception $e) {
            Log::error('UpdateUserStatsTool failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'result' => 'Failed to update stats: ' . $e->getMessage()];
        }
    }
}
