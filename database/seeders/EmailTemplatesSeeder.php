<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (EmailTemplate::query()->doesntExist()) {
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

        EmailTemplate::firstOrCreate(
            ['name' => 'registration-account'],
            [
                'category' => 'onboarding',
                'subject' => 'Your Bio-Pacific HR portal registration code',
                'body' => "Hello {first_name},\n\nYou have been invited to create your Bio-Pacific HR portal account.\n\n<strong>Registration code:</strong> {registration_code}\n\n<a href=\"{registration_link}\">Create Your Account</a>\n\nWhen registering, use your full name, work email address, and either your employee number ({employee_num}) or the last 4 digits of your Social Security number to verify your identity.\n\nThis code expires on {registration_expiration}.\n\nIf you did not expect this message, please contact your facility administrator at {facility_name}.",
                'is_active' => true,
            ]
        );

        EmailTemplate::firstOrCreate(
            ['name' => 'welcome-account'],
            [
                'category' => 'onboarding',
                'subject' => 'Welcome to Bio-Pacific — verify your email',
                'body' => "Hello {first_name},\n\nWelcome to Bio-Pacific. Your portal account has been created successfully.\n\nPlease verify your email address to activate your account:\n<a href=\"{verification_link}\">Verify Email Address</a>\n\nAfter verifying, sign in here:\n<a href=\"{dashboard_link}\">Open Portal Dashboard</a>\n\nIf you did not create this account, please contact your facility administrator.",
                'is_active' => true,
            ]
        );
    }
}
