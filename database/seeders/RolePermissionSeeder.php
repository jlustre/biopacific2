<?php

namespace Database\Seeders;

use App\Support\Rbac\Permissions as RbacPermissions;
use Database\Seeders\Support\RolePermissionsSeedData;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Rename legacy permission slug to match current RBAC naming.
        $legacyCommunications = Permission::query()->where('name', 'view communications')->first();
        if ($legacyCommunications) {
            $targetName = RbacPermissions::VIEW_WEB_COMMUNICATIONS;
            $existingTarget = Permission::query()->where('name', $targetName)->first();
            if ($existingTarget) {
                \Illuminate\Support\Facades\DB::table(config('permission.table_names.role_has_permissions'))
                    ->where('permission_id', $legacyCommunications->id)
                    ->update(['permission_id' => $existingTarget->id]);
                $legacyCommunications->delete();
            } else {
                $legacyCommunications->update(['name' => $targetName]);
            }
        }

        foreach (RbacPermissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roleDefinitions = RolePermissionsSeedData::all();

        if ($roleDefinitions === []) {
            $this->command?->warn('RolePermissionSeeder: role_permissions.json is missing or empty. Run Role Management → Update Seeder after configuring roles.');

            return;
        }

        foreach ($roleDefinitions as $definition) {
            $roleName = $definition['name'] ?? '';
            if ($roleName === '') {
                continue;
            }

            $role = Role::firstOrCreate(['name' => $roleName]);

            if (! empty($definition['all_permissions'])) {
                $role->syncPermissions(Permission::all()->pluck('name')->toArray());

                continue;
            }

            $role->syncPermissions($definition['permissions'] ?? []);
        }
    }
}
