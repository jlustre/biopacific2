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
            'name' => 'Application Received',
            'category' => 'applicant',
            'subject' => 'We received your application',
            'body' => "Hi {first_name},\n\nThank you for applying to {facility_name}. We have received your application for {job_title}. Our team will review it and contact you if your qualifications match our needs.\n\nBest regards,\n{facility_name} Hiring Team",
            'is_active' => true,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
