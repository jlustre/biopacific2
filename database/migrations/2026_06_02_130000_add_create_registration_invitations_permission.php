<?php

use App\Support\Rbac\Permissions as RbacPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => RbacPermissions::CREATE_REGISTRATION_INVITATIONS,
        ]);

        foreach (['super-admin', 'admin', 'rdhr', 'facility-admin', 'facility-dsd'] as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionName = RbacPermissions::CREATE_REGISTRATION_INVITATIONS;

        foreach (Role::all() as $role) {
            $role->revokePermissionTo($permissionName);
        }

        Permission::where('name', $permissionName)->delete();
    }
};
