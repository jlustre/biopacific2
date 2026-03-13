<?php

namespace Database\Seeders;

use App\Models\ColorScheme;
use App\Models\JobDescriptionTemplate;
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
            EmailTemplatesSeeder::class,
            // GeneralJobOpeningSeeder::class,
            TestUsersSeeder::class,
            DepartmentSeeder::class,
            PositionsSeeder::class,
            JobDescriptionsSeeder::class,
            UsersTableSeeder::class,
            BPDepartmentsTableSeeder::class,
            BPPositionsTableSeeder::class,
            BPBargainingUnitsTableSeeder::class,
            BPEmpEmployeesTableSeeder::class,
            BPEmpAssignmentsTableSeeder::class,
            BPEmpCompensationTableSeeder::class,
            BPEmpCredentialsTableSeeder::class,
            BPEmpPhonesTableSeeder::class,
            BPEmpAddressesTableSeeder::class,
            BPEmpHealthScreeningsTableSeeder::class,
        ]);

    }
}
