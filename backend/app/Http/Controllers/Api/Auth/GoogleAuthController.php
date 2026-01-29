<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Laravel\Socialite\Common\SocialiteStruct;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Google Auth Controller
 */
class GoogleAuthController extends Controller
{
    /**
     * Get the Google Auth URL.
     *
     * @return JsonResponse
     */
    public function getAuthUrl(): JsonResponse
    {
        // We use stateless() because we are implementing an API-based flow
        // The frontend will handle the redirect
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');

        $url = $driver
            ->stateless()
            ->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/gmail.readonly'
            ])
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Handle the Google Callback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            // Because we are stateless, we get the user directly from the driver using the access token or code
            // However, Socialite stateless helper looks for 'code' in the request automatically.
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');

            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->stateless()->user();

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if (! $user) {
                if (SystemSetting::getValue('allow_registration') === false) {
                    return response()->json(['message' => 'Đăng ký tài khoản đang tạm khóa.'], 403);
                }

                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(16)), // Random password
                    'role' => User::ROLE_USER,
                    'status' => User::STATUS_ACTIVE,
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
            } else {
                // Update existing user with Google info (Tokens only)
                // We do NOT overwrite name or avatar if the user already exists
                $user->update([
                    'google_id' => $googleUser->id, // Ensure ID is linked if matched by email
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]);
            }

            // Create Sanctum Token
            $token = $user->createToken($request->device_name ?? 'web')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
