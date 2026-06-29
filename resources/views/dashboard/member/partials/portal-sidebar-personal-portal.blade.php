@php
    $active = $active ?? 'dashboard';
    $items = config('member-portal.personal_portal_nav', []);
    $personalRoutePatterns = config('member-portal.personal_portal_route_patterns', []);
    $documentsRoutePatterns = config('member-portal.personal_portal_documents_route_patterns', []);
    $personalOpen = request()->routeIs($personalRoutePatterns);
    $documentsOpen = request()->routeIs(array_merge(
        $documentsRoutePatterns,
        ['member.certifications', 'member.certifications.*']
    ));
    $subLinkClass = fn (array|string $patterns) => request()->routeIs($patterns)
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';
    $itemIsActive = function (array $item) use ($active): bool {
        if ($active === ($item['id'] ?? '')) {
            return true;
        }
        if (!empty($item['route_is']) && request()->routeIs($item['route_is'])) {
            return true;
        }
        foreach ($item['children'] ?? [] as $child) {
            if ($active === ($child['id'] ?? '') || (!empty($child['route_is']) && request()->routeIs($child['route_is']))) {
                return true;
            }
        }

        return false;
    };
@endphp

<div class="my-3 space-y-1 border-t border-white/10 pt-3"
     x-data="{ personalOpen: {{ $personalOpen ? 'true' : 'false' }}, documentsOpen: {{ $documentsOpen ? 'true' : 'false' }} }">
    <button type="button"
            @click="personalOpen = !personalOpen"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 {{ $personalOpen ? 'bg-white/10 text-white' : 'text-teal-100 hover:bg-white/10 hover:text-white' }}">
        <span class="flex items-center gap-3"><span>👤</span><span>Personal Portal</span></span>
        <span class="text-xs" x-text="personalOpen ? '▲' : '▼'"></span>
    </button>

    <div x-show="personalOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
        @foreach($items as $item)
            @if(!empty($item['children']))
                @php $documentsActive = $itemIsActive($item); @endphp
                <div class="space-y-1">
                    <div class="flex items-center gap-1 rounded-lg {{ $documentsActive ? 'bg-white/10' : '' }}">
                        <a href="{{ route($item['route']) }}"
                           class="member-portal-nav-link flex min-w-0 flex-1 items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm {{ $subLinkClass($item['route_is'] ?? [$item['route']]) }}">
                            <span>{{ $item['label'] }}</span>
                            @php
                                $navBadge = ($item['id'] ?? '') === 'documents'
                                    ? ($documentsNeededCount ?? null)
                                    : ($item['badge'] ?? null);
                            @endphp
                            @if(!empty($navBadge))
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $item['badge_class'] ?? 'bg-amber-400 text-slate-900' }}">{{ $navBadge }}</span>
                            @endif
                        </a>
                        <button type="button"
                                @click="documentsOpen = !documentsOpen"
                                class="shrink-0 rounded-lg px-2 py-2 text-xs text-teal-100 hover:bg-white/10 hover:text-white"
                                aria-label="Toggle {{ $item['label'] }} submenu">
                            <span x-text="documentsOpen ? '▲' : '▼'"></span>
                        </button>
                    </div>
                    <div x-show="documentsOpen" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
                        @foreach($item['children'] as $child)
                            <a href="{{ route($child['route']) }}"
                               class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $subLinkClass($child['route_is'] ?? $child['route']) }}">
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $href = isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
                    $isActive = $itemIsActive($item);
                @endphp
                <a href="{{ $href }}"
                   class="member-portal-nav-link block rounded-lg px-3 py-2 text-sm {{ $isActive ? 'member-portal-nav-sub-active font-semibold text-white' : 'text-teal-100' }}">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
