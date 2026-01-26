<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Exercise Model
 *
 * Represents individual exercises with video instructions and calorie estimates.
 * Supports both rep-based (e.g., push-ups) and time-based (e.g., plank) exercises.
 *
 * @property int $id
 * @property int|null $user_id
 * @property bool $is_global
 * @property string $name
 * @property string|null $description
 * @property string|null $video_url
 * @property string $type 'reps' or 'time'
 * @property float|null $calories_per_unit Calories per rep OR per minute
 * @property string|null $image
 */
class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_global',
        'name',
        'description',
        'video_url',
        'type',
        'calories_per_unit',
        'image',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'calories_per_unit' => 'decimal:2',
    ];

    /**
     * Get the user who created this exercise.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the routines that include this exercise.
     */
    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class, 'routine_exercises')
            ->withPivot(['order', 'target_value', 'rest_time'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Scope: Get only global exercises.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope: Get user-specific or global exercises.
     */
    public function scopeAvailableFor($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_global', true);
        });
    }

    /**
     * Calculate calories burned for a given value.
     *
     * @param int $value Reps count OR duration in seconds
     * @return float Calories burned
     */
    public function calculateCalories(int $value): float
    {
        if (!$this->calories_per_unit) {
            return 0;
        }

        if ($this->type === 'time') {
            // Convert seconds to minutes
            return ($value / 60) * $this->calories_per_unit;
        }

        // Reps-based
        return $value * $this->calories_per_unit;
    }
}
