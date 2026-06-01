<?php

namespace Database\Seeders;

use App\Support\Rbac\Permissions as RbacPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create comprehensive permissions
        $permissions = RbacPermissions::all();

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with descriptions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $facilityAdmin = Role::firstOrCreate(['name' => 'facility-admin'], ['name' => 'facility-admin']);
        $facilityEditor = Role::firstOrCreate(['name' => 'facility-editor'], ['name' => 'facility-editor']);
        $regularUser = Role::firstOrCreate(['name' => 'regular-user'], ['name' => 'regular-user']);

        // New HR roles (short names)
        $rdhr = Role::firstOrCreate(['name' => 'rdhr'], ['name' => 'rdhr']);
        $facilityDsd = Role::firstOrCreate(['name' => 'facility-dsd'], ['name' => 'facility-dsd']);
        $don = Role::firstOrCreate(['name' => 'don'], ['name' => 'don']);
        $ssd = Role::firstOrCreate(['name' => 'ssd'], ['name' => 'ssd']);
        $activitiesDirector = Role::firstOrCreate(['name' => 'activities-director'], ['name' => 'activities-director']);

        // Assign permissions to roles

        // Super Admin — full system access
        $superAdmin->syncPermissions(Permission::all()->pluck('name')->toArray());

        // System admin — full access including import preset creation
        $admin->syncPermissions(Permission::all()->pluck('name')->toArray());

        // Facility Admin - Comprehensive facility management
        $facilityAdmin->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::ACCESS_HR_PORTAL,
            RbacPermissions::VIEW_POSITIONS,
            RbacPermissions::EDIT_POSITIONS,
            RbacPermissions::EDIT_EMPLOYEE_CORE_TABS,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::CREATE_FACILITIES,
            RbacPermissions::EDIT_FACILITIES,
            RbacPermissions::MANAGE_FACILITY_SETTINGS,
            RbacPermissions::VIEW_FACILITY_ANALYTICS,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::CREATE_USERS,
            RbacPermissions::EDIT_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::CREATE_CONTENT,
            RbacPermissions::EDIT_CONTENT,
            RbacPermissions::PUBLISH_CONTENT,
            RbacPermissions::MANAGE_TESTIMONIALS,
            RbacPermissions::MANAGE_NEWS,
            RbacPermissions::MANAGE_FAQS,
            RbacPermissions::MANAGE_GALLERIES,
            RbacPermissions::MANAGE_SERVICES,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::MANAGE_TOUR_REQUESTS,
            RbacPermissions::MANAGE_INQUIRIES,
            RbacPermissions::MANAGE_JOB_APPLICATIONS,
            RbacPermissions::VIEW_REPORTS,
            RbacPermissions::EXPORT_DATA,
            RbacPermissions::USE_IMPORT_MAPPING_PRESETS,
        ]);

        // HR Regional Director (rdhr) - All facilities HR portal access
        $rdhr->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::ACCESS_HR_PORTAL,
            RbacPermissions::VIEW_POSITIONS,
            RbacPermissions::EDIT_POSITIONS,
            RbacPermissions::EDIT_EMPLOYEE_CORE_TABS,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::VIEW_REPORTS,
            RbacPermissions::USE_IMPORT_MAPPING_PRESETS,
        ]);

        // Facility DSD - Assigned facility HR portal access
        $facilityDsd->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::ACCESS_HR_PORTAL,
            RbacPermissions::VIEW_POSITIONS,
            RbacPermissions::EDIT_POSITIONS,
            RbacPermissions::EDIT_EMPLOYEE_CORE_TABS,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::VIEW_REPORTS,
            RbacPermissions::USE_IMPORT_MAPPING_PRESETS,
        ]);

        // Director of Nursing - facility clinical leadership access
        $don->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::ACCESS_HR_PORTAL,
            RbacPermissions::VIEW_POSITIONS,
            RbacPermissions::EDIT_POSITIONS,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::VIEW_REPORTS,
            RbacPermissions::USE_IMPORT_MAPPING_PRESETS,
        ]);

        // Social Services Director - facility oversight access (limited)
        $ssd->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::VIEW_REPORTS,
        ]);

        // Activities Director - facility content and communication access
        $activitiesDirector->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
        ]);

        // Facility Editor - Content and basic management
        $facilityEditor->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::EDIT_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::CREATE_CONTENT,
            RbacPermissions::EDIT_CONTENT,
            RbacPermissions::MANAGE_TESTIMONIALS,
            RbacPermissions::MANAGE_NEWS,
            RbacPermissions::MANAGE_FAQS,
            RbacPermissions::MANAGE_GALLERIES,
            RbacPermissions::MANAGE_SERVICES,
            RbacPermissions::VIEW_COMMUNICATIONS,
            RbacPermissions::MANAGE_TOUR_REQUESTS,
            RbacPermissions::MANAGE_INQUIRIES,
            RbacPermissions::USE_IMPORT_MAPPING_PRESETS,
        ]);

        // Regular User - View only access
        $regularUser->syncPermissions([
            RbacPermissions::ACCESS_ADMIN_PANEL,
            RbacPermissions::VIEW_FACILITIES,
            RbacPermissions::VIEW_USERS,
            RbacPermissions::VIEW_USER_PROFILES,
            RbacPermissions::VIEW_CONTENT,
            RbacPermissions::VIEW_COMMUNICATIONS,
        ]);
    }
}
