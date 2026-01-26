<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DailySummary Model
 *
 * Pre-calculated daily energy balance for dashboard performance.
 * Cached values for BMR, TDEE, intake, and balance.
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $date
 * @property float $bmr Basal Metabolic Rate
 * @property float $activity_calories From activity level
 * @property float $workout_calories From workout logs
 * @property float $tdee Total Daily Energy Expenditure
 * @property float $total_calories Nutrition intake
 * @property float $total_protein
 * @property float $total_carbs
 * @property float $total_fat
 * @property float $energy_balance Intake - TDEE
 */
class DailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'bmr',
        'activity_calories',
        'workout_calories',
        'tdee',
        'total_calories',
        'total_protein',
        'total_carbs',
        'total_fat',
        'energy_balance',
    ];

    protected $casts = [
        'date' => 'date',
        'bmr' => 'decimal:2',
        'activity_calories' => 'decimal:2',
        'workout_calories' => 'decimal:2',
        'tdee' => 'decimal:2',
        'total_calories' => 'decimal:2',
        'total_protein' => 'decimal:2',
        'total_carbs' => 'decimal:2',
        'total_fat' => 'decimal:2',
        'energy_balance' => 'decimal:2',
    ];

    /**
     * Get the user this summary belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get summary for a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: Get summaries between dates.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if user is in caloric surplus.
     *
     * @return bool
     */
    public function isInSurplus(): bool
    {
        return $this->energy_balance > 0;
    }

    /**
     * Check if user is in caloric deficit.
     *
     * @return bool
     */
    public function isInDeficit(): bool
    {
        return $this->energy_balance < 0;
    }

    /**
     * Get deficit/surplus percentage relative to TDEE.
     *
     * @return float Percentage
     */
    public function getBalancePercentage(): float
    {
        if ($this->tdee == 0) {
            return 0;
        }

        return ($this->energy_balance / $this->tdee) * 100;
    }
}
