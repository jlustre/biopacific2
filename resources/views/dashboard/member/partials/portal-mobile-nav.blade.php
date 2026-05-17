@php
    $active = $active ?? 'dashboard';
    $navItems = config('member-portal.mobile_nav', []);
@endphp

<nav class="fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white px-3 py-2 shadow-2xl lg:hidden">
  <div class="grid text-center text-xs font-semibold text-slate-500" style="grid-template-columns: repeat({{ count($navItems) }}, minmax(0, 1fr));">
    @foreach($navItems as $item)
      @php
          $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
          $isActive = $active === $item['id'];
      @endphp
      <a href="{{ $href }}"
         class="rounded-xl p-2 {{ $isActive ? 'text-brand-700' : '' }}">
        {{ $item['icon'] }}<span class="block">{{ $item['label'] }}</span>
      </a>
    @endforeach
  </div>
</nav>
