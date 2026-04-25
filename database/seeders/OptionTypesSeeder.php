<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Example seed data, adjust as needed
        $optionTypes = [
            ['name' => 'Marital Status', 'isActive' => 1],
            ['name' => 'Ethnic Group', 'isActive' => 1],
            ['name' => 'Military Status', 'isActive' => 1],
            ['name' => 'Citizenship Status', 'isActive' => 1],
            ['name' => 'Hourly Status', 'isActive' => 1],
            ['name' => 'Standard Hours', 'isActive' => 1],
            ['name' => 'Compensation Rate', 'isActive' => 1],
        ];

        DB::table('optionstypes')->insert($optionTypes);
    }
}
