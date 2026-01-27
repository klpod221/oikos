<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ShoppingList Model
 *
 * Generated shopping lists from meal plans with aggregated ingredient quantities.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string $status 'draft', 'finalized', 'purchased'
 */
class ShoppingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who owns this shopping list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this shopping list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    /**
     * Scope: Get only finalized lists.
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    /**
     * Scope: Get only purchased lists.
     */
    public function scopePurchased($query)
    {
        return $query->where('status', 'purchased');
    }
}
