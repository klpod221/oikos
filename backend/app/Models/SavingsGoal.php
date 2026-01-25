<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class SavingsGoal
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property float $target_amount
 * @property float $current_amount
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string $status
 * @property string|null $icon
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 *
 * @package App\Models
 */
class SavingsGoal extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'currency',
        'start_date',
        'deadline',
        'status',
        'icon',
        'color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'start_date' => 'date',
            'deadline' => 'date',
        ];
    }

    // ===== Relationships =====

    /**
     * Savings goal belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== Query Scopes =====

    /**
     * Scope to get in-progress goals
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to get completed goals
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get cancelled goals
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // ===== Helper Methods =====

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return min($percentage, 100); // Cap at 100%
    }

    /**
     * Get remaining amount to reach goal
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Add amount to current savings
     */
    public function addAmount(float $amount): void
    {
        $this->increment('current_amount', $amount);

        // Auto-complete if target reached
        if ($this->current_amount >= $this->target_amount) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }

    /**
     * Check if goal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if goal is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if deadline has passed
     */
    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->lt(now()) && $this->isInProgress();
    }
}
