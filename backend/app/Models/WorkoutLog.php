<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WorkoutLog Model
 *
 * Tracks completed workouts with actual performance data.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $routine_id
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int|null $duration_seconds
 * @property float $calories_burned
 * @property array|null $exercises_completed
 * @property string|null $notes
 */
class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'routine_id',
        'started_at',
        'completed_at',
        'duration_seconds',
        'calories_burned',
        'exercises_completed',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'calories_burned' => 'decimal:2',
        'exercises_completed' => 'array',
    ];

    /**
     * Get the user who performed this workout.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the routine that was performed.
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /**
     * Scope: Get logs for a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('started_at', $date);
    }

    /**
     * Scope: Get logs between dates.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }
}
