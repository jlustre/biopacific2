<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Non-transactional / reference data required for the application to function.
 *
 * Safe for production fresh installs: roles, departments, positions, checklist
 * templates, upload types, option lists, corporate facility shell, and the
 * super-admin login account.
 *
 * Does NOT seed other demo users, sample employees, marketing content, or uploads.
 *
 * Run alone: php artisan db:seed --class=StructuralSeeder
 * Also runs automatically via: php artisan db:seed (always)
 */
class StructuralSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Must run first — facilities reference color_scheme_id by numeric id
            ColorSchemesTableSeeder::class,
            // Services catalog must exist before facility_service pivot rows
            ServiceSeeder::class,
            // Facilities & public-site configuration (after color_schemes and services exist)
            FacilitySeeder::class,
            WebContentsSeeder::class,
            FacilityServiceSeeder::class,

            // Visual / layout catalog
            LayoutSectionsSeeder::class,
            SimpleTemplatesSeeder::class,

            // RBAC
            RolePermissionSeeder::class,

            // HR reference data
            StatesTableSeeder::class,
            BPBargainingUnitsTableSeeder::class,
            DepartmentSeeder::class,
            PositionsSeeder::class,
            DocTypesTableSeeder::class,
            UploadTypesTableSeeder::class,
            OptionTypesSeeder::class,
            SelectOptionsSeeder::class,

            // Checklists & performance templates
            ChecklistItemsSeeder::class,
            OrientationChecklistItemsSeeder::class,
            ChecklistItemPositionBackfillSeeder::class,
            EmployeePerformanceItemsSeeder::class,
            EmployeeCompetencyItemsSeeder::class,

            // Job description templates (services catalog seeded above)
            JobDescriptionsSeeder::class,

            // Reporting & imports (reference reports only; presets run after super-admin exists)
            ReportCategoriesTableSeeder::class,
            ReportSeeder::class,

            // Communications & compliance reference
            EmailTemplatesSeeder::class,
            DefaultFaqSeeder::class,
            BaaVendorSeeder::class,

            // Required corporate public-site facility (id 99, uses color_scheme_id 33)
            BioPacificCorporateSeeder::class,

            // Super-admin account (production-safe; idempotent via firstOrCreate)
            SuperAdminSeeder::class,

            // Import presets reference owner_email — must run after SuperAdminSeeder
            ImportMappingPresetsTableSeeder::class,

            // Inactive catch-all job opening per facility (requires at least corporate facility)
            GeneralJobOpeningSeeder::class,
        ]);
    }
}
