@php
    $active = $active ?? 'dashboard';
    $groups = $groups ?? \App\Support\MemberPortalLayout::purposeGroupsForUser(auth()->user());
    $authUser = auth()->user();
    $selectedFacilityId = \App\Support\SelectedFacility::id();
    $careersUrl = $selectedFacilityId
        ? route('admin.facilities.webcontents.careers', ['facility_id' => $selectedFacilityId])
        : (($authUser && $authUser->facility_id)
            ? route('admin.facilities.webcontents.careers', ['facility_id' => $authUser->facility_id])
            : (\Illuminate\Support\Facades\Route::has('admin.facilities.webcontents.careers')
                ? route('admin.facilities.webcontents.careers')
                : '#'));

    $subLinkClass = fn (bool $isActive) => $isActive
        ? 'member-portal-nav-sub-active font-semibold text-white'
        : 'text-teal-100';

    $itemIsActive = function (array $item) use ($active): bool {
        return \App\Support\MemberPortalLayout::navItemMatchesRequest($item, $active);
    };

    $badgeValue = function (array $item) {
        return match ($item['badge'] ?? null) {
            'tasks' => $myTasksCount ?? null,
            'messages' => $myMessagesCount ?? null,
            'documents' => ($documentsNeededCount ?? 0) > 0 ? $documentsNeededCount : null,
            'webmaster' => \App\Models\WebmasterContact::query()->where('status', '!=', 'resolved')->count() ?: null,
            'portal_help' => \App\Models\PortalHelpRequest::query()->where('status', '!=', 'resolved')->count() ?: null,
            default => $item['badge_count'] ?? null,
        };
    };
@endphp

<div class="space-y-1"
     x-data="{
        open: {
            @foreach($groups as $group)
            '{{ $group['id'] }}': {{ !empty($group['open']) ? 'true' : 'false' }},
            @endforeach
        }
     }">
    @foreach($groups as $group)
        @php $gid = $group['id']; @endphp
        <div class="my-2 space-y-1 @if(!$loop->first) border-t border-white/10 pt-2 @endif">
            <button type="button"
                    @click="open['{{ $gid }}'] = !open['{{ $gid }}']"
                    class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-teal-100 hover:bg-white/10 hover:text-white"
                    :class="open['{{ $gid }}'] ? 'bg-white/10 text-white' : ''">
                <span class="flex items-center gap-3">
                    <span>{{ $group['icon'] }}</span>
                    <span class="text-xs font-semibold uppercase tracking-wide">{{ $group['label'] }}</span>
                </span>
                <span class="text-xs" x-text="open['{{ $gid }}'] ? '▲' : '▼'"></span>
            </button>

            <div x-show="open['{{ $gid }}']" x-cloak class="ml-2 space-y-1 border-l border-white/10 pl-3">
                @foreach($group['items'] as $item)
                    @php
                        $isActive = $itemIsActive($item);
                        if (!empty($item['careers'])) {
                            $href = $careersUrl;
                        } else {
                            $href = \App\Support\MemberPortalLayout::routeWithSelectedFacility($item['route']);
                            if (!empty($item['query']) && is_array($item['query'])) {
                                $href .= (str_contains($href, '?') ? '&' : '?').http_build_query($item['query']);
                            }
                        }
                        $badge = $badgeValue($item);
                    @endphp
                    <a href="{{ $href }}"
                       class="member-portal-nav-link flex items-center {{ $badge ? 'justify-between' : 'gap-3' }} rounded-lg px-3 py-2 text-sm {{ $subLinkClass($isActive) }}">
                        <span class="flex min-w-0 items-center gap-2">
                            @if(!empty($item['icon']))
                            <span class="shrink-0">{{ $item['icon'] }}</span>
                            @endif
                            <span class="truncate">{{ $item['label'] }}</span>
                        </span>
                        @if(!empty($badge))
                        <span class="rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-slate-900">
                            {{ $badge > 99 ? '99+' : $badge }}
                        </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
