@php
    $items = $profileRecognitions ?? [];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-2.5">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
            <i class="fa-solid fa-circle-check text-sm"></i>
        </span>
        <h2 class="text-base font-black text-slate-900">Recognition</h2>
    </div>

    @if(count($items) === 0)
    <p class="mt-4 text-sm text-slate-500">Milestones and awards will appear here when added to your profile.</p>
    @else
    <ul class="mt-4 space-y-3">
        @foreach($items as $item)
        <li class="flex items-start gap-2.5 text-sm">
            <span class="text-base leading-none" aria-hidden="true">{{ $item['icon'] }}</span>
            <span class="font-medium text-slate-800">{{ $item['label'] }}</span>
        </li>
        @endforeach
    </ul>
    @endif
</div>
