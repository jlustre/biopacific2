@php
    $active = $active ?? 'dashboard';
    $profileComplete = $profileComplete ?? 88;
    $portalNav = $portalNav ?? 'employee';
    $usesStructuredNav = in_array($portalNav, ['employee', 'facility', 'corporate'], true);
    $navItems = match ($portalNav) {
        'admin' => config('member-portal.admin_sidebar_nav', []),
        default => [],
    };
    $dashboardNav = match ($portalNav) {
        'facility' => config('member-portal.facility_dashboard_nav', []),
        'corporate' => config('member-portal.corporate_dashboard_nav', []),
        'employee' => \App\Support\MemberPortalLayout::employeeDashboardNavItems(auth()->user()),
        default => [],
    };
    $portalSubtitle = $portalSubtitle ?? match ($portalNav) {
        'admin' => 'Admin Portal',
    'corporate' => 'Corporate Management',
    'facility' => 'Facility Portal',
        default => 'HR Employee Portal',
    };
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="member-portal-sidebar fixed inset-y-0 left-0 z-40 w-80 transform bg-teal-800 text-white transition-transform duration-300 lg:static lg:translate-x-0 lg:w-72">
  <div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
      <div class="flex items-center gap-3">
        <div class="member-portal-sidebar-brand flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5 shadow-lg">
          <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-lg font-bold leading-tight">Bio-Pacific</p>
          <p class="text-xs text-teal-100">{{ $portalSubtitle }}</p>
        </div>
      </div>
      <button type="button" class="rounded-lg p-2 text-teal-100 hover:bg-white/10 lg:hidden"
        @click="sidebarOpen = false" aria-label="Close menu">✕</button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-4 pt-4 pb-6 text-sm">
      @if($usesStructuredNav)
        <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Dashboard</p>
        @include('dashboard.member.partials.portal-sidebar-dashboard-nav', [
            'active' => $active,
            'items' => $dashboardNav,
        ])

        @include('dashboard.member.partials.portal-sidebar-personal-portal', ['active' => $active])

        @if(in_array($portalNav, ['corporate', 'facility'], true)
            || (auth()->user() && \App\Support\MemberPortalLayout::userIsSystemAdmin(auth()->user()) && request()->routeIs(array_merge(
                config('member-portal.facility_manager_global_route_patterns', []),
                config('member-portal.facility_management_route_patterns', [])
            ))))
          @include('dashboard.member.partials.portal-sidebar-management-groups', [
              'active' => $active,
              'portalNav' => $portalNav === 'admin' ? 'facility' : $portalNav,
          ])
        @endif

        @if($portalNav === 'employee')
          @include('dashboard.member.partials.portal-sidebar-extras', ['active' => $active, 'portalNav' => $portalNav])
        @endif
      @else
        @foreach($navItems as $item)
          @php
              $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
              if (!empty($item['fragment'])) {
                  $href .= '#' . ltrim($item['fragment'], '#');
              }
              $routePatterns = $item['route_is'] ?? null;
              $isActive = $active === $item['id']
                  || ($routePatterns && request()->routeIs($routePatterns));
              $linkClass = $isActive
                  ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white'
                  : 'text-teal-100';
          @endphp
          <a href="{{ $href }}"
             class="member-portal-nav-link flex items-center {{ !empty($item['badge']) ? 'justify-between' : 'gap-3' }} rounded-xl px-4 py-3 {{ $linkClass }}">
            <span class="flex items-center gap-3">
              <span>{{ $item['icon'] }}</span>
              <span>{{ $item['label'] }}</span>
            </span>
            @if(!empty($item['badge']))
            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $item['badge_class'] ?? 'bg-amber-400 text-slate-900' }}">{{ $item['badge'] }}</span>
            @endif
          </a>
        @endforeach

        @if(($portalNav ?? '') === 'admin' && auth()->user() && \App\Support\MemberPortalLayout::userIsSystemAdmin(auth()->user()))
          @include('dashboard.member.partials.portal-sidebar-admin-management-groups', ['active' => $active])
        @endif
      @endif
    </nav>

    <div class="border-t border-white/10 p-4">
      @if($active === 'profile')
      <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
        <p class="text-sm font-semibold">Profile Record Status</p>
        <div class="mt-3 h-2 rounded-full bg-white/20">
          <div class="h-2 rounded-full bg-teal-300" style="width: {{ min(100, max(0, $profileComplete)) }}%"></div>
        </div>
        <p class="mt-2 text-xs text-teal-100">{{ $profileComplete }}% complete</p>
      </div>
      @else
      <div class="member-portal-help-card overflow-hidden rounded-2xl p-4">
        <div class="member-portal-help-card-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="relative">
          <div class="flex items-start gap-3">
            <span class="member-portal-help-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm" aria-hidden="true">
              <i class="fa-solid fa-circle-question"></i>
            </span>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-bold leading-snug text-white">Need help?</p>
              <p class="mt-1 text-xs leading-relaxed text-teal-100">Contact HR for payroll, benefits, onboarding, or account questions.</p>
            </div>
          </div>
          <div class="mt-4 grid gap-2">
            <a href="mailto:hr@biopacific.com" class="member-portal-help-action member-portal-help-action-primary">
              <i class="fa-solid fa-envelope text-xs" aria-hidden="true"></i>
              <span>Email HR</span>
            </a>
            <a href="mailto:hr@biopacific.com?subject=Support%20Request" class="member-portal-help-action member-portal-help-action-secondary">
              <i class="fa-solid fa-ticket text-xs" aria-hidden="true"></i>
              <span>Submit support request</span>
            </a>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</aside>
