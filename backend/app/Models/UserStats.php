<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserStats Model
 *
 * Physical statistics for BMR/TDEE calculations with historical tracking.
 *
 * @property int $id
 * @property int $user_id
 * @property float $weight Kilograms
 * @property float $height Centimeters
 * @property int $age Years
 * @property string $gender 'male', 'female', 'other'
 * @property string $activity_level
 * @property \Carbon\Carbon $recorded_at
 */
class UserStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'height',
        'age',
        'gender',
        'activity_level',
        'recorded_at',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'age' => 'integer',
        'recorded_at' => 'date',
    ];

    /**
     * Get the user these stats belong to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get latest stats for a user.
     */
    public function scopeLatestFor($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    /**
     * Get activity level multiplier for TDEE calculation.
     *
     * @return float Multiplier value
     */
    public function getActivityMultiplier(): float
    {
        return match ($this->activity_level) {
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'moderately_active' => 1.55,
            'very_active' => 1.725,
            'extra_active' => 1.9,
            default => 1.2,
        };
    }
}
