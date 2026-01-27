<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'klpod221@gmail.com'],
            [
                'name' => 'klpod221',
                'password' => Hash::make('klpod221'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}
