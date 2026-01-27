<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', 'user.active'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // User Profile & Settings
    Route::get('/user/profile', [\App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::put('/user/profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    Route::post('/user/avatar', [\App\Http\Controllers\Api\UserController::class, 'updateAvatar']);
    Route::put('/user/preferences', [\App\Http\Controllers\Api\UserController::class, 'updatePreferences']);
    Route::put('/user/password', [\App\Http\Controllers\Api\UserController::class, 'changePassword']);

    // Finance Module
    Route::apiResource('wallets', \App\Http\Controllers\Api\Finance\WalletController::class);
    Route::apiResource('categories', \App\Http\Controllers\Api\Finance\CategoryController::class); // User's categories
    Route::apiResource('transactions', \App\Http\Controllers\Api\Finance\TransactionController::class);
    Route::apiResource('savings-goals', \App\Http\Controllers\Api\Finance\SavingsGoalController::class);

    // Statistics
    Route::get('/statistics', [\App\Http\Controllers\Api\Finance\StatisticsController::class, 'index']);
    Route::post('/statistics/refresh', [\App\Http\Controllers\Api\Finance\StatisticsController::class, 'refresh']);

    // External Data (Weather, Exchange Rates, Metals)
    Route::get('/external-data', [\App\Http\Controllers\Api\ExternalDataController::class, 'index']);
    Route::post('/external-data/refresh', [\App\Http\Controllers\Api\ExternalDataController::class, 'refresh']);

    // Nutrition Module
    Route::apiResource('ingredients', \App\Http\Controllers\Api\Nutrition\IngredientController::class);
    Route::apiResource('recipes', \App\Http\Controllers\Api\Nutrition\RecipeController::class);
    Route::apiResource('meal-plans', \App\Http\Controllers\Api\Nutrition\MealPlanController::class);

    // Advanced Nutrition
    Route::prefix('nutrition')->group(function () {
        Route::post('/shopping-list/preview', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'previewShoppingList']);
        Route::post('/shopping-list', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'storeShoppingList']);
        Route::get('/shopping-lists', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'getShoppingLists']);
        Route::patch('/shopping-lists/{listId}/items/{itemId}', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'updateShoppingListItem']);
        Route::post('/logs', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'logNutrition']);
        Route::get('/logs', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'getNutritionLogs']);
        Route::get('/macros/progress', [\App\Http\Controllers\Api\Nutrition\AdvancedNutritionController::class, 'getMacroProgress']);
    });

    // Workout Module
    Route::prefix('workout')->group(function () {
        Route::get('/exercises', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getExercises']);
        Route::post('/exercises', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'storeExercise']);
        Route::get('/routines', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getRoutines']);
        Route::get('/routines/{id}', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getRoutine']);
        Route::post('/routines', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'storeRoutine']);
        Route::put('/routines/{id}', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'updateRoutine']);
        Route::delete('/routines/{id}', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'deleteRoutine']);
        Route::get('/schedules', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getSchedules']);
        Route::post('/schedules', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'storeSchedule']);
        Route::delete('/schedules/{id}', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'deleteSchedule']);
        Route::get('/upcoming', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getUpcomingWorkouts']);
        Route::post('/logs', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'logWorkout']);
        Route::get('/logs', [\App\Http\Controllers\Api\Workout\WorkoutController::class, 'getWorkoutLogs']);
    });

    // Integration (Energy Balance)
    Route::prefix('integration')->group(function () {
        Route::get('/energy-balance', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'getEnergyBalance']);
        Route::get('/energy-balance/trend', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'getTrend']);
        Route::get('/goal-warnings', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'getGoalWarnings']);
        Route::post('/user-stats', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'updateUserStats']);
        Route::get('/user-stats', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'getUserStats']);
        Route::post('/user-goals', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'updateUserGoals']);
        Route::get('/user-goals', [\App\Http\Controllers\Api\Integration\EnergyBalanceController::class, 'getUserGoals']);
    });

    // AI Chat (RAG + Function Calling + Streaming)
    Route::prefix('chat')->group(function () {
        Route::post('/send', [\App\Http\Controllers\Api\ChatController::class, 'send']);
        Route::get('/history', [\App\Http\Controllers\Api\ChatController::class, 'history']);
        Route::delete('/clear', [\App\Http\Controllers\Api\ChatController::class, 'clear']);
    });
});



// Admin routes
Route::middleware(['auth:sanctum', 'user.active', 'admin'])->prefix('admin')->group(function () {
    // User Management
    Route::get('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
    Route::get('/users/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'show']);
    Route::post('/users/{id}/block', [\App\Http\Controllers\Api\Admin\UserController::class, 'block']);
    Route::post('/users/{id}/unblock', [\App\Http\Controllers\Api\Admin\UserController::class, 'unblock']);

    // Master Data: Categories
    Route::apiResource('categories', \App\Http\Controllers\Api\Admin\CategoryController::class);

    // Master Data: Ingredients
    Route::apiResource('ingredients', \App\Http\Controllers\Api\Admin\IngredientController::class);
});
