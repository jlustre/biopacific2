<?php

namespace App\Support;

use App\Models\User;

class ImportMappingPresetAccess
{
    public static function permission(string $key): string
    {
        return config("import-mapping.permissions.{$key}");
    }

    public static function canCreate($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user instanceof User) {
            return false;
        }

        $permission = self::permission('create');

        return $user->hasPermissionTo($permission)
            || $user->hasRole('super-admin');
    }

    public static function canUse($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user instanceof User) {
            return false;
        }

        $permission = self::permission('use');

        return $user->hasPermissionTo($permission)
            || $user->hasRole(['super-admin', 'admin', 'facility-admin', 'facility-dsd', 'rdhr', 'facility-editor']);
    }

    public static function restrictedRoleLabel($user = null): ?string
    {
        $user = $user ?? auth()->user();

        if (!$user instanceof User || !method_exists($user, 'hasRole')) {
            return null;
        }

        if ($user->hasRole('facility-dsd')) {
            return 'Facility DSD';
        }

        if ($user->hasRole('facility-admin')) {
            return 'Facility Administrator';
        }

        if ($user->hasRole('rdhr')) {
            return 'RDHR';
        }

        return null;
    }
}
