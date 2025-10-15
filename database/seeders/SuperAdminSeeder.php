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

        // Check if user already exists
        $existingUser = User::where('email', 'admin@biopacific.com')->first();

        if ($existingUser) {
            $user = $existingUser;
            $this->command->info('User already exists, updating role...');
        } else {
            // Create the super admin user
            $user = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@biopacific.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]);
            $this->command->info('Super admin user created successfully.');
        }

        // Force assign the admin role to the user
        $user->syncRoles(['admin']);
        $this->command->info('Admin role synced to user.');
    }
}
