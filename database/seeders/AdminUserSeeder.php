<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Ensures no duplicate admin
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin', // Set default role to admin
                'password' => Hash::make('admin123'), // Change as needed
                'email_verified_at' => now(),
            ]
        );
    }
}
