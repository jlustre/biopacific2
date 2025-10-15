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
        // Create permissions
        Permission::create(['name' => 'view facilities']);
        Permission::create(['name' => 'create facilities']);
        Permission::create(['name' => 'edit facilities']);
        Permission::create(['name' => 'delete facilities']);
        Permission::create(['name' => 'manage users']);

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $editor = Role::create(['name' => 'editor']);
        $viewer = Role::create(['name' => 'viewer']);

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo(['view facilities', 'create facilities', 'edit facilities']);
        $editor->givePermissionTo(['view facilities', 'create facilities', 'edit facilities']);
        $viewer->givePermissionTo(['view facilities']);
    }
}
