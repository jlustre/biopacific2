<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpAddressesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_emp_addresses')->insert([
            [
                'emp_id' => 'EMP001',
                'address_type' => 'H',
                'effdt' => '2023-01-01',
                'effseq' => 0,
                'address1' => '123 Main St',
                'address2' => null,
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip' => '90001',
                'country' => 'USA',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emp_id' => 'EMP001',
                'address_type' => 'H',
                'effdt' => '2022-12-01',
                'effseq' => 0,
                'address1' => '111 Fast payout Ct',
                'address2' => null,
                'city' => 'Colton',
                'state' => 'CA',
                'zip' => '90001',
                'country' => 'USA',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
