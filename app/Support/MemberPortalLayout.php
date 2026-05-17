<?php

namespace App\Support;

use App\Services\MemberPortalContextResolver;
use Illuminate\Http\Request;

class MemberPortalLayout
{
    public static function shouldUseForCurrentRequest(?Request $request = null): bool
    {
        $request = $request ?? request();
        $user = $request->user();

        if (!$user || !$user->hasRole(['hrrd', 'facility-admin', 'facility-dsd'])) {
            return false;
        }

        return $request->routeIs(config('member-portal.facility_management_route_patterns', []));
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

        return [
            'portalActive' => self::activeIdForRoute($routeName),
            'portalTitle' => $title . ' | Bio Pacific',
            'portalEyebrow' => 'Facility Management',
            'portalPageTitle' => $title,
            'showPortalSearch' => false,
            'showPortalNotifications' => false,
            'showPortalFooter' => false,
        ];
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
