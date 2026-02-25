<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('email_templates')->insert([
            [
                'name' => 'Application Received',
                'category' => 'applicant',
                'subject' => 'We received your application',
                'body' => "Hi {first_name},\n\nThank you for applying to {facility_name}. We have received your application for {job_title}. Our team will review it and contact you if your qualifications match our needs.\n\nBest regards,\n{facility_name} Hiring Team",
                'is_active' => true,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pre-Employment Link',
                'category' => 'applicant',
                'subject' => 'Your pre-employment link is ready',
                'body' => "Hi {first_name},\n\nYour pre-employment steps are ready. Please use the secure link below to continue:\n{pre_employment_link}\n\nYour code: {applicant_code}\n\nIf you need help, contact HR at hr@biopacific.com.\n\nBest regards,\nBio-Pacific HR",
                'is_active' => true,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
