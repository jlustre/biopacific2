<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'user' . $i . '@example.com'],
                [
                    'name' => 'User ' . $i,
                    'password' => Hash::make('password'),
                ]
            );

            if (!$user->hasRole('regular-user')) {
                $user->assignRole('regular-user');
            }
        }
    }
}
