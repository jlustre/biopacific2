<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpHealthScreeningsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first employee's numeric id
        $empId = DB::table('bp_employees')->orderBy('id')->value('id');
        DB::table('bp_emp_health_screenings')->insert([
            [
                'emp_id' => $empId,
                'screening_type' => 'tb test',
                'screening_date' => '2023-06-01',
                'expiry_date' => '2024-06-01',
                'result' => 'negative',
                'provider' => 'health clinic',
                'notes' => 'annual screening',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
