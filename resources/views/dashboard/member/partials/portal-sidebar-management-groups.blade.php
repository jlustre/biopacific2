@php
    $active = $active ?? 'dashboard';
    $authUser = auth()->user();
    $portalNav = $portalNav ?? 'facility';
    $showAllOpsLinks = $showAllOpsLinks ?? false;
    $sectionLabel = $sectionLabel ?? match ($portalNav) {
        'corporate' => 'Corporate Management',
        default => 'Facility Portal',
    };
    $managementRoutePatterns = config('member-portal.facility_management_route_patterns', []);
    $webRoutePatterns = config('member-portal.facility_management_web_route_patterns', []);
    $commRoutePatterns = config('member-portal.facility_management_comm_route_patterns', []);
    $managementOpen = request()->routeIs($managementRoutePatterns);
    $webSectionOpen = request()->routeIs($webRoutePatterns);
    $commSectionOpen = request()->routeIs($commRoutePatterns);
    $hrActive = $active === 'hr-portal' || $active === 'facility-hr-portal'
         || request()->routeIs(['user.hr-portal', 'hr-portal.index', 'admin.facility.*', 'admin.employees.*', 'admin.facility.employees*', 'admin.facility.hiring', 'admin.facility.job_openings*', 'admin.facility.reports*', 'admin.facility.documents*', 'admin.facility.uploads*', 'admin.facility.pre-employment*', 'admin.reports.*', 'admin.scheduled-reports.*', 'admin.scheduled-report-runs.*', 'admin.hr-portal.*']);
    $subLinkClass = fn (array|string $patterns) => request()->routeIs($patterns)
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';
    $selectedFacilityId = $selectedFacilityId ?? \App\Support\SelectedFacility::id();
    $selectedFacilityRouteKey = \App\Support\SelectedFacility::routeKey();
    $careersUrl = $selectedFacilityId
        ? route('admin.facilities.webcontents.careers', ['facility_id' => $selectedFacilityId])
        : (($authUser && $authUser->facility_id)
            ? route('admin.facilities.webcontents.careers', ['facility_id' => $authUser->facility_id])
            : route('admin.facilities.webcontents.careers'));
    $hrPortalUrl = \App\Support\MemberPortalLayout::hrPortalRouteForUser($authUser);
@endphp

@if($authUser)
<div class="my-3 space-y-1 border-t border-white/10 pt-3"
     x-data="{ managementOpen: {{ $managementOpen ? 'true' : 'false' }}, webOpen: {{ $webSectionOpen ? 'true' : 'false' }}, commOpen: {{ $commSectionOpen ? 'true' : 'false' }} }">
    <button type="button"
            @click="managementOpen = !managementOpen"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $managementOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
        <span class="flex items-center gap-3"><span>🏢</span><span>{{ $sectionLabel }}</span></span>
        <span class="text-xs" x-text="managementOpen ? '▲' : '▼'"></span>
    </button>

    <div x-show="managementOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
        @if($portalNav === 'corporate' || $showAllOpsLinks)
        <a href="{{ route('admin.positions.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">
            Positions Management
        </a>
        <a href="{{ route('admin.reports.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.reports.*', 'admin.scheduled-reports.*']) }}">
            Reports
        </a>
        @endif

        <a href="{{ $hrPortalUrl }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $hrActive ? 'member-portal-nav-sub-active font-semibold text-white' : 'text-teal-100' }}">
            Employee Management
        </a>

        @php
            $leadershipFacility = $selectedFacilityId
                ? \App\Models\Facility::find($selectedFacilityId)
                : ($authUser->facility_id ? \App\Models\Facility::find($authUser->facility_id) : null);
            $leadershipHref = ($leadershipFacility && ! $authUser->hasRole(['admin', 'super-admin', 'rdhr']))
                ? route('admin.facility.leadership.edit', ['facility' => $leadershipFacility->getRouteKey()])
                : ($selectedFacilityRouteKey
                    ? route('admin.facility.leadership.edit', ['facility' => $selectedFacilityRouteKey])
                    : route('admin.facilities.leadership.index'));
        @endphp
        @if($showAllOpsLinks || (in_array($portalNav, ['facility', 'corporate'], true) && \Illuminate\Support\Facades\Route::has('admin.facility.leadership.edit') && ($authUser->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']) || $authUser->can(\App\Support\Rbac\Permissions::ACCESS_HR_PORTAL))))
        <a href="{{ $leadershipHref }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ request()->routeIs(['admin.facility.leadership*', 'admin.facilities.leadership*']) ? 'member-portal-nav-sub-active font-semibold text-white' : 'text-teal-100' }}">
            Facility Leadership
        </a>
        @endif

        <a href="{{ route('admin.upload-types.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.upload-types.*') }}">
            Documents Management
        </a>

        <a href="{{ route('admin.training-management.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.training-management.*') }}">
            Training Management
        </a>

        @if($showAllOpsLinks || ($portalNav === 'facility' && ($authUser->can(\App\Support\Rbac\Permissions::VIEW_POSITIONS) || $authUser->hasRole(['facility-admin', 'facility-dsd', 'don']))))
        <a href="{{ route('admin.positions.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">
            Positions Management
        </a>
        @endif

        @include('dashboard.member.partials.portal-sidebar-web-communications-groups')
    </div>
</div>
@endif
