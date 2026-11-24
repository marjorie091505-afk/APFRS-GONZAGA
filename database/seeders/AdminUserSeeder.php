<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('airline.admin.password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set AIRLINE_ADMIN_PASSWORD before seeding the administrator account.');
        }

        $admin = User::updateOrCreate(
            ['email' => config('airline.admin.email')],
            [
                'name' => config('airline.admin.name'),
                'password' => Hash::make($password),
            ]
        );

        $admin->forceFill([
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
