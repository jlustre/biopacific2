<?php

namespace App\Support;

use App\Services\MemberPortalContextResolver;
use App\Services\RoleMemberDashboardService;
use App\Support\Rbac\Permissions;
use Illuminate\Http\Request;

class MemberPortalLayout
{
    public static function userCanAccessFacilityDashboard($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && app(RoleMemberDashboardService::class)->canAccessFacilityDashboard($user);
    }

    /**
     * Dashboard sidebar links for the current user (filters Facility Dashboard to leadership).
     *
     * @return list<array<string, mixed>>
     */
    public static function dashboardNavItemsForUser($user = null): array
    {
        $user = $user ?? auth()->user();
        $navMode = self::sidebarModeForCurrentRequest();

        $items = match ($navMode) {
            'facility' => config('member-portal.facility_dashboard_nav', []),
            'corporate' => config('member-portal.corporate_dashboard_nav', []),
            'employee' => self::employeeDashboardNavItems($user),
            default => [],
        };

        $canFacilityDashboard = self::userCanAccessFacilityDashboard($user);
        $hasFacilityLink = collect($items)->contains(fn (array $item) => ($item['id'] ?? '') === 'facility-dashboard');

        if ($canFacilityDashboard && ! $hasFacilityLink) {
            $facilityItem = [
                'id' => 'facility-dashboard',
                'route' => 'member.facility.dashboard',
                'route_is' => ['member.facility.dashboard', 'admin.facility.dashboard'],
                'icon' => '🏢',
                'label' => 'Facility Dashboard',
            ];
            array_splice($items, 1, 0, [$facilityItem]);
        }

        if (! $canFacilityDashboard) {
            $items = collect($items)
                ->filter(fn (array $item) => ($item['id'] ?? '') !== 'facility-dashboard')
                ->values()
                ->all();
        }

        return $items;
    }

    /**
     * Purpose-grouped sidebar for all portal modes.
     *
     * @return list<array{id: string, label: string, icon: string, open: bool, items: list<array<string, mixed>>}>
     */
    public static function purposeGroupsForUser($user = null): array
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return [];
        }

        $groups = [];

        foreach (config('member-portal.nav_purpose_groups', []) as $group) {
            if (! self::userPassesNavGate($user, $group['gate'] ?? 'everyone')) {
                continue;
            }

            $items = [];
            foreach ($group['items'] ?? [] as $item) {
                if (! self::userPassesNavGate($user, $item['gate'] ?? 'everyone')) {
                    continue;
                }

                $route = $item['route'] ?? null;
                if (! $route || ! \Illuminate\Support\Facades\Route::has($route)) {
                    continue;
                }

                $items[] = $item;
            }

            $items = self::filterEmploymentSelfServiceNav($items, $user);

            // Avoid duplicate Leadership under Company when Facility Leadership is already shown.
            if (($group['id'] ?? '') === 'company') {
                $facilityGroup = collect($groups)->firstWhere('id', 'facility');
                $facilityShowsLeadership = $facilityGroup && collect($facilityGroup['items'] ?? [])
                    ->contains(fn (array $item) => ($item['id'] ?? '') === 'facility-leadership');

                if ($facilityShowsLeadership) {
                    $items = collect($items)
                        ->reject(fn (array $item) => ($item['id'] ?? '') === 'company-leadership')
                        ->values()
                        ->all();
                }
            }

            // Prefer company news manage route for web managers.
            if (($group['id'] ?? '') === 'company') {
                $items = collect($items)->map(function (array $item) use ($user) {
                    if (($item['id'] ?? '') === 'company-news' && self::userCanAccessWebContentsNav($user) && \Illuminate\Support\Facades\Route::has('admin.news.index')) {
                        $item['route'] = 'admin.news.index';
                        $item['route_is'] = ['admin.news.*', 'member.news-events.*'];
                    }

                    return $item;
                })->all();
            }

            if ($items === []) {
                continue;
            }

            $groups[] = [
                'id' => $group['id'],
                'label' => $group['label'],
                'icon' => $group['icon'] ?? '•',
                'open' => collect($items)->contains(fn (array $item) => self::navItemMatchesRequest($item)),
                'items' => $items,
            ];
        }

        return $groups;
    }

    public static function userPassesNavGate($user, string $gate): bool
    {
        return match ($gate) {
            'everyone' => true,
            'system_admin' => self::userIsSystemAdmin($user),
            'web_contents' => self::userCanAccessWebContentsNav($user),
            'documents_mgmt' => self::userCanAccessDocumentsManagement($user),
            'facility_dashboard' => self::userCanAccessFacilityDashboard($user),
            'facility_ops' => self::userCanAccessFacilityOpsNav($user),
            'facility_member' => self::userCanAccessFacilityMemberNav($user),
            'hr_portal' => self::userCanAccessHrPortalNav($user),
            'training_mgmt' => self::userCanAccessTrainingManagementNav($user),
            'reports' => self::userCanAccessReportsNav($user),
            'positions' => self::userCanAccessPositionsNav($user),
            'invite_mgmt' => self::userCanAccessInviteManagementNav($user),
            default => true,
        };
    }

    public static function userCanAccessFacilityOpsNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userIsSystemAdmin($user) || self::userCanAccessHrPortalNav($user) || self::userCanAccessFacilityDashboard($user)) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole(array_values(array_unique(array_merge(
            self::facilityManagerRoles(),
            self::corporateRoles(),
            self::documentsManagementRoles(),
            self::webContentsNavRoles()
        ))));
    }

    /**
     * Facility-linked employees (any position) plus facility management.
     * Used for read-oriented Facility group items such as News/Events and Galleries.
     */
    public static function userCanAccessFacilityMemberNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userCanAccessFacilityOpsNav($user)) {
            return true;
        }

        if ((int) ($user->facility_id ?? 0) > 0) {
            return true;
        }

        if (SelectedFacility::id() && SelectedFacility::userCanChooseFacility($user)) {
            return true;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        return (int) ($employee?->currentAssignment?->facility_id ?? 0) > 0;
    }

    public static function userCanAccessHrPortalNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userIsSystemAdmin($user)) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can(Permissions::ACCESS_HR_PORTAL)) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole(array_merge(
            self::facilityManagerRoles(),
            self::corporateRoles()
        ));
    }

    public static function userCanAccessTrainingManagementNav($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don']);
    }

    public static function userCanAccessReportsNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userIsSystemAdmin($user)) {
            return true;
        }

        return method_exists($user, 'hasRole') && $user->hasRole(array_merge(
            self::corporateRoles(),
            ['facility-admin', 'facility-dsd']
        ));
    }

    public static function userCanAccessPositionsNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userIsSystemAdmin($user)) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can(Permissions::VIEW_POSITIONS)) {
            return true;
        }

        return method_exists($user, 'hasRole')
            && $user->hasRole(['facility-admin', 'facility-dsd', 'don', 'rdhr']);
    }

    public static function userCanAccessInviteManagementNav($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (self::userIsSystemAdmin($user)) {
            return true;
        }

        return method_exists($user, 'can')
            && $user->can(Permissions::CREATE_REGISTRATION_INVITATIONS);
    }

    /**
     * Whether a purpose-nav item should appear active for the current request.
     *
     * @param  array<string, mixed>  $item
     */
    public static function navItemMatchesRequest(array $item, ?string $activeId = null): bool
    {
        if ($activeId !== null && $activeId !== '' && $activeId === ($item['id'] ?? null)) {
            return true;
        }

        $patterns = $item['route_is'] ?? $item['route'] ?? null;
        if ($patterns && request()->routeIs($patterns) && self::navQueryConstraintsMatch($item)) {
            return true;
        }

        foreach ($item['active_also'] ?? [] as $also) {
            $alsoPatterns = $also['route_is'] ?? null;
            if (! $alsoPatterns || ! request()->routeIs($alsoPatterns)) {
                continue;
            }

            if (! self::navQueryConstraintsMatch($also)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $constraints
     */
    protected static function navQueryConstraintsMatch(array $constraints): bool
    {
        foreach ($constraints['query'] ?? [] as $key => $value) {
            $actual = request()->query($key);
            if (is_array($value)) {
                if (! in_array((string) $actual, array_map('strval', $value), true)) {
                    return false;
                }
            } elseif ((string) $actual !== (string) $value) {
                return false;
            }
        }

        foreach ($constraints['unless_query'] ?? [] as $key => $value) {
            $actual = request()->query($key);
            if (is_array($value)) {
                if (in_array((string) $actual, array_map('strval', $value), true)) {
                    return false;
                }
            } elseif ((string) $actual === (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Keep personal “My …” links for every employee; show Employment or Pre-Employment by status.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public static function filterEmploymentSelfServiceNav(array $items, $user = null): array
    {
        $user = $user ?? auth()->user();
        $isEmployee = $user && method_exists($user, 'resolvedBpEmployee') && $user->resolvedBpEmployee() !== null;

        return collect($items)
            ->filter(function (array $item) use ($isEmployee) {
                $id = $item['id'] ?? '';

                if ($isEmployee && $id === 'pre-employment') {
                    return false;
                }

                if (! $isEmployee && $id === 'employment') {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

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

    public static function webContentsNavRoles(): array
    {
        $configured = config('member-portal.web_contents_manager_roles', 'admin|super-admin|rdhr|facility-admin|facility-dsd');

        if (is_string($configured)) {
            return array_values(array_filter(explode('|', $configured)));
        }

        return is_array($configured) ? $configured : [];
    }

    public static function userCanAccessWebContentsNav($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::webContentsNavRoles());
    }

    /**
     * @return list<string>
     */
    public static function documentsManagementRoles(): array
    {
        $configured = config('member-portal.documents_management_roles', [
            'admin',
            'super-admin',
            'rdhr',
            'facility-admin',
            'facility-dsd',
            'don',
        ]);

        return is_array($configured) ? array_values($configured) : [];
    }

    public static function userCanAccessDocumentsManagement($user = null): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'hasRole')
            && $user->hasRole(self::documentsManagementRoles());
    }

    public static function documentsManagementRoutePatterns(): array
    {
        return [
            'admin.upload-types.*',
            'admin.checklist-items.*',
            'admin.position-document-requirements.*',
            'admin.training-items.*',
        ];
    }

    public static function userHasFullNavAccess($user = null): bool
    {
        return self::userIsSystemAdmin($user);
    }

    public static function hrPortalRouteForUser($user = null): string
    {
        $routeKey = SelectedFacility::routeKey();

        if ($routeKey) {
            return route('user.hr-portal', ['facility' => $routeKey]);
        }

        return route('user.hr-portal');
    }

    public static function facilityDashboardRouteForUser($user = null): string
    {
        $routeKey = SelectedFacility::routeKey();

        if ($routeKey) {
            return route('member.facility.dashboard', ['facility' => $routeKey]);
        }

        return route('member.facility.dashboard');
    }

    public static function routeWithSelectedFacility(string $name, array $parameters = [], bool $absolute = true): string
    {
        return SelectedFacility::route($name, $parameters, $absolute);
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

    public static function hrPortalRoutePatterns(): array
    {
        return ['hr-portal.*', 'admin.hr-portal.*', 'user.hr-portal'];
    }

    public static function sidebarModeForCurrentRequest(?Request $request = null): string
    {
        $request = $request ?? request();
        $user = $request->user();

        if (!$user) {
            return 'employee';
        }

        if ($request->routeIs(self::hrPortalRoutePatterns()) && $user->can(Permissions::ACCESS_HR_PORTAL)) {
            if (self::userIsSystemAdmin($user) || $user->hasRole('admin')) {
                return 'admin';
            }

            if (self::userIsCorporateManager($user)) {
                return 'corporate';
            }

            if (self::userIsFacilityManager($user)) {
                return 'facility';
            }
        }

        if (self::userIsSystemAdmin($user) && $request->routeIs(self::adminRoutePatterns())) {
            return 'admin';
        }

        $routes = array_values(array_unique(array_merge(
            self::facilityManagerRoutePatterns(),
            self::documentsManagementRoutePatterns(),
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
            config('member-portal.facility_management_web_route_patterns', []),
            config('member-portal.facility_management_comm_route_patterns', []),
        )));
    }

    public static function adminRoutePatterns(): array
    {
        return array_values(array_unique(array_merge(
            config('member-portal.admin_route_patterns', []),
            config('member-portal.facility_management_route_patterns', []),
            config('member-portal.facility_management_web_route_patterns', []),
            config('member-portal.facility_management_comm_route_patterns', []),
        )));
    }

    public static function shouldUseForCurrentRequest(?Request $request = null): bool
    {
        // Legacy admin sidebar/topbar is retired; authenticated app chrome is always the member portal.
        return ($request ?? request())->user() !== null;
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

        return array_merge($resolver->forUser($user), self::pageMeta(), [
            'selectedFacility' => SelectedFacility::model(),
            'selectedFacilityId' => SelectedFacility::id(),
            'selectedFacilityName' => SelectedFacility::name(),
            'canChooseFacility' => SelectedFacility::userCanChooseFacility($user),
        ]);
    }

    public static function pageMeta(): array
    {
        $routeName = request()->route()?->getName() ?? '';

        if (request()->routeIs('member.facilities.websites*')) {
            $title = 'Bio-Pacific Websites';

            return [
                'portalNav' => self::navModeForCurrentRequest(),
                'portalActive' => self::activeIdForRoute($routeName),
                'portalTitle' => $title . ' | Bio Pacific',
                'portalEyebrow' => $title,
                'portalPageTitle' => $title,
                'portalSubtitle' => self::navModeForCurrentRequest() === 'employee'
                    ? 'HR Employee Portal'
                    : (self::navModeForCurrentRequest() === 'corporate' ? 'Corporate Management' : 'Facility Portal'),
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
                'showPortalFooter' => false,
            ];
        }

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

            if (request()->routeIs(['member.checklists', 'member.checklists.*'])) {
                return 'checklists';
            }

            if (request()->routeIs(['admin.employees.edit', 'admin.employees.update', 'admin.employees.show'])) {
                return match ((string) request('checklist_tab')) {
                    'partH', 'partG', 'partF' => 'checklists',
                    default => request('tab') === 'documents' ? 'documents' : 'checklists',
                };
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

    /**
     * Employee portal dashboard nav with employment vs pre-employment link resolved per user.
     *
     * @return list<array<string, mixed>>
     */
    public static function employeeDashboardNavItems($user = null): array
    {
        $user = $user ?? auth()->user();
        $items = config('member-portal.employee_dashboard_nav', []);
        $isEmployee = $user && method_exists($user, 'resolvedBpEmployee') && $user->resolvedBpEmployee() !== null;

        return collect($items)
            ->filter(function (array $item) use ($isEmployee) {
                $id = $item['id'] ?? '';

                if ($isEmployee && $id === 'pre-employment') {
                    return false;
                }

                if (!$isEmployee && $id === 'employment') {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }
}
