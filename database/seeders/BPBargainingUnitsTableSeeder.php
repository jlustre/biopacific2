<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPBargainingUnitsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_bargaining_units')->insert([
            [
                'unit_name' => 'Nurses Union',
                'description' => 'Registered Nurses',
                'union_code' => 'SEIU-UHW',
                'local_number' => 'Local 2015',
                'contract_name' => 'Nurses Master Contract',
                'contract_expiry' => '2027-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'unit_name' => 'Service Workers',
                'description' => 'Service Employees',
                'union_code' => 'SEIU',
                'local_number' => 'Local 99',
                'contract_name' => 'Service Workers Agreement',
                'contract_expiry' => '2028-06-30',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
