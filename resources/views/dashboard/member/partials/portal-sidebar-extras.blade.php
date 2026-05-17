@php
    $active = $active ?? 'dashboard';
    $authUser = auth()->user();
    $webRoutePatterns = config('member-portal.facility_management_web_route_patterns', []);
    $commRoutePatterns = config('member-portal.facility_management_comm_route_patterns', []);
    $webSectionOpen = request()->routeIs($webRoutePatterns);
    $commSectionOpen = request()->routeIs($commRoutePatterns);
@endphp

@if($authUser)
    @php
        $extraClass = fn (bool $isActive) => $isActive
            ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white'
            : 'text-teal-100';
        $subLinkClass = fn (array|string $patterns) => request()->routeIs($patterns)
            ? 'member-portal-nav-sub-active font-semibold text-white'
            : 'text-teal-100';
    @endphp

    @if($authUser->hasRole(['admin']))
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Administration</p>
            <a href="{{ route('admin.dashboard.index') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.dashboard.*')) }}">
                <span>⚙️</span>
                <span>Admin Dashboard</span>
            </a>
        </div>
    @endif

    @if($authUser->hasRole(['hrrd']))
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">HR</p>
            <a href="{{ route('user.hr-portal') }}"
               class="flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass($active === 'facility-hr-portal' || request()->routeIs('user.hr-portal')) }}">
                <span>👥</span>
                <span>HR Portal</span>
            </a>
        </div>
    @endif

    @if($authUser->hasRole(['facility-admin', 'facility-dsd']))
        <div class="my-3 space-y-1 border-t border-white/10 pt-3"
             x-data="{ webOpen: {{ $webSectionOpen ? 'true' : 'false' }}, commOpen: {{ $commSectionOpen ? 'true' : 'false' }} }">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Facility management</p>
            <a href="{{ route('user.hr-portal') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass($active === 'facility-hr-portal' || request()->routeIs(['user.hr-portal', 'admin.facility.dashboard'])) }}">
                <span>👥</span>
                <span>HR Portal</span>
            </a>
            <button type="button" @click="webOpen = !webOpen"
                    class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $webSectionOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
                <span class="flex items-center gap-3"><span>🌐</span><span>Web Contents</span></span>
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
                   class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.news.*') }}">News</a>
                <a href="{{ route('admin.facilities.webcontents.blogs') }}"
                   class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass(['admin.facilities.webcontents.blogs', 'facilities.webcontents.blogs', 'admin.blogs.*']) }}">Blogs</a>
                <a href="{{ $authUser->facility_id ? route('admin.facilities.webcontents.careers', ['facility_id' => $authUser->facility_id]) : route('admin.facilities.webcontents.careers') }}"
                   class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.facilities.webcontents.careers*') }}">Careers</a>
                <a href="{{ route('admin.services.index') }}"
                   class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.services.*') }}">Services</a>
            </div>
            <button type="button" @click="commOpen = !commOpen"
                    class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $commSectionOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
                <span class="flex items-center gap-3"><span>💬</span><span>Communications</span></span>
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
    @endif
@endif
