<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpPhonesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
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
        ];

        foreach ($rows as $row) {
            $exists = DB::table('bp_emp_phones')
                ->where('employee_num', $row['employee_num'])
                ->where('phone_type', $row['phone_type'])
                ->where('effdt', $row['effdt'])
                ->where('effseq', $row['effseq'])
                ->exists();

            if (!$exists) {
                DB::table('bp_emp_phones')->insert($row);
            }
        }
    }
}
