<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpCredentialsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_emp_credentials')->insert([
            [
                'emp_id' => 'EMP001',
                'credential_type' => 'rn',
                'credential_number' => 'RN123456',
                'issue_date' => '2022-01-01',
                'expiry_date' => '2025-01-01',
                'issuing_authority' => 'ca board of nursing',
                'verified_via' => 'cdph',
                'last_verified_dt' => '2024-01-01',
                'status' => 'a',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
