@php
    $active = $active ?? 'dashboard';
    $extraClass = fn (bool $isActive) => $isActive
        ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white'
        : 'text-teal-100';
    $subLinkClass = fn (array|string $patterns) => request()->routeIs($patterns)
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';
    $rolesOpen = request()->routeIs(['admin.roles.*', 'admin.permissions.*', 'admin.role-assignments.*', 'admin.position-portal-roles.*']);
    $tablesOpen = request()->routeIs([
        'admin.positions.*',
        'admin.upload-types.*',
        'admin.checklist-items.*',
        'admin.departments.*',
        'admin.events.*',
        'admin.email-recipients.*',
        'admin.email-templates.*',
        'admin.import-mapping-presets.*',
        'admin.import-logs.*',
    ]);
    $hipaaOpen = request()->routeIs(['admin.baa-registry.*', 'admin.hipaa-checklist.*']);
    $webRoutePatterns = config('member-portal.facility_management_web_route_patterns', []);
    $commRoutePatterns = config('member-portal.facility_management_comm_route_patterns', []);
    $webSectionOpen = request()->routeIs($webRoutePatterns);
    $commSectionOpen = request()->routeIs($commRoutePatterns);
@endphp

<div class="my-3 space-y-1 border-t border-white/10 pt-3"
     x-data="{ rolesOpen: {{ $rolesOpen ? 'true' : 'false' }}, tablesOpen: {{ $tablesOpen ? 'true' : 'false' }}, hipaaOpen: {{ $hipaaOpen ? 'true' : 'false' }}, webOpen: {{ $webSectionOpen ? 'true' : 'false' }}, commOpen: {{ $commSectionOpen ? 'true' : 'false' }} }">
    <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Admin Management</p>

    <a href="{{ route('admin.arbitration-templates.index') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.arbitration-templates.*')) }}">
        <span>📄</span><span>Arbitration Templates</span>
    </a>

    <a href="{{ route('admin.scheduled-report-runs.index') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.scheduled-report-runs.*')) }}">
        <span>📋</span><span>Scheduled Report Runs</span>
    </a>

    <a href="{{ route('admin.facilities.index') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs(['admin.facilities.index', 'admin.facilities.create', 'admin.facilities.edit', 'admin.facilities.show'])) }}">
        <span>🏢</span><span>Facilities Management</span>
    </a>

    @if(\Illuminate\Support\Facades\Route::has('admin.webmaster.contacts.index'))
    @php
        $openWebmasterIssues = \App\Models\WebmasterContact::query()->where('status', '!=', 'resolved')->count();
        $openPortalHelpRequests = \App\Models\PortalHelpRequest::query()->where('status', '!=', 'resolved')->count();
    @endphp
    <a href="{{ route('admin.webmaster.contacts.index') }}"
       class="member-portal-nav-link flex items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.webmaster.contacts.*')) }}">
        <span class="flex items-center gap-3"><span>🛠️</span><span>Webmaster Issues</span></span>
        @if($openWebmasterIssues > 0)
        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $openWebmasterIssues > 99 ? '99+' : $openWebmasterIssues }}</span>
        @endif
    </a>
    @if(\Illuminate\Support\Facades\Route::has('admin.portal-help-requests.index'))
    <a href="{{ route('admin.portal-help-requests.index') }}"
       class="member-portal-nav-link flex items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.portal-help-requests.*')) }}">
        <span class="flex items-center gap-3"><span>📬</span><span>Portal Help Requests</span></span>
        @if($openPortalHelpRequests > 0)
        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $openPortalHelpRequests > 99 ? '99+' : $openPortalHelpRequests }}</span>
        @endif
    </a>
    @endif
    @endif

    <a href="{{ route('user.hr-portal') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs(['user.hr-portal', 'hr-portal.*', 'admin.hr-portal.*', 'admin.facility.employees*', 'admin.facility.hiring*', 'admin.facility.job_openings*', 'admin.facility.documents*', 'admin.facility.reports*'])) }}">
        <span>👥</span><span>Employee Management</span>
    </a>

    @if(\Illuminate\Support\Facades\Route::has('admin.facilities.leadership.index'))
    <a href="{{ route('admin.facilities.leadership.index') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs(['admin.facility.leadership*', 'admin.facilities.leadership*'])) }}">
        <span>👔</span><span>Facility Leadership</span>
    </a>
    @endif

    <a href="{{ route('admin.users.index') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.users.*')) }}">
        <span>👥</span><span>Users Management</span>
    </a>

    <button type="button" @click="rolesOpen = !rolesOpen"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $rolesOpen ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
        <span class="flex items-center gap-3"><span>🛡️</span><span>Roles & Permissions</span></span>
        <span class="text-xs" x-text="rolesOpen ? '▲' : '▼'"></span>
    </button>
    <div x-show="rolesOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
        <a href="{{ route('admin.roles.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.roles.*') }}">Manage Roles</a>
        <a href="{{ route('admin.permissions.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.permissions.*') }}">Manage Permissions</a>
        <a href="{{ route('admin.role-assignments.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.role-assignments.*') }}">Role Assignments</a>
        <a href="{{ route('admin.role-assignments.statistics') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.role-assignments.statistics') }}">Assignment Statistics</a>
        @if(\Illuminate\Support\Facades\Route::has('admin.position-portal-roles.index'))
        <a href="{{ route('admin.position-portal-roles.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.position-portal-roles.*') }}">Position Portal Roles</a>
        @endif
    </div>

    <button type="button" @click="tablesOpen = !tablesOpen"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $tablesOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
        <span class="flex items-center gap-3"><span>📊</span><span>Manage Tables</span></span>
        <span class="text-xs" x-text="tablesOpen ? '▲' : '▼'"></span>
    </button>
    <div x-show="tablesOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
        <a href="{{ route('admin.positions.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">Positions</a>
        <a href="{{ route('admin.upload-types.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.upload-types.*', 'admin.checklist-items.*']) }}">Documents Management</a>
        <a href="{{ route('admin.departments.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.departments.*') }}">Departments</a>
        @if(\Illuminate\Support\Facades\Route::has('admin.events.index'))
        <a href="{{ route('admin.events.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.events.*') }}">Events</a>
        @endif
        <a href="{{ route('admin.email-recipients.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-recipients.*') }}">Email Recipients</a>
        <a href="{{ route('admin.email-templates.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-templates.*') }}">Email Templates</a>
        <a href="{{ route('admin.import-mapping-presets.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.import-mapping-presets.*') }}">Import Preset Management</a>
        <a href="{{ route('admin.import-logs.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.import-logs.*') }}">Import History</a>
    </div>

    <button type="button" @click="hipaaOpen = !hipaaOpen"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $hipaaOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
        <span class="flex items-center gap-3"><span>🔒</span><span>HIPAA Compliance</span></span>
        <span class="text-xs" x-text="hipaaOpen ? '▲' : '▼'"></span>
    </button>
    <div x-show="hipaaOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
        <a href="{{ route('admin.baa-registry.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.baa-registry.*') }}">BAA Vendor Registry</a>
        <a href="{{ route('admin.hipaa-checklist.index') }}" class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.hipaa-checklist.*') }}">HIPAA Checklist</a>
    </div>

    <a href="{{ route('admin.security.dashboard') }}"
       class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.security.*')) }}">
        <span>🔐</span><span>Security Monitoring</span>
    </a>
</div>
