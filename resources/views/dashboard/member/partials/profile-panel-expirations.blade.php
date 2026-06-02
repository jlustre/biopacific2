@php
    $items = $upcomingExpirations ?? [];
    $toneClasses = [
        'urgent' => 'text-rose-600',
        'warning' => 'text-amber-600',
        'ok' => 'text-emerald-600',
    ];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-2.5">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
            <i class="fa-regular fa-clock text-sm"></i>
        </span>
        <h2 class="text-base font-black text-slate-900">Upcoming Expirations</h2>
    </div>

    @if(count($items) === 0)
    <p class="mt-4 text-sm text-slate-500">No upcoming expirations on your record.</p>
    @else
    <ul class="mt-4 space-y-3">
        @foreach($items as $item)
        <li class="flex items-center justify-between gap-3 text-sm">
            <span class="font-medium text-slate-800">{{ $item['label'] }}</span>
            <span class="shrink-0 font-bold {{ $toneClasses[$item['tone']] ?? 'text-slate-600' }}">
                {{ $item['tone_label'] }}
            </span>
        </li>
        @endforeach
    </ul>
    @endif
</div>
