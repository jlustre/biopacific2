@php
    $active = $active ?? 'dashboard';
    $portalNav = $portalNav ?? 'employee';
    $navItems = match ($portalNav) {
        'admin' => [
            ['id' => 'admin-dashboard', 'route' => 'admin.dashboard.index', 'icon' => '⚙️', 'label' => 'Admin'],
            ['id' => 'facility-hr-portal', 'route' => 'user.hr-portal', 'icon' => '👥', 'label' => 'HR'],
            ['id' => 'facilities', 'route' => 'admin.facilities.index', 'icon' => '🏢', 'label' => 'Sites'],
            ['id' => 'facility-tour-requests', 'route' => 'admin.tour-requests.index', 'icon' => '💬', 'label' => 'Comms'],
            ['id' => 'settings', 'route' => 'admin.settings.index', 'icon' => '🔧', 'label' => 'Settings'],
        ],
      'corporate' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Home'],
        ['id' => 'hr-portal', 'route' => 'user.hr-portal', 'icon' => '👥', 'label' => 'HR'],
        ['id' => 'positions', 'route' => 'admin.positions.index', 'icon' => '💼', 'label' => 'Positions'],
        ['id' => 'reports', 'route' => 'admin.reports.index', 'icon' => '📊', 'label' => 'Reports'],
        ['id' => 'profile', 'route' => 'settings.profile', 'icon' => '👤', 'label' => 'Profile'],
      ],
      'facility' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Home'],
        ['id' => 'documents', 'route' => 'member.documents', 'icon' => '📄', 'label' => 'Docs'],
        ['id' => 'facility-hr-portal', 'route' => 'user.hr-portal', 'icon' => '👥', 'label' => 'HR'],
        ['id' => 'profile', 'route' => 'settings.profile', 'icon' => '👤', 'label' => 'Profile'],
      ],
        default => config('member-portal.mobile_nav', []),
    };
@endphp

<nav class="fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white px-3 py-2 shadow-2xl lg:hidden">
  <div class="grid text-center text-xs font-semibold text-slate-500" style="grid-template-columns: repeat({{ count($navItems) }}, minmax(0, 1fr));">
    @foreach($navItems as $item)
      @php
          $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
          $isActive = $active === $item['id']
              || (!empty($item['route_is']) && request()->routeIs($item['route_is']));
      @endphp
      <a href="{{ $href }}"
         class="rounded-xl p-2 {{ $isActive ? 'text-teal-700' : '' }}">
        {{ $item['icon'] }}<span class="block">{{ $item['label'] }}</span>
      </a>
    @endforeach
  </div>
</nav>
