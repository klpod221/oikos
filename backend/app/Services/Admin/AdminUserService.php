<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Admin User Service
 *
 * Service for administrative management of users.
 *
 * @package App\Services\Admin
 */
class AdminUserService
{
    /**
     * Get list of users with filtering
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::applyFilters($filters)->paginate($perPage);
    }

    /**
     * Get user details
     */
    public function getUser(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Block user
     */
    public function blockUser(User $user): User
    {
        $user->update(['status' => User::STATUS_BLOCKED]);
        $user->tokens()->delete(); // Revoke all tokens

        return $user;
    }

    /**
     * Unblock user
     */
    public function unblockUser(User $user): User
    {
        $user->update(['status' => User::STATUS_ACTIVE]);

        return $user;
    }
    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'] ?? 'user',
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Reset user password
     *
     * @param User $user
     * @param string $newPassword
     * @return User
     */
    public function resetPassword(User $user, string $newPassword): User
    {
        $user->update([
            'password' => bcrypt($newPassword),
        ]);

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return $user;
    }
}
