<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\SeedsUserEmployeeRecords;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'user' . $i . '@biopacific.com'],
                [
                    'name' => 'User ' . $i,
                    'password' => Hash::make('password'),
                    'facility_id' => ($i % 2) + 1,
                ]
            );

            if (!$user->hasRole('regular-user')) {
                $user->assignRole('regular-user');
            }

            SeedsUserEmployeeRecords::seed($user, [
                'employee_num' => 'EMP' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'facility_id' => ($i % 2) + 1,
                'position_index' => $i - 1,
                'is_union' => $i % 2 === 0,
                'original_hire_dt' => sprintf(
                    '%04d-%02d-01',
                    2016 + intdiv($i - 1, 12),
                    (($i - 1) % 12) + 1
                ),
            ]);
        }
    }
}
