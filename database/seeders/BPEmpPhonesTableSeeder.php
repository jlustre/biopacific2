<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpPhonesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_emp_phones')->insert([
            [
                'employee_num' => 'EMP001',
                'phone_type' => 'M',
                'phone_number' => '555-123-4567',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_num' => 'EMP001',
                'phone_type' => 'H',
                'phone_number' => '509-516-6142',
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
