<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BPEmpChecklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = DB::table('bp_employees')->pluck('employee_num');
        // Get all checklist items
        $items = DB::table('checklist_items')->get();
        $now = Carbon::now();
        $rows = [];
        // Do not insert any records initially. Only insert when at least one item is verified.
        // This seeder will not insert any records by default.
    }
}
