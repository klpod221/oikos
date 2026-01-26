<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WorkoutSchedule Model
 *
 * Represents flexible workout scheduling with JSON configuration.
 * Supports weekly patterns, intervals, and specific dates.
 *
 * @property int $id
 * @property int $user_id
 * @property int $routine_id
 * @property string|null $name
 * @property array $schedule_config JSON configuration
 * @property bool $is_active
 * @property string|null $start_date
 * @property string|null $end_date
 */
class WorkoutSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'routine_id',
        'name',
        'schedule_config',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who owns this schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the routine for this schedule.
     */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /**
     * Scope: Get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
