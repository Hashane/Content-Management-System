<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cms.com'],
            [
                'name' => 'Administrator',
                'password' => 'Password123!',
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin']);

        $moderator = User::firstOrCreate(
            ['email' => 'moderator@cms.com'],
            [
                'name' => 'Content Moderator',
                'password' => 'Password123!',
                'email_verified_at' => now(),
            ],
        );
        $moderator->syncRoles(['moderator']);
    }
}
