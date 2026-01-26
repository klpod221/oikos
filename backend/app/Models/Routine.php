<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Routine Model
 *
 * Represents a workout routine as a sequence of exercises.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property int|null $estimated_duration Minutes
 */
class Routine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'estimated_duration',
    ];

    protected $casts = [
        'estimated_duration' => 'integer',
    ];

    /**
     * Get the user who created this routine.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exercises in this routine (ordered).
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'routine_exercises')
            ->withPivot(['order', 'target_value', 'rest_time'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Get workout schedules using this routine.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(WorkoutSchedule::class);
    }

    /**
     * Get workout logs for this routine.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }

    /**
     * Calculate total estimated calories for the routine.
     *
     * @return float Total calories
     */
    public function calculateEstimatedCalories(): float
    {
        $totalCalories = 0;

        foreach ($this->exercises as $exercise) {
            $targetValue = $exercise->pivot->target_value;
            $totalCalories += $exercise->calculateCalories($targetValue);
        }

        return $totalCalories;
    }
}
