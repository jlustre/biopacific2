<?php

namespace App\Support;

use App\Services\MemberPortalContextResolver;
use Illuminate\Http\Request;

class MemberPortalLayout
{
    public static function systemAdminRoles(): array
    {
        return config('member-portal.system_admin_roles', ['admin', 'super-admin']);
    }

    public static function userIsSystemAdmin($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::systemAdminRoles());
    }

    public static function facilityManagerRoles(): array
    {
        return config('member-portal.facility_manager_roles', ['rdhr', 'facility-admin', 'facility-dsd']);
    }

    public static function userIsFacilityManager($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::facilityManagerRoles());
    }

    public static function facilityManagerRoutePatterns(): array
    {
        return array_values(array_unique(array_merge(
            config('member-portal.facility_manager_global_route_patterns', []),
            config('member-portal.facility_management_route_patterns', []),
        )));
    }

    public static function adminRoutePatterns(): array
    {
        return array_values(array_unique(array_merge(
            config('member-portal.admin_route_patterns', []),
            config('member-portal.facility_management_route_patterns', []),
        )));
    }

    public static function shouldUseForCurrentRequest(?Request $request = null): bool
    {
        $request = $request ?? request();
        $user = $request->user();

        if (!$user) {
            return false;
        }

        if (self::userIsSystemAdmin($user) && $request->routeIs(self::adminRoutePatterns())) {
            return true;
        }

        if (!self::userIsFacilityManager($user)) {
            return false;
        }

        return $request->routeIs(self::facilityManagerRoutePatterns());
    }

    public static function navModeForCurrentRequest(?Request $request = null): string
    {
        $request = $request ?? request();
        $user = $request->user();

        if (self::userIsSystemAdmin($user) && $request->routeIs(self::adminRoutePatterns())) {
            return 'admin';
        }

        if (self::userIsFacilityManager($user)
            && $request->routeIs(self::facilityManagerRoutePatterns())) {
            return 'facility';
        }

        return 'employee';
    }

    public static function variablesForView(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $resolver = new MemberPortalContextResolver();

        return array_merge($resolver->forUser($user), self::pageMeta());
    }

    public static function pageMeta(): array
    {
        $routeName = request()->route()?->getName() ?? '';
        $title = self::titleForRoute($routeName);

        if ($routeName === 'admin.facility.dashboard') {
            $facility = request()->route('facility');
            if ($facility instanceof \App\Models\Facility) {
                $title = $facility->name;
            }
        }

        $navMode = self::navModeForCurrentRequest();

        if ($navMode === 'admin') {
            return [
                'portalNav' => 'admin',
                'portalActive' => self::activeIdForAdminRoute($routeName),
                'portalTitle' => self::adminTitleForRoute($routeName) . ' | Bio Pacific',
                'portalEyebrow' => 'System Administration',
                'portalPageTitle' => self::adminTitleForRoute($routeName),
                'portalSubtitle' => 'Bio-Pacific Administration',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
                'showPortalFooter' => false,
            ];
        }

        return [
            'portalNav' => $navMode === 'facility' ? 'facility' : 'employee',
            'portalActive' => self::activeIdForRoute($routeName),
            'portalTitle' => $title . ' | Bio Pacific',
            'portalEyebrow' => $navMode === 'facility' ? 'Facility Management' : 'Employee Portal',
            'portalPageTitle' => $title,
            'portalSubtitle' => $navMode === 'facility' ? 'Facility Management' : 'HR Employee Portal',
            'showPortalSearch' => false,
            'showPortalNotifications' => $navMode === 'employee',
            'showPortalFooter' => false,
        ];
    }

    protected static function adminTitleForRoute(string $routeName): string
    {
        foreach (config('member-portal.facility_management_titles', []) as $pattern => $label) {
            if (request()->routeIs($pattern)) {
                return $label;
            }
        }

        foreach (config('member-portal.admin_titles', []) as $pattern => $label) {
            if (request()->routeIs($pattern)) {
                return $label;
            }
        }

        return 'Admin Dashboard';
    }

    protected static function activeIdForAdminRoute(string $routeName): string
    {
        foreach (config('member-portal.facility_management_active_map', []) as $pattern => $id) {
            if (request()->routeIs($pattern)) {
                return $id;
            }
        }

        foreach (config('member-portal.admin_active_map', []) as $pattern => $id) {
            if (request()->routeIs($pattern)) {
                return $id;
            }
        }

        return 'admin-dashboard';
    }

    protected static function titleForRoute(string $routeName): string
    {
        $titles = config('member-portal.facility_management_titles', []);

        foreach ($titles as $pattern => $label) {
            if (request()->routeIs($pattern)) {
                return $label;
            }
        }

        return 'Facility Management';
    }

    protected static function activeIdForRoute(string $routeName): string
    {
        $map = config('member-portal.facility_management_active_map', []);

        foreach ($map as $pattern => $id) {
            if (request()->routeIs($pattern)) {
                return $id;
            }
        }

        if (request()->routeIs(['user.hr-portal', 'admin.facility.dashboard'])) {
            return 'facility-hr-portal';
        }

        return 'dashboard';
    }
}
