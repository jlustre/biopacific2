<?php

namespace Database\Seeders\Support;

class RolePermissionsSeedData
{
    private static ?array $items = null;

    /**
     * @return list<array{name: string, all_permissions?: bool, permissions?: list<string>}>
     */
    public static function all(): array
    {
        if (self::$items !== null) {
            return self::$items;
        }

        $path = database_path('seeders/data/role_permissions.json');

        if (! is_file($path)) {
            self::$items = [];

            return self::$items;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        self::$items = is_array($decoded) ? self::normalize($decoded) : [];

        return self::$items;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{name: string, all_permissions?: bool, permissions?: list<string>}>
     */
    private static function normalize(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $entry = ['name' => $name];

            if (! empty($item['all_permissions'])) {
                $entry['all_permissions'] = true;
            } else {
                $permissions = $item['permissions'] ?? [];
                $entry['permissions'] = is_array($permissions)
                    ? array_values(array_unique(array_filter(array_map(
                        fn ($permission) => trim((string) $permission),
                        $permissions
                    ))))
                    : [];
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }
}
