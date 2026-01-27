<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NutritionLog Model
 *
 * Tracks actual daily food intake (separate from meal plans).
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $meal_plan_id
 * @property \Carbon\Carbon $date
 * @property string $meal_type
 * @property int|null $recipe_id
 * @property int|null $ingredient_id
 * @property float|null $quantity
 * @property float $servings
 * @property float $calories
 * @property float $protein
 * @property float $carbs
 * @property float $fat
 */
class NutritionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meal_plan_id',
        'date',
        'meal_type',
        'recipe_id',
        'ingredient_id',
        'quantity',
        'servings',
        'calories',
        'protein',
        'carbs',
        'fat',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
        'servings' => 'decimal:2',
        'calories' => 'decimal:2',
        'protein' => 'decimal:2',
        'carbs' => 'decimal:2',
        'fat' => 'decimal:2',
    ];

    /**
     * Get the user who logged this nutrition.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the meal plan associated with this log.
     */
    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    /**
     * Get the recipe if logged.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the ingredient if logged.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Scope: Get logs for a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: Get logs between dates.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
