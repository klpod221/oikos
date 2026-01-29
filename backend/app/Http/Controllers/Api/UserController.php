<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * User Controller
 *
 * Handles user profile and settings.
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{
    public function __construct(
        protected UserSettingsService $userSettingsService
    ) {}

    /**
     * Get user profile and settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('settings');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'settings' => $user->settings,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $updatedUser = $this->userSettingsService->updateProfile($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $updatedUser,
        ]);
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048', // Max 2MB
        ]);

        $user = $request->user();
        $path = $this->userSettingsService->updateAvatar($user, $request->file('avatar'));

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'data' => [
                'avatar' => 'storage/' . $path,
            ],
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency' => 'nullable|string|in:VND,USD,EUR',
            'gold_unit' => 'nullable|string|in:oz,lượng,chỉ',
            'silver_unit' => 'nullable|string|in:oz,lượng,chỉ',
            'language' => 'nullable|string|in:vi,en',
            'theme' => 'nullable|string|in:light,dark,system',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = $request->user();
        $settings = $this->userSettingsService->updatePreferences($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => $settings,
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $this->userSettingsService->changePassword($user, $validated['old_password'], $validated['new_password']);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}
