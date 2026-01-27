<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ShoppingListItem Model
 *
 * Individual items in a shopping list with aggregated quantities.
 *
 * @property int $id
 * @property int $shopping_list_id
 * @property int $ingredient_id
 * @property float $total_quantity Aggregated grams
 * @property bool $is_purchased
 */
class ShoppingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopping_list_id',
        'ingredient_id',
        'total_quantity',
        'is_purchased',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:2',
        'is_purchased' => 'boolean',
    ];

    /**
     * Get the shopping list this item belongs to.
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    /**
     * Get the ingredient for this item.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Scope: Get only purchased items.
     */
    public function scopePurchased($query)
    {
        return $query->where('is_purchased', true);
    }

    /**
     * Scope: Get only unpurchased items.
     */
    public function scopeUnpurchased($query)
    {
        return $query->where('is_purchased', false);
    }
}
