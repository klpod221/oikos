<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Auth Service
 *
 * Service for user authentication and registration.
 *
 * @package App\Services\Auth
 */
class AuthService
{
    /**
     * Authenticate user and create token
     *
     * @param string $email
     * @param string $password
     * @param string|null $deviceName
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->isBlocked()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been blocked. Please contact support.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $deviceName = $deviceName ?? 'web';
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Register new user
     *
     * @param array $data
     * @param string|null $deviceName
     * @return array{user: User, token: string}
     */
    public function register(array $data, ?string $deviceName = null): array
    {
        $role = \App\Models\SystemSetting::getValue('default_user_role', User::ROLE_USER);
        $requireVerification = \App\Models\SystemSetting::getValue('require_email_verification', false);
        $status = $requireVerification ? User::STATUS_PENDING : User::STATUS_ACTIVE;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'status' => $status,
        ]);

        $deviceName = $deviceName ?? 'web';
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user (revoke token)
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        $token->delete();
    }
}
