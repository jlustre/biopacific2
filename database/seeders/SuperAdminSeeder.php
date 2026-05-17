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

        // Create permissions if they don't exist
        $permissions = [
            'view facilities',
            'create facilities',
            'edit facilities',
            'delete facilities',
            'manage users',
            'manage roles',
            'manage permissions'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create or get the admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Assign all permissions to admin role
        $adminRole->syncPermissions($permissions);

        $adminEmail = 'admin@biopacific.com';

        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Joey Lustre',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'facility_id' => 99,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info('Super admin user created successfully.');
        } else {
            $this->command->info('User already exists, updating role...');
        }

        // Update existing user's facility if not set
        if (!$user->facility_id) {
            $user->update(['facility_id' => 99]);
            $this->command->info('Updated user facility assignment.');
        }

        // Force assign the admin role to the user
        $user->syncRoles(['admin']);
        $this->command->info('Admin role synced to user.');
    }
}
