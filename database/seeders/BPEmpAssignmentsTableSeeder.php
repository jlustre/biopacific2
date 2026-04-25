<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpAssignmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [];
        for ($i = 1; $i <= 20; $i++) {
            $isUnion = $i % 2 === 0;
            $assignments[] = [
                'employee_num' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'effdt' => '2022-01-01',
                'effseq' => 0,
                'facility_id' => ($i % 2) + 1, // alternate between 1 and 2
                'dept_id' => ($i % 5) + 1, // cycle through 1-5
                'job_code_id' => ($i % 5) + 1, // cycle through 1-5
                'reports_to_employee_num' => null,
                'reg_temp' => 'r',
                'full_part_time' => $i % 3 === 0 ? 'pt' : 'ft',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'bargaining_unit_id' => $isUnion ? 1 : null,
                'union_seniority_dt' => $isUnion ? '2022-01-01' : null,
            ];
        }
        DB::table('bp_emp_assignments')->insert($assignments);
    }
}
