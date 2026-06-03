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
        return config('member-portal.facility_roles', config('member-portal.facility_manager_roles', ['facility-admin', 'facility-dsd', 'facility-ssd', 'don']));
    }

    public static function userIsFacilityManager($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::facilityManagerRoles());
    }

    public static function corporateRoles(): array
    {
        return config('member-portal.corporate_roles', ['rdhr']);
    }

    public static function userIsCorporateManager($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::corporateRoles());
    }

    public static function sidebarModeForCurrentRequest(?Request $request = null): string
    {
        $request = $request ?? request();
        $user = $request->user();

        if (!$user) {
            return 'employee';
        }

        if (self::userIsSystemAdmin($user) && $request->routeIs(self::adminRoutePatterns())) {
            return 'admin';
        }

        $routes = array_values(array_unique(array_merge(
            self::facilityManagerRoutePatterns(),
            ['dashboard.index', 'user.dashboard', 'member.*', 'settings.*', 'admin.positions.*', 'admin.training-management.*']
        )));

        if (self::userIsCorporateManager($user) && self::requestMatchesAnyRoutePattern($request, $routes)) {
            return 'corporate';
        }

        if (self::userIsFacilityManager($user) && self::requestMatchesAnyRoutePattern($request, $routes)) {
            return 'facility';
        }

        if (self::userIsSystemAdmin($user) && self::requestMatchesAnyRoutePattern($request, ['dashboard.index', 'user.dashboard', 'member.*', 'settings.*'])) {
            return 'admin';
        }

        return 'employee';
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
        return self::sidebarModeForCurrentRequest($request) !== 'employee';
    }

    public static function navModeForCurrentRequest(?Request $request = null): string
    {
        return self::sidebarModeForCurrentRequest($request);
    }

    public static function variablesForView(): array
    {
        $user = auth()->user()?->fresh();
        if (!$user) {
            return [];
        }

        $resolver = new MemberPortalContextResolver();

        return array_merge($resolver->forUser($user), self::pageMeta());
    }

    public static function pageMeta(): array
    {
        $routeName = request()->route()?->getName() ?? '';

        if (self::isPersonalPortalRoute()) {
            $pageTitle = self::personalPortalPageTitle() ?? 'Personal';

            return [
                'portalNav' => self::navModeForCurrentRequest(),
                'portalActive' => self::activeIdForRoute($routeName),
                'portalTitle' => $pageTitle . ' | Bio Pacific',
                'portalEyebrow' => 'Personal Portal',
                'portalPageTitle' => $pageTitle,
                'portalSubtitle' => 'Personal Portal',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
                'showPortalFooter' => false,
            ];
        }

        $title = self::titleForRoute($routeName);

        if (in_array($routeName, ['admin.facility.dashboard', 'member.facility.dashboard', 'user.hr-portal'], true)) {
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

        if ($navMode === 'corporate') {
            return [
                'portalNav' => 'corporate',
                'portalActive' => self::activeIdForRoute($routeName),
                'portalTitle' => $title . ' | Bio Pacific',
                'portalEyebrow' => 'Corporate Management',
                'portalPageTitle' => $title,
                'portalSubtitle' => 'Corporate Management',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
                'showPortalFooter' => false,
            ];
        }

        if ($navMode === 'facility') {
            return [
                'portalNav' => 'facility',
                'portalActive' => self::activeIdForRoute($routeName),
                'portalTitle' => $title . ' | Bio Pacific',
                'portalEyebrow' => 'Facility Portal',
                'portalPageTitle' => $title,
                'portalSubtitle' => 'Facility Portal',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
                'showPortalFooter' => false,
            ];
        }

        return [
            'portalNav' => 'employee',
            'portalActive' => self::activeIdForRoute($routeName),
            'portalTitle' => $title . ' | Bio Pacific',
            'portalEyebrow' => 'Employee Portal',
            'portalPageTitle' => $title,
            'portalSubtitle' => 'HR Employee Portal',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
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

    public static function isPersonalPortalRoute(?Request $request = null): bool
    {
        $request = $request ?? request();

        foreach (config('member-portal.personal_portal_route_patterns', []) as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function personalPortalPageTitle(?Request $request = null): ?string
    {
        $request = $request ?? request();

        foreach (config('member-portal.personal_portal_nav', []) as $item) {
            $title = self::personalPortalNavItemTitle($item, $request);

            if ($title !== null) {
                return $title;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected static function personalPortalNavItemTitle(array $item, Request $request): ?string
    {
        $patterns = $item['route_is'] ?? $item['route'] ?? null;

        if ($patterns === null) {
            return null;
        }

        foreach ((array) $patterns as $pattern) {
            if ($request->routeIs($pattern)) {
                return $item['label'] ?? null;
            }
        }

        foreach ($item['children'] ?? [] as $child) {
            $title = self::personalPortalNavItemTitle($child, $request);

            if ($title !== null) {
                return $title;
            }
        }

        return null;
    }

    protected static function titleForRoute(string $routeName): string
    {
        if (request()->routeIs('admin.positions.*')) {
            return 'Positions Management';
        }

        $titles = config('member-portal.facility_management_titles', []);

        foreach ($titles as $pattern => $label) {
            if (request()->routeIs($pattern)) {
                return $label;
            }
        }

        if (request()->routeIs('admin.positions.*')) {
            return 'Positions Management';
        }

        return 'Facility Portal';
    }

    protected static function activeIdForRoute(string $routeName): string
    {
        $sidebarMode = self::sidebarModeForCurrentRequest();

        $map = match ($sidebarMode) {
            'admin' => config('member-portal.admin_active_map', []),
            'corporate' => array_merge(
                config('member-portal.corporate_active_map', []),
                config('member-portal.facility_management_active_map', []),
            ),
            'facility' => array_merge(
                config('member-portal.facility_active_map', []),
                config('member-portal.facility_management_active_map', []),
            ),
            'employee' => config('member-portal.employee_active_map', []),
            default => [],
        };

        foreach ($map as $pattern => $id) {
            if (request()->routeIs($pattern)) {
                return $id;
            }
        }

        if ($sidebarMode === 'employee') {
            if (request()->routeIs('dashboard.index')) {
                return 'dashboard';
            }

            if (request()->routeIs('settings.profile')) {
                return 'profile';
            }

            if (request()->routeIs('member.documents')) {
                return 'documents';
            }

            if (request()->routeIs('member.certifications')) {
                return 'certifications';
            }

            if (request()->routeIs('member.trainings')) {
                return 'trainings';
            }

            if (request()->routeIs('user.hr-portal')) {
                return 'hr-portal';
            }

            if (request()->routeIs('admin.facility.dashboard')) {
                return 'facility-hr-portal';
            }
        }

        if (request()->routeIs('admin.positions.*')) {
            return 'positions';
        }

        return 'dashboard';
    }

    protected static function requestMatchesAnyRoutePattern(Request $request, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
