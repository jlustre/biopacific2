<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $create = Permission::firstOrCreate(['name' => config('import-mapping.permissions.create')]);
        $use = Permission::firstOrCreate(['name' => config('import-mapping.permissions.use')]);

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo([$create, $use]);

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($use);
        }

        foreach (['facility-admin', 'facility-dsd', 'rdhr', 'facility-editor'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($use);
            }
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $names = array_values(config('import-mapping.permissions'));

        foreach (Role::all() as $role) {
            $role->revokePermissionTo($names);
        }

        Permission::whereIn('name', $names)->delete();
    }
};
