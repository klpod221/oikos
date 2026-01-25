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
    Route::apiResource('ingredients', \App\Http\Controllers\Api\Nutrition\IngredientController::class); // User custom ingredients
    Route::apiResource('recipes', \App\Http\Controllers\Api\Nutrition\RecipeController::class);
    Route::apiResource('meal-plans', \App\Http\Controllers\Api\Nutrition\MealPlanController::class);
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
