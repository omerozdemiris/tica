<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ExampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'role' => 1,
            ]
        );
        // Super admin
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('123456'),
                'role' => 2,
            ]
        );
        // Standard user
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Standard User',
                'username' => 'user',
                'password' => Hash::make('123456'),
                'role' => 0,
            ]
        );
    }
}


