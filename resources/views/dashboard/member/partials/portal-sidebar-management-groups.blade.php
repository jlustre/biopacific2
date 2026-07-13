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
            Reports Management
        </a>
        <a href="{{ route('admin.scheduled-report-runs.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.scheduled-report-runs.*') }}">
            Scheduled Report Runs
        </a>
        @endif

        <a href="{{ $hrPortalUrl }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $hrActive ? 'member-portal-nav-sub-active font-semibold text-white' : 'text-teal-100' }}">
            Employee Management
        </a>

        @if(\App\Support\MemberPortalLayout::userCanAccessDocumentsManagement($authUser))
        <a href="{{ route('admin.upload-types.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.upload-types.*', 'admin.checklist-items.*']) }}">
            Documents Management
        </a>
        @endif

        <a href="{{ route('admin.facility.trainings') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.facility.trainings', 'admin.facility.employees*']) }}">
            Trainings
        </a>

        @if($showAllOpsLinks || ($portalNav === 'facility' && ($authUser->can(\App\Support\Rbac\Permissions::VIEW_POSITIONS) || $authUser->hasRole(['facility-admin', 'facility-dsd', 'don']))))
        <a href="{{ route('admin.positions.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">
            Positions Management
        </a>
        @endif
    </div>

    @if(\App\Support\MemberPortalLayout::userCanAccessWebContentsNav($authUser))
        @include('dashboard.member.partials.portal-sidebar-web-communications-groups')
    @endif
</div>
@endif
