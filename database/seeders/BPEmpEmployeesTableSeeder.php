<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpEmployeesTableSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [];
        $userIds = DB::table('users')->pluck('id', 'email');
        for ($i = 1; $i <= 20; $i++) {
            $email = 'user' . $i . '@example.com';
            $employees[] = [
                'emp_id' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'user_id' => $userIds[$email] ?? null,
                'ssn' => sprintf('%03d-%02d-%04d', rand(100,999), rand(10,99), rand(1000,9999)),
                'first_name' => 'First' . $i,
                'middle_name' => chr(65 + ($i % 26)),
                'last_name' => 'Last' . $i,
                'dob' => '198' . rand(0,9) . '-' . rand(1,12) . '-' . rand(1,28),
                'original_hire_dt' => '202' . rand(0,2) . '-' . rand(1,12) . '-' . rand(1,28),
                'gender' => $i % 2 == 0 ? 'M' : 'F',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('bp_employees')->insert($employees);
    }
}
