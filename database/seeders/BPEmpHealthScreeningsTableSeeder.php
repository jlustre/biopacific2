<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpHealthScreeningsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_emp_health_screenings')->insert([
            [
                'emp_id' => 'EMP001',
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
