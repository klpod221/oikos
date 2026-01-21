<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminUserService
{
    /**
     * Get list of users with filtering
     */
    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when(isset($filters['search']), function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->when(isset($filters['role']), function (Builder $query, $role) {
                $query->where('role', $role);
            })
            ->when(isset($filters['status']), function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
