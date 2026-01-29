<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin User Controller
 *
 * Handles admin management of users.
 *
 * @package App\Http\Controllers\Api\Admin
 */
class UserController extends Controller
{
    public function __construct(
        protected AdminUserService $userService
    ) {}

    /**
     * List all users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers(
            $request->only(['search', 'role', 'status', 'sort_by', 'sort_order']),
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Show user details
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Block user
     */
    public function block(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);

        // Prevent blocking self or other admins (optional policy)
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot block admin users.',
            ], 403);
        }

        $user = $this->userService->blockUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Unblock user
     */
    public function unblock(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);
        $user = $this->userService->unblockUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully',
            'data' => new UserResource($user),
        ]);
    }
    /**
     * Create a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:user,admin',
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->userService->getUser($id);
        $this->userService->resetPassword($user, $validated['password']);

        return response()->json([
            'success' => true,
            'message' => 'User password has been reset successfully',
        ]);
    }
}
