<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $states = [
            ['name' => 'Alabama', 'abbreviation' => 'AL', 'created_at' => $now],
            ['name' => 'Alaska', 'abbreviation' => 'AK', 'created_at' => $now],
            ['name' => 'Arizona', 'abbreviation' => 'AZ', 'created_at' => $now],
            ['name' => 'Arkansas', 'abbreviation' => 'AR', 'created_at' => $now],
            ['name' => 'California', 'abbreviation' => 'CA', 'created_at' => $now],
            ['name' => 'Colorado', 'abbreviation' => 'CO', 'created_at' => $now],
            ['name' => 'Connecticut', 'abbreviation' => 'CT', 'created_at' => $now],
            ['name' => 'Delaware', 'abbreviation' => 'DE', 'created_at' => $now],
            ['name' => 'Florida', 'abbreviation' => 'FL', 'created_at' => $now],
            ['name' => 'Georgia', 'abbreviation' => 'GA', 'created_at' => $now],
            ['name' => 'Hawaii', 'abbreviation' => 'HI', 'created_at' => $now],
            ['name' => 'Idaho', 'abbreviation' => 'ID', 'created_at' => $now],
            ['name' => 'Illinois', 'abbreviation' => 'IL', 'created_at' => $now],
            ['name' => 'Indiana', 'abbreviation' => 'IN', 'created_at' => $now],
            ['name' => 'Iowa', 'abbreviation' => 'IA', 'created_at' => $now],
            ['name' => 'Kansas', 'abbreviation' => 'KS', 'created_at' => $now],
            ['name' => 'Kentucky', 'abbreviation' => 'KY', 'created_at' => $now],
            ['name' => 'Louisiana', 'abbreviation' => 'LA', 'created_at' => $now],
            ['name' => 'Maine', 'abbreviation' => 'ME', 'created_at' => $now],
            ['name' => 'Maryland', 'abbreviation' => 'MD', 'created_at' => $now],
            ['name' => 'Massachusetts', 'abbreviation' => 'MA', 'created_at' => $now],
            ['name' => 'Michigan', 'abbreviation' => 'MI', 'created_at' => $now],
            ['name' => 'Minnesota', 'abbreviation' => 'MN', 'created_at' => $now],
            ['name' => 'Mississippi', 'abbreviation' => 'MS', 'created_at' => $now],
            ['name' => 'Missouri', 'abbreviation' => 'MO', 'created_at' => $now],
            ['name' => 'Montana', 'abbreviation' => 'MT', 'created_at' => $now],
            ['name' => 'Nebraska', 'abbreviation' => 'NE', 'created_at' => $now],
            ['name' => 'Nevada', 'abbreviation' => 'NV', 'created_at' => $now],
            ['name' => 'New Hampshire', 'abbreviation' => 'NH', 'created_at' => $now],
            ['name' => 'New Jersey', 'abbreviation' => 'NJ', 'created_at' => $now],
            ['name' => 'New Mexico', 'abbreviation' => 'NM', 'created_at' => $now],
            ['name' => 'New York', 'abbreviation' => 'NY', 'created_at' => $now],
            ['name' => 'North Carolina', 'abbreviation' => 'NC', 'created_at' => $now],
            ['name' => 'North Dakota', 'abbreviation' => 'ND', 'created_at' => $now],
            ['name' => 'Ohio', 'abbreviation' => 'OH', 'created_at' => $now],
            ['name' => 'Oklahoma', 'abbreviation' => 'OK', 'created_at' => $now],
            ['name' => 'Oregon', 'abbreviation' => 'OR', 'created_at' => $now],
            ['name' => 'Pennsylvania', 'abbreviation' => 'PA', 'created_at' => $now],
            ['name' => 'Rhode Island', 'abbreviation' => 'RI', 'created_at' => $now],
            ['name' => 'South Carolina', 'abbreviation' => 'SC', 'created_at' => $now],
            ['name' => 'South Dakota', 'abbreviation' => 'SD', 'created_at' => $now],
            ['name' => 'Tennessee', 'abbreviation' => 'TN', 'created_at' => $now],
            ['name' => 'Texas', 'abbreviation' => 'TX', 'created_at' => $now],
            ['name' => 'Utah', 'abbreviation' => 'UT', 'created_at' => $now],
            ['name' => 'Vermont', 'abbreviation' => 'VT', 'created_at' => $now],
            ['name' => 'Virginia', 'abbreviation' => 'VA', 'created_at' => $now],
            ['name' => 'Washington', 'abbreviation' => 'WA', 'created_at' => $now],
            ['name' => 'West Virginia', 'abbreviation' => 'WV', 'created_at' => $now],
            ['name' => 'Wisconsin', 'abbreviation' => 'WI', 'created_at' => $now],
            ['name' => 'Wyoming', 'abbreviation' => 'WY', 'created_at' => $now],
        ];
        DB::table('states')->insert($states);
    }
}
