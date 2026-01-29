<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * User Settings Service
 *
 * Service for managing user profile, avatar, and settings.
 *
 * @package App\Services
 */
class UserSettingsService
{
    /**
     * Update user profile (name, email)
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return $user;
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(User $user, $file): string
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Create image manager instance with desired driver
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        // Read image from file input
        $image = $manager->read($file);

        // Resize image to max 500x500
        $image->scale(height: 500);

        // Encode image
        $encoded = $image->toJpeg(quality: 80);

        // Generate filename
        $filename = 'avatars/' . uniqid() . '.jpg';

        // Store optimized image
        Storage::disk('public')->put($filename, (string) $encoded);

        $user->update(['avatar' => $filename]);

        return $filename;
    }

    /**
     * Update user preferences (settings)
     */
    public function updatePreferences(User $user, array $data): UserSetting
    {
        return UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => $data['currency'] ?? 'VND',
                'gold_unit' => $data['gold_unit'] ?? 'lượng',
                'silver_unit' => $data['silver_unit'] ?? 'lượng',
                'language' => $data['language'] ?? 'vi',
                'theme' => $data['theme'] ?? 'system',
            ]
        );
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided password does not match our records.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
