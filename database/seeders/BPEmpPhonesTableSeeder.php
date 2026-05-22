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
                'effdt' => '2023-01-01',
                'effseq' => 0,
                'phone_number' => '555-123-4567',
                'is_primary' => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_num' => 'EMP001',
                'phone_type' => 'H',
                'effdt' => '2023-01-01',
                'effseq' => 0,
                'phone_number' => '509-516-6142',
                'is_primary' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
