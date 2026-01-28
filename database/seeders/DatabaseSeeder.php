<?php

namespace Database\Seeders;

use App\Models\ColorScheme;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            ColorSchemesTableSeeder::class,
            FacilitySeeder::class,
            BioPacificCorporateSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            WebContentsSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            ServiceSeeder::class,
            FacilityServiceSeeder::class,
            NewsSeeder::class,
            FacilityNewsSeeder::class,
            EmailRecipientsTableSeeder::class,
            EmployeeEmailMappingsTableSeeder::class,
            GeneralJobOpeningSeeder::class,
            TestUsersSeeder::class,
        ]);

    }
}
