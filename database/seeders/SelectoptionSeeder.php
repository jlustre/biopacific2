<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SelectoptionSeeder extends Seeder
{
    public function run()
    {
        DB::table('selectoptions')->insert([
            // Example options for Department
            ['type_id' => 1, 'name' => 'HR', 'isActive' => 1, 'sort_order' => 1],
            ['type_id' => 1, 'name' => 'Finance', 'isActive' => 1, 'sort_order' => 2],
            ['type_id' => 1, 'name' => 'Operations', 'isActive' => 1, 'sort_order' => 3],
            // Example options for Job Title
            ['type_id' => 2, 'name' => 'Manager', 'isActive' => 1, 'sort_order' => 1],
            ['type_id' => 2, 'name' => 'Staff', 'isActive' => 1, 'sort_order' => 2],
            // Example options for Document Type
            ['type_id' => 3, 'name' => 'Resume', 'isActive' => 1, 'sort_order' => 1],
            ['type_id' => 3, 'name' => 'Certificate', 'isActive' => 1, 'sort_order' => 2],
            // Example options for Status
            ['type_id' => 4, 'name' => 'Active', 'isActive' => 1, 'sort_order' => 1],
            ['type_id' => 4, 'name' => 'Inactive', 'isActive' => 1, 'sort_order' => 2],
            // Example options for credentials
            ['type_id' => 5, 'name' => 'Nurse License', 'isActive' => 1, 'sort_order' => 1],
            ['type_id' => 5, 'name' => 'Medical License', 'isActive' => 1, 'sort_order' => 2],
        ]);
    }
}
