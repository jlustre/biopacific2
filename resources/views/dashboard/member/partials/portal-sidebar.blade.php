@php
    $active = $active ?? 'dashboard';
    $profileComplete = $profileComplete ?? 88;
    $navItems = config('member-portal.sidebar_nav', []);
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="member-portal-sidebar fixed inset-y-0 left-0 z-40 w-80 transform bg-gradient-to-b from-teal-950 to-teal-900 text-white transition-transform duration-300 lg:static lg:translate-x-0 lg:w-72">
  <div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
      <div class="flex items-center gap-3">
        <div class="member-portal-sidebar-brand flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5 shadow-lg">
          <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-lg font-bold leading-tight">Bio-Pacific</p>
          <p class="text-xs text-teal-100">HR Employee Portal</p>
        </div>
      </div>
      <button type="button" class="rounded-lg p-2 text-teal-100 hover:bg-white/10 lg:hidden"
        @click="sidebarOpen = false" aria-label="Close menu">✕</button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-4 pt-4 pb-6 text-sm">
      @foreach($navItems as $item)
        @php
            $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
            if (!empty($item['fragment'])) {
                $href .= '#' . ltrim($item['fragment'], '#');
            }
            $isActive = $active === $item['id']
                || (!empty($item['route_is']) && request()->routeIs($item['route_is']));
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

      @include('dashboard.member.partials.portal-sidebar-extras', ['active' => $active])
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
      <div class="member-portal-help-card rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-4">
        <p class="font-semibold">Need help?</p>
        <p class="mt-1 text-xs text-brand-50">Contact HR or submit a support ticket.</p>
        <a href="#support" class="member-portal-nav-link mt-3 block w-full rounded-xl bg-white px-3 py-2 text-center text-sm font-bold text-brand-700">Open Support</a>
      </div>
      @endif
    </div>
  </div>
</aside>
