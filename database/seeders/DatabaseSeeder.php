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
            TestUsersSeeder::class,
            DepartmentSeeder::class,
            PositionsSeeder::class,
            JobDescriptionsSeeder::class,
            UsersTableSeeder::class,
            DocTypesTableSeeder::class,
            BPBargainingUnitsTableSeeder::class,
            BPEmpEmployeesTableSeeder::class,
            BPEmpAssignmentsTableSeeder::class,
            BPEmpCompensationTableSeeder::class,
            BPEmpCredentialsTableSeeder::class,
            BPEmpPhonesTableSeeder::class,
            BPEmpAddressesTableSeeder::class,
            BPEmpHealthScreeningsTableSeeder::class,
            ChecklistItemsSeeder::class,
            OrientationChecklistItemsSeeder::class,
            ChecklistItemPositionBackfillSeeder::class,
            BPEmpChecklistSeeder::class,
            EmployeePerformanceItemsSeeder::class,
            EmployeeCompetencyItemsSeeder::class,
            StatesTableSeeder::class,
            UploadTypesTableSeeder::class,
            UploadsSeeder::class,
            ReportCategoriesTableSeeder::class,
            ReportSeeder::class,
            OptionTypesSeeder::class,
            SelectOptionsSeeder::class,
        ]);

    }
}
