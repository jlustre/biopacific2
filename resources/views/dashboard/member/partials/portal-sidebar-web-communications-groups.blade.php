@php
    $authUser = $authUser ?? auth()->user();
    $webRoutePatterns = config('member-portal.facility_management_web_route_patterns', []);
    $commRoutePatterns = config('member-portal.facility_management_comm_route_patterns', []);
    $webSectionOpen = $webSectionOpen ?? request()->routeIs($webRoutePatterns);
    $commSectionOpen = $commSectionOpen ?? request()->routeIs($commRoutePatterns);
    $subLinkClass = $subLinkClass ?? fn (array|string $patterns) => request()->routeIs($patterns)
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';
    $careersUrl = $careersUrl ?? (($authUser && $authUser->facility_id)
        ? route('admin.facilities.webcontents.careers', ['facility_id' => $authUser->facility_id])
        : route('admin.facilities.webcontents.careers'));
@endphp

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
    @if(\App\Support\MemberPortalLayout::userIsSystemAdmin($authUser))
    @php
        $openWebmasterIssues = \App\Models\WebmasterContact::query()->where('status', '!=', 'resolved')->count();
        $openPortalHelpRequests = \App\Models\PortalHelpRequest::query()->where('status', '!=', 'resolved')->count();
    @endphp
    <a href="{{ route('admin.webmaster.contacts.index') }}"
       class="member-portal-nav-link flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.webmaster.contacts.*') }}">
        <span>Webmaster Issues</span>
        @if($openWebmasterIssues > 0)
        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $openWebmasterIssues > 99 ? '99+' : $openWebmasterIssues }}</span>
        @endif
    </a>
    <a href="{{ route('admin.portal-help-requests.index') }}"
       class="member-portal-nav-link flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.portal-help-requests.*') }}">
        <span>Portal Help Requests</span>
        @if($openPortalHelpRequests > 0)
        <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $openPortalHelpRequests > 99 ? '99+' : $openPortalHelpRequests }}</span>
        @endif
    </a>
    @endif
    <a href="{{ route('admin.job-applications.index') }}"
       class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.job-applications.*') }}">Job Applications</a>
    <a href="{{ route('admin.email-recipients.index') }}"
       class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-recipients.*') }}">Email Recipients</a>
    <a href="{{ route('admin.email-templates.index') }}"
       class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.email-templates.*') }}">Email Templates</a>
    <a href="{{ route('admin.scheduled-reports.index') }}"
       class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.scheduled-reports.*') }}">Scheduled Reports</a>
    <a href="{{ route('admin.communications.employee-email-mappings') }}"
       class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass('admin.communications.employee-email-mappings') }}">Employee Email Mappings</a>
</div>
