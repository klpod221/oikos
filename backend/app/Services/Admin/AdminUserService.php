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
}
