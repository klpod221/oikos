<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'is_global',
        'name',
        'unit',
        'calories',
        'protein',
        'carbs',
        'fat',
        'fiber',
        'sugar',
        'image',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_global' => 'boolean',
            'calories' => 'decimal:2',
            'protein' => 'decimal:2',
            'carbs' => 'decimal:2',
            'fat' => 'decimal:2',
            'fiber' => 'decimal:2',
            'sugar' => 'decimal:2',
        ];
    }

    // ===== Relationships =====

    /**
     * Ingredient belongs to a user (nullable)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== Query Scopes =====

    /**
     * Scope to get global ingredients
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope to get custom ingredients for a user
     */
    public function scopeCustomFor(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->where('is_global', false);
    }

    /**
     * Scope to get available ingredients (Global + User's custom)
     */
    public function scopeAvailableFor(Builder $query, int $userId): Builder
    {
        return $query->where('is_global', true)
            ->orWhere('user_id', $userId);
    }
}
