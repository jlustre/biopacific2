@php
    $active = $active ?? 'dashboard';
    $authUser = auth()->user();
    $portalNav = $portalNav ?? 'facility';
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
    $hrActive = $active === 'facility-hr-portal'
         || request()->routeIs(['user.hr-portal', 'hr-portal.*', 'admin.facility.dashboard', 'admin.facility.*', 'admin.employees.*', 'admin.facility.employees*', 'admin.facility.hiring', 'admin.facility.job_openings*', 'admin.facility.reports*', 'admin.facility.documents*', 'admin.facility.uploads*', 'admin.facility.pre-employment*', 'admin.reports.*', 'admin.scheduled-reports.*', 'admin.scheduled-report-runs.*', 'admin.hr-portal.*']);
    $subLinkClass = fn (array|string $patterns) => request()->routeIs($patterns)
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';
    $careersUrl = ($authUser && $authUser->facility_id)
        ? route('admin.facilities.webcontents.careers', ['facility_id' => $authUser->facility_id])
        : route('admin.facilities.webcontents.careers');
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
        @if($portalNav === 'corporate')
        <a href="{{ route('admin.positions.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">
            Positions Management
        </a>
        <a href="{{ route('admin.reports.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.reports.*', 'admin.scheduled-reports.*']) }}">
            Reports
        </a>
        @endif

        <a href="{{ route('user.hr-portal') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $hrActive ? 'member-portal-nav-sub-active font-semibold text-white' : 'text-teal-100' }}">
            HR Management
        </a>

        <a href="{{ route('admin.upload-types.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.upload-types.*') }}">
            Documents Management
        </a>

        <a href="{{ route('admin.training-management.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.training-management.*') }}">
            Training Management
        </a>

        @if($portalNav === 'facility' && ($authUser->can(\App\Support\Rbac\Permissions::VIEW_POSITIONS) || $authUser->hasRole(['facility-admin', 'facility-dsd', 'don'])))
        <a href="{{ route('admin.positions.index') }}"
           class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.positions.*') }}">
            Positions Management
        </a>
        @endif

        <button type="button" @click="webOpen = !webOpen"
                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm {{ $webSectionOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
            <span class="flex items-center gap-2"><span>🌐</span><span>Web Contents</span></span>
            <span class="text-xs" x-text="webOpen ? '▲' : '▼'"></span>
        </button>
        <div x-show="webOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
            <a href="{{ route('admin.facilities.webcontents.testimonials') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.facilities.webcontents.testimonials', 'facilities.webcontents.testimonials*']) }}">Testimonials</a>
            <a href="{{ route('admin.facilities.webcontents.faqs') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.facilities.webcontents.faqs', 'facilities.webcontents.faqs*']) }}">FAQs</a>
            <a href="{{ route('admin.galleries.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.galleries.*') }}">Galleries</a>
            <a href="{{ route('admin.news.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.news.*', 'facilities.news-events.*', 'facilities.webcontents.news-events']) }}">News</a>
            <a href="{{ route('admin.facilities.webcontents.blogs') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.facilities.webcontents.blogs', 'facilities.webcontents.blogs', 'admin.blogs.*']) }}">Blogs</a>
            <a href="{{ $careersUrl }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.facilities.webcontents.careers*') }}">Careers</a>
            <a href="{{ route('admin.services.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.services.*') }}">Services</a>
        </div>

        <button type="button" @click="commOpen = !commOpen"
                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm {{ $commSectionOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
            <span class="flex items-center gap-2"><span>💬</span><span>Communications</span></span>
            <span class="text-xs" x-text="commOpen ? '▲' : '▼'"></span>
        </button>
        <div x-show="commOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
            <a href="{{ route('admin.tour-requests.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.tour-requests.*') }}">Tour Requests</a>
            <a href="{{ route('admin.inquiries.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.inquiries.*') }}">General Inquiries</a>
            <a href="{{ route('admin.job-applications.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.job-applications.*') }}">Job Applications</a>
            <a href="{{ route('admin.email-recipients.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-recipients.*') }}">Email Recipients</a>
            <a href="{{ route('admin.email-templates.index') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-templates.*') }}">Email Templates</a>
            <a href="{{ route('admin.communications.employee-email-mappings') }}"
               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.communications.employee-email-mappings') }}">Employee Email Mappings</a>
        </div>
    </div>
</div>
@endif
