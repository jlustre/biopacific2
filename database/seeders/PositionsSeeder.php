<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionsSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['title' => 'Registered Nurse', 'description' => 'Provides nursing care to residents.', 'department' => 'Nursing'],
            ['title' => 'Licensed Vocational Nurse', 'description' => 'Assists in nursing care under RN supervision.', 'department' => 'Nursing'],
            ['title' => 'Certified Nursing Assistant', 'description' => 'Provides basic care and assists residents with daily activities.', 'department' => 'Nursing'],
            ['title' => 'Director of Nursing', 'description' => 'Oversees nursing staff and operations.', 'department' => 'Nursing'],
            ['title' => 'MDS Coordinator', 'description' => 'Manages resident assessment and care plans.', 'department' => 'Nursing'],
            ['title' => 'Charge Nurse', 'description' => 'Supervises nursing staff during shifts.', 'department' => 'Nursing'],
            ['title' => 'Staff Development Coordinator', 'description' => 'Coordinates staff training and development.', 'department' => 'Nursing'],
            ['title' => 'Unit Clerk', 'description' => 'Provides administrative support to nursing unit.', 'department' => 'Nursing'],
            ['title' => 'Medical Records Clerk', 'description' => 'Manages resident medical records.', 'department' => 'Administration'],
            ['title' => 'Social Worker', 'description' => 'Supports residents and families with social needs.', 'department' => 'Social Services'],
            ['title' => 'Activities Director', 'description' => 'Plans and coordinates resident activities.', 'department' => 'Activities'],
            ['title' => 'Dietary Manager', 'description' => 'Oversees dietary services and staff.', 'department' => 'Dietary'],
            ['title' => 'Cook', 'description' => 'Prepares meals for residents.', 'department' => 'Dietary'],
            ['title' => 'Housekeeping Supervisor', 'description' => 'Supervises housekeeping staff.', 'department' => 'Housekeeping'],
            ['title' => 'Maintenance Director', 'description' => 'Oversees facility maintenance.', 'department' => 'Maintenance'],
            ['title' => 'Receptionist', 'description' => 'Manages front desk and resident inquiries.', 'department' => 'Administration'],
            ['title' => 'Business Office Manager', 'description' => 'Manages business office operations.', 'department' => 'Administration'],
            ['title' => 'Admissions Coordinator', 'description' => 'Coordinates resident admissions.', 'department' => 'Administration'],
        ];

        DB::table('positions')->insert($positions);
    }
}
