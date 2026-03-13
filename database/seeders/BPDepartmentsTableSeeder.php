<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPDepartmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bp_departments')->insert([
            [ 'dept_code' => 'ADMIN', 'dept_name' => 'Administration', 'description' => 'Overall facility management and leadership.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'HR', 'dept_name' => 'Human Resources', 'description' => 'Recruitment, onboarding, and employee relations.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'NURS', 'dept_name' => 'Nursing', 'description' => 'Direct patient care and nursing services.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'MED', 'dept_name' => 'Medical', 'description' => 'Physicians and medical oversight.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'SOC', 'dept_name' => 'Social Services', 'description' => 'Resident advocacy and social work.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'ACT', 'dept_name' => 'Activities', 'description' => 'Resident activities and recreation.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'DIET', 'dept_name' => 'Dietary', 'description' => 'Meal planning and food service.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'ENV', 'dept_name' => 'Environmental Services', 'description' => 'Housekeeping and laundry.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'MAINT', 'dept_name' => 'Maintenance', 'description' => 'Facility maintenance and repairs.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'IT', 'dept_name' => 'Information Technology', 'description' => 'IT infrastructure and support.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'FIN', 'dept_name' => 'Finance', 'description' => 'Budgeting and financial planning.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'QA', 'dept_name' => 'Quality Assurance', 'description' => 'Regulatory compliance and quality improvement.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'ADMIT', 'dept_name' => 'Admissions', 'description' => 'Resident intake and census management.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'PHARM', 'dept_name' => 'Pharmacy', 'description' => 'Medication management and pharmacy services.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'REHAB', 'dept_name' => 'Rehabilitation', 'description' => 'Physical, occupational, and speech therapy.', 'created_at' => now(), 'updated_at' => now() ],
            [ 'dept_code' => 'SEC', 'dept_name' => 'Security', 'description' => 'Facility security and safety.', 'created_at' => now(), 'updated_at' => now() ],
        ]);
    }
}
