<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionstypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('optionstypes')->insert([
            ['name' => 'Department', 'isActive' => 1],
            ['name' => 'Job Title', 'isActive' => 1],
            ['name' => 'Document Type', 'isActive' => 1],
            ['name' => 'Status', 'isActive' => 1],
            ['name' => 'Status', 'isActive' => 1],
            ['name' => 'Credentials', 'isActive' => 1],
        ]);
    }
}
