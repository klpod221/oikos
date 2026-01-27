<?php

namespace App\Http\Controllers\Api\Nutrition;

use App\Http\Controllers\Controller;
use App\Models\NutritionLog;
use App\Models\ShoppingList;
use App\Services\Nutrition\NutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Advanced Nutrition Controller
 *
 * Handles shopping lists, nutrition logging, and macro tracking.
 */
class AdvancedNutritionController extends Controller
{
    public function __construct(
        protected NutritionService $nutritionService
    ) {}

    /**
     * Generate shopping list preview
     */
    public function previewShoppingList(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Return aggregated ingredients without saving
        $aggregatedIngredients = DB::table('meal_plans as mp')
            ->join('recipes as r', 'mp.recipe_id', '=', 'r.id')
            ->join('recipe_ingredients as ri', 'r.id', '=', 'ri.recipe_id')
            ->join('ingredients as i', 'ri.ingredient_id', '=', 'i.id')
            ->where('mp.user_id', Auth::id())
            ->whereBetween('mp.date', [$validated['start_date'], $validated['end_date']])
            ->whereNotNull('mp.recipe_id')
            ->select([
                'ri.ingredient_id',
                'i.name as ingredient_name',
                DB::raw('SUM(ri.quantity * COALESCE(i.gram_conversion_factor, 1)) as total_quantity')
            ])
            ->groupBy('ri.ingredient_id', 'i.name')
            ->get();

        return response()->json([
            'items' => $aggregatedIngredients,
            'count' => count($aggregatedIngredients),
        ]);
    }

    /**
     * Create shopping list
     */
    public function storeShoppingList(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'items' => 'nullable|array',
        ]);

        $shoppingList = $this->nutritionService->generateShoppingList(
            Auth::user(),
            \Carbon\Carbon::parse($validated['start_date']),
            \Carbon\Carbon::parse($validated['end_date']),
            $validated['name']
        );

        return response()->json($shoppingList, 201);
    }

    /**
     * Get user's shopping lists
     */
    public function getShoppingLists(Request $request)
    {
        $lists = ShoppingList::where('user_id', Auth::id())
            ->with('items.ingredient')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($lists);
    }

    /**
     * Update shopping list item (mark as purchased)
     */
    public function updateShoppingListItem(Request $request, $listId, $itemId)
    {
        $validated = $request->validate([
            'is_purchased' => 'required|boolean',
        ]);

        $item = \App\Models\ShoppingListItem::where('shopping_list_id', $listId)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->update($validated);

        return response()->json($item);
    }

    /**
     * Log nutrition intake
     */
    public function logNutrition(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'recipe_id' => 'nullable|exists:recipes,id',
            'ingredient_id' => 'nullable|exists:ingredients,id',
            'quantity' => 'nullable|numeric|min:0',
            'servings' => 'nullable|numeric|min:0.1',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
        ]);

        $log = NutritionLog::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($log, 201);
    }

    /**
     * Get nutrition logs
     */
    public function getNutritionLogs(Request $request)
    {
        $query = NutritionLog::where('user_id', Auth::id());

        if ($request->has('date')) {
            $query->forDate($request->date);
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }

        $logs = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Get macro progress for a date
     */
    public function getMacroProgress(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $progress = $this->nutritionService->getMacroProgress(
            Auth::user(),
            \Carbon\Carbon::parse($validated['date'])
        );

        return response()->json($progress);
    }
}
