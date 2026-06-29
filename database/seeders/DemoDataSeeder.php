<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Transactional / demo-only data for local and staging environments.
 *
 * Facilities, users, employees, marketing content (news, testimonials, FAQs),
 * sample uploads, and portal dashboard fixtures. Do not run on production.
 *
 * Requires StructuralSeeder (or equivalent reference data) first — especially
 * ColorSchemesTableSeeder before FacilitySeeder.
 *
 * Run alone: php artisan db:seed --class=DemoDataSeeder
 * Runs via `php artisan db:seed` only when SEED_DEMO_DATA=true (see config/seeding.php).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Marketing / public content
            FaqSeeder::class,
            TestimonialSeeder::class,
            NewsSeeder::class,
            FacilityNewsSeeder::class,

            // Careers demo postings
            JobOpeningSeeder::class,

            // Facility contact routing (demo emails)
            EmailRecipientsTableSeeder::class,
            EmployeeEmailMappingsTableSeeder::class,

            // Demo users & linked employee records (super-admin is in StructuralSeeder)
            TestUsersSeeder::class,
            UsersTableSeeder::class,

            // Demo HR employee population
            BPEmpEmployeesTableSeeder::class,
            BPEmpJobDataTableSeeder::class,
            BPEmpCompensationTableSeeder::class,
            BPEmpCredentialsTableSeeder::class,
            BPEmpPhonesTableSeeder::class,
            BPEmpAddressesTableSeeder::class,
            BPEmpHealthScreeningsTableSeeder::class,
            BPEmpChecklistSeeder::class,
            UploadsSeeder::class,

            // Member portal demo widgets
            MemberProfilePanelsSeeder::class,
            MemberEmergencyContactsSeeder::class,
            MemberPortalDashboardSeeder::class,
        ]);
    }
}
