<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeederExporter
{
    public function jsonPath(): string
    {
        return database_path('seeders/data/role_permissions.json');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $roles = $this->collectRoleData();
        $json = json_encode(
            $roles,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException('Failed to encode role permissions as JSON.');
        }

        $path = $this->jsonPath();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Could not create seeders data directory.');
        }

        $written = file_put_contents($path, $json);

        if ($written === false) {
            throw new \RuntimeException('Could not write role permissions JSON file.');
        }

        return ['count' => count($roles), 'path' => $path];
    }

    public function shouldSyncFromRequest(Request $request): bool
    {
        return $request->boolean('update_seeder');
    }

    /**
     * @return array{synced: bool, count?: int, path?: string, error?: string}
     */
    public function syncFromRequest(Request $request): array
    {
        if (! $this->shouldSyncFromRequest($request)) {
            return ['synced' => false];
        }

        return $this->sync();
    }

    /**
     * @return array{synced: bool, count?: int, path?: string, error?: string}
     */
    public function sync(): array
    {
        try {
            $result = $this->writeSeederFile();

            return [
                'synced' => true,
                'count' => $result['count'],
                'path' => 'database/seeders/data/role_permissions.json',
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'synced' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function seederSyncMessage(array $sync): ?string
    {
        if (! empty($sync['synced'])) {
            return ' Seeder data updated with '.$sync['count']
                .' role(s). Commit database/seeders/data/role_permissions.json so migrate:fresh --seed restores permissions.';
        }

        if (! empty($sync['error'])) {
            return ' Seeder update failed: '.$sync['error'];
        }

        return null;
    }

    /**
     * @return list<array{name: string, all_permissions?: bool, permissions?: list<string>}>
     */
    public function collectRoleData(): array
    {
        $priority = User::roleDisplayPriority();
        $allPermissionCount = Permission::query()->count();

        return Role::query()
            ->with('permissions')
            ->get()
            ->sortBy(function (Role $role) use ($priority) {
                $index = array_search($role->name, $priority, true);

                return $index === false ? 999 : $index;
            })
            ->values()
            ->map(function (Role $role) use ($allPermissionCount) {
                $permissionNames = $role->permissions
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all();

                if ($allPermissionCount > 0 && count($permissionNames) >= $allPermissionCount) {
                    return [
                        'name' => $role->name,
                        'all_permissions' => true,
                    ];
                }

                return [
                    'name' => $role->name,
                    'permissions' => $permissionNames,
                ];
            })
            ->all();
    }
}
