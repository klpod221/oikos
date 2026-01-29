<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auth Controller
 *
 * Handles user authentication (login, register, logout).
 *
 * @package App\Http\Controllers\Api\Auth
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Login
     *
     * Authenticate user with email and password to receive an access token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ]);
    }

    /**
     * Register
     *
     * Create a new user account and return an access token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        if (\App\Models\SystemSetting::getValue('allow_registration') === false) {
            return response()->json(['message' => 'Đăng ký tài khoản đang tạm khóa.'], 403);
        }

        $result = $this->authService->register(
            $request->validated(),
            $request->device_name
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Logout
     *
     * Revoke the user's current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get Profile
     *
     * Get the authenticated user's profile information.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()),
        ]);
    }
}
