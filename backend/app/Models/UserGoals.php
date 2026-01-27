<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserGoals Model
 *
 * Fitness and nutrition goals for the user.
 *
 * @property int $id
 * @property int $user_id
 * @property string $goal_type
 * @property float|null $target_weight
 * @property int|null $target_calories
 * @property float|null $target_protein
 * @property float|null $target_carbs
 * @property float|null $target_fat
 * @property int $weekly_workout_target
 */
class UserGoals extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_type',
        'target_weight',
        'target_calories',
        'target_protein',
        'target_carbs',
        'target_fat',
        'weekly_workout_target',
    ];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'target_calories' => 'integer',
        'target_protein' => 'decimal:2',
        'target_carbs' => 'decimal:2',
        'target_fat' => 'decimal:2',
        'weekly_workout_target' => 'integer',
    ];

    /**
     * Get the user these goals belong to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific macro target is met.
     *
     * @param string $macro 'protein', 'carbs', or 'fat'
     * @param float $actual Actual intake
     * @return bool Whether target is met
     */
    public function isMacroTargetMet(string $macro, float $actual): bool
    {
        $target = $this->{"target_{$macro}"};

        if (!$target) {
            return true; // No target set
        }

        // Allow 5% margin
        return $actual >= ($target * 0.95);
    }
}
