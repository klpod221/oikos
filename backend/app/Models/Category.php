<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Scope constants
     */
    public const SCOPE_SYSTEM = 'system';
    public const SCOPE_CUSTOM = 'custom';

    /**
     * Type constants
     */
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'scope',
        'icon',
        'color',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ===== Relationships =====

    /**
     * Category belongs to a user (nullable for system categories)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category has a parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Category has many child categories (subcategories)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Category has many transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ===== Query Scopes =====

    /**
     * Scope to get only system (global) categories
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('scope', self::SCOPE_SYSTEM);
    }

    /**
     * Scope to get only custom (user-specific) categories
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('scope', self::SCOPE_CUSTOM);
    }

    /**
     * Scope to get income categories
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    /**
     * Scope to get expense categories
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    /**
     * Scope to get active categories only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get categories available for a specific user
     * (system categories + user's custom categories)
     */
    public function scopeAvailableFor(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('scope', self::SCOPE_SYSTEM)
                ->orWhere('user_id', $userId);
        });
    }

    // ===== Helper Methods =====

    /**
     * Check if category is system category
     */
    public function isSystem(): bool
    {
        return $this->scope === self::SCOPE_SYSTEM;
    }

    /**
     * Check if category is custom category
     */
    public function isCustom(): bool
    {
        return $this->scope === self::SCOPE_CUSTOM;
    }
}
