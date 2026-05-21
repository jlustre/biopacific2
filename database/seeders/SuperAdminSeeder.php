<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions if they don't exist
        $allPermissions = [
            'view facilities',
            'create facilities',
            'edit facilities',
            'delete facilities',
            'manage users',
            'manage roles',
            'manage permissions',
            'use import mapping presets',
            'create import mapping presets',
            // Add more permissions here as needed
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create or get the SuperAdmin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // Assign ALL permissions to SuperAdmin role
        $superAdminRole->syncPermissions(Permission::all());

        $superAdminEmail = 'super-admin@biopacific.com';

        $superAdminUser = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'Joey Lustre',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'facility_id' => 99,
            ]
        );

        if ($superAdminUser->wasRecentlyCreated) {
            $this->command->info('Super admin user created successfully.');
        } else {
            $this->command->info('User already exists, updating role...');
        }

        // Update existing user's facility if not set
        if (!$superAdminUser->facility_id) {
            $superAdminUser->update(['facility_id' => 99]);
            $this->command->info('Updated user facility assignment.');
        }

        // Assign the SuperAdmin role to the user (ensures all permissions)
        $superAdminUser->syncRoles(['super-admin']);
        $this->command->info('SuperAdmin role synced to user.');
    }
}
