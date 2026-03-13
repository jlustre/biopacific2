<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpCompensationTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_emp_compensation')->insert([
            [
                'emp_id' => 'EMP001',
                'effdt' => '2023-01-01',
                'effseq' => 0,
                'base_rate' => 45.00,
                'rate_type' => 'h',
                'fte' => 1.00,
                'pay_frequency' => 'b',
                'reason_code' => 'hire',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
