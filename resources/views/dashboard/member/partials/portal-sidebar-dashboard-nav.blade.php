@php
    $active = $active ?? 'dashboard';
    $items = $items ?? [];
    $extraClass = fn (bool $isActive) => $isActive
        ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white'
        : 'text-teal-100';
@endphp

@foreach($items as $item)
    @php
        $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
        if (!empty($item['fragment'])) {
            $href .= '#' . ltrim($item['fragment'], '#');
        }
        $routePatterns = $item['route_is'] ?? null;
        $isActive = $active === $item['id']
            || ($routePatterns && request()->routeIs($routePatterns));
    @endphp
    <a href="{{ $href }}"
       class="member-portal-nav-link flex items-center {{ !empty($item['badge']) ? 'justify-between' : 'gap-3' }} rounded-xl px-4 py-3 {{ $extraClass($isActive) }}">
        <span class="flex items-center gap-3">
            @if(!empty($item['icon']))
            <span>{{ $item['icon'] }}</span>
            @endif
            <span>{{ $item['label'] }}</span>
        </span>
        @if(!empty($item['badge']))
        <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $item['badge_class'] ?? 'bg-amber-400 text-slate-900' }}">{{ $item['badge'] }}</span>
        @endif
    </a>
@endforeach
