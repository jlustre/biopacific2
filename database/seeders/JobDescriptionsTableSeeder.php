<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JobDescriptionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('job_descriptions')->insert([
            [
                'name' => 'RN Template',
                'title' => 'Registered Nurse',
                'description' => 'Provide nursing care to patients.',
                'detailed_description' => 'Responsible for patient assessments, medication administration, and care planning.',
                'department' => 'Nursing',
                'employment_type' => 'Full-time',
                'reporting_to' => 'Director of Nursing',
                'status' => 'open',
                'active' => true,
                'created_by' => 1,
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'LVN Template',
                'title' => 'Licensed Vocational Nurse',
                'description' => 'Assist in patient care under RN supervision.',
                'detailed_description' => 'Supports RNs in daily patient care, documentation, and basic procedures.',
                'department' => 'Nursing',
                'employment_type' => 'Part-time',
                'reporting_to' => 'Registered Nurse',
                'status' => 'open',
                'active' => true,
                'created_by' => 1,
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CNA Template',
                'title' => 'Certified Nursing Assistant',
                'description' => 'Assist patients with daily living activities.',
                'detailed_description' => 'Provides basic care, hygiene, and support for patients.',
                'department' => 'Nursing',
                'employment_type' => 'Per Diem',
                'reporting_to' => 'Registered Nurse',
                'status' => 'open',
                'active' => true,
                'created_by' => 1,
                'posted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
