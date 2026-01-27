<?php

namespace App\Services\Nutrition;

use App\Models\Ingredient;
use App\Models\MealPlan;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nutrition Service
 *
 * Advanced nutrition calculations including unit conversion and shopping list aggregation.
 */
class NutritionService
{
    /**
     * Convert ingredient quantity to grams using conversion factor.
     *
     * @param Ingredient $ingredient Ingredient model
     * @param float $quantity Original quantity
     * @return float Quantity in grams
     */
    public function convertToGrams(Ingredient $ingredient, float $quantity): float
    {
        // If gram_conversion_factor is NULL, ingredient is already in grams
        if ($ingredient->gram_conversion_factor === null) {
            return $quantity;
        }

        // Apply conversion factor (e.g., 1 egg = 50g)
        return $quantity * $ingredient->gram_conversion_factor;
    }

    /**
     * Generate shopping list from meal plans in a date range.
     *
     * Aggregation Logic:
     * 1. Query all meal_plans for user in date range
     * 2. Join with recipe_ingredients via recipes
     * 3. Normalize quantities to grams using gram_conversion_factor
     * 4. GROUP BY ingredient_id and SUM quantities
     * 5. Create shopping_list_items with aggregated totals
     *
     * @param User $user User model
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string $name Shopping list name
     * @return ShoppingList Created shopping list
     */
    public function generateShoppingList(User $user, Carbon $startDate, Carbon $endDate, string $name = null): ShoppingList
    {
        // Create shopping list
        $shoppingList = ShoppingList::create([
            'user_id' => $user->id,
            'name' => $name ?? "Shopping List {$startDate->format('M d')} - {$endDate->format('M d')}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'draft',
        ]);

        // Aggregate ingredients from meal plans
        $aggregatedIngredients = DB::table('meal_plans as mp')
            ->join('recipes as r', 'mp.recipe_id', '=', 'r.id')
            ->join('recipe_ingredients as ri', 'r.id', '=', 'ri.recipe_id')
            ->join('ingredients as i', 'ri.ingredient_id', '=', 'i.id')
            ->where('mp.user_id', $user->id)
            ->whereBetween('mp.date', [$startDate, $endDate])
            ->whereNotNull('mp.recipe_id') // Only meal plans with recipes
            ->select([
                'ri.ingredient_id',
                DB::raw('SUM(ri.quantity * COALESCE(i.gram_conversion_factor, 1)) as total_grams')
            ])
            ->groupBy('ri.ingredient_id')
            ->get();

        // Create shopping list items
        foreach ($aggregatedIngredients as $item) {
            ShoppingListItem::create([
                'shopping_list_id' => $shoppingList->id,
                'ingredient_id' => $item->ingredient_id,
                'total_quantity' => $item->total_grams,
                'is_purchased' => false,
            ]);
        }

        return $shoppingList->load('items.ingredient');
    }

    /**
     * Calculate macro totals from an array of ingredients.
     *
     * @param array $ingredients Array with ingredient_id and quantity
     * @return array Macro totals
     */
    public function calculateMacros(array $ingredients): array
    {
        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
            'fiber' => 0,
            'sugar' => 0,
        ];

        foreach ($ingredients as $item) {
            $ingredient = Ingredient::find($item['ingredient_id']);
            if (!$ingredient) {
                continue;
            }

            $quantityGrams = $this->convertToGrams($ingredient, $item['quantity']);

            // Ingredient nutrition is per 100g, so scale accordingly
            $factor = $quantityGrams / 100;

            $totals['calories'] += $ingredient->calories * $factor;
            $totals['protein'] += $ingredient->protein * $factor;
            $totals['carbs'] += $ingredient->carbs * $factor;
            $totals['fat'] += $ingredient->fat * $factor;
            $totals['fiber'] += $ingredient->fiber * $factor;
            $totals['sugar'] += $ingredient->sugar * $factor;
        }

        return $totals;
    }

    /**
     * Get macro progress for a user on a specific date with color codes.
     *
     * Returns macro intake vs targets with color-coded progress.
     *
     * @param User $user User model
     * @param Carbon $date Date
     * @return array Progress data
     */
    public function getMacroProgress(User $user, Carbon $date): array
    {
        $goals = $user->goals;

        $nutrition = DB::table('nutrition_logs')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->select([
                DB::raw('SUM(protein) as total_protein'),
                DB::raw('SUM(carbs) as total_carbs'),
                DB::raw('SUM(fat) as total_fat'),
            ])
            ->first();

        $current = [
            'protein' => $nutrition->total_protein ?? 0,
            'carbs' => $nutrition->total_carbs ?? 0,
            'fat' => $nutrition->total_fat ?? 0,
        ];

        $target = [
            'protein' => $goals->target_protein ?? 150,
            'carbs' => $goals->target_carbs ?? 200,
            'fat' => $goals->target_fat ?? 60,
        ];

        $progress = [];
        foreach (['protein', 'carbs', 'fat'] as $macro) {
            $percentage = $target[$macro] > 0 ? ($current[$macro] / $target[$macro]) * 100 : 0;

            // Color codes: Green (Protein), Yellow (Carbs), Red (Fat)
            $color = match ($macro) {
                'protein' => '#22c55e', // Green
                'carbs' => '#f59e0b',   // Yellow
                'fat' => '#ef4444',      // Red
            };

            $progress[$macro] = [
                'current' => round($current[$macro], 1),
                'target' => round($target[$macro], 1),
                'percentage' => min(round($percentage, 1), 100), // Cap at 100%
                'color' => $color,
                'status' => $percentage >= 95 ? 'met' : ($percentage >= 80 ? 'close' : 'below'),
            ];
        }

        return $progress;
    }

    /**
     * Update ingredient conversion factor.
     *
     * @param Ingredient $ingredient Ingredient model
     * @param float|null $conversionFactor Conversion factor (NULL if already in grams)
     * @return Ingredient Updated ingredient
     */
    public function updateConversionFactor(Ingredient $ingredient, ?float $conversionFactor): Ingredient
    {
        $ingredient->update(['gram_conversion_factor' => $conversionFactor]);
        return $ingredient;
    }
}
