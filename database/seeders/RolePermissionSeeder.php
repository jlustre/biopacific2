<?php

namespace Database\Seeders;

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
        $permissions = [
            // Facility Management
            'view facilities',
            'create facilities',
            'edit facilities',
            'delete facilities',
            'manage facility settings',
            'view facility analytics',
            
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage users',
            'view user profiles',
            
            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'manage permissions',
            'assign roles',
            'revoke roles',
            
            // Content Management
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'manage testimonials',
            'manage news',
            'manage faqs',
            'manage galleries',
            'manage services',
            
            // Communications
            'view communications',
            'manage tour requests',
            'manage inquiries',
            'manage job applications',
            'manage email recipients',
            
            // Security & Monitoring
            'view security dashboard',
            'view audit logs',
            'manage security settings',
            'view system monitoring',
            
            // System Administration
            'access admin panel',
            'view system settings',
            'manage system settings',
            'view reports',
            'export data',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with descriptions
        $facilityAdmin = Role::firstOrCreate(['name' => 'facility-admin'], ['name' => 'facility-admin']);
        $facilityEditor = Role::firstOrCreate(['name' => 'facility-editor'], ['name' => 'facility-editor']);
        $regularUser = Role::firstOrCreate(['name' => 'regular-user'], ['name' => 'regular-user']);

        // New HR roles (short names)
        $hrrd = Role::firstOrCreate(['name' => 'hrrd'], ['name' => 'hrrd']);
        $facilityDsd = Role::firstOrCreate(['name' => 'facility-dsd'], ['name' => 'facility-dsd']);

        // Assign permissions to roles

        // Web Admin - Full system access

        // Facility Admin - Comprehensive facility management
        $facilityAdmin->syncPermissions([
            'access admin panel',
            'view facilities', 'create facilities', 'edit facilities', 'manage facility settings', 'view facility analytics',
            'view users', 'create users', 'edit users', 'view user profiles',
            'view content', 'create content', 'edit content', 'publish content',
            'manage testimonials', 'manage news', 'manage faqs', 'manage galleries', 'manage services',
            'view communications', 'manage tour requests', 'manage inquiries', 'manage job applications',
            'view reports', 'export data'
        ]);

        // HR Regional Director (hrrd) - All facilities HR portal access
        $hrrd->syncPermissions([
            'access admin panel',
            'view facilities', 'view users', 'view user profiles',
            'view content', 'view communications', 'view reports',
            // Add more HR-specific permissions as needed
        ]);

        // Facility DSD - Assigned facility HR portal access
        $facilityDsd->syncPermissions([
            'access admin panel',
            'view facilities', 'view users', 'view user profiles',
            'view content', 'view communications', 'view reports',
            // Add more HR-specific permissions as needed
        ]);

        // Facility Editor - Content and basic management
        $facilityEditor->syncPermissions([
            'access admin panel',
            'view facilities', 'edit facilities',
            'view users', 'view user profiles',
            'view content', 'create content', 'edit content',
            'manage testimonials', 'manage news', 'manage faqs', 'manage galleries', 'manage services',
            'view communications', 'manage tour requests', 'manage inquiries'
        ]);

        // Regular User - View only access
        $regularUser->syncPermissions([
            'access admin panel',
            'view facilities',
            'view users', 'view user profiles',
            'view content',
            'view communications'
        ]);
    }
}
