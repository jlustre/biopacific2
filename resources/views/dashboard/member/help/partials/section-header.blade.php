@php
    $sectionNumber = $sectionNumber ?? '1';
    $sectionTitle = $sectionTitle ?? 'Section';
    $sectionDescription = $sectionDescription ?? null;
    $accent = $accent ?? 'teal';
    $badgeClass = $accent === 'indigo'
        ? 'bg-indigo-100 text-indigo-800'
        : 'bg-teal-100 text-teal-800';
@endphp

<div class="border-b border-slate-100 px-6 py-4 sm:px-8">
    <div class="flex items-start gap-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $badgeClass }} text-sm font-black">{{ $sectionNumber }}</span>
        <div>
            <h2 class="text-base font-bold text-slate-900">{{ $sectionTitle }}</h2>
            @if($sectionDescription)
            <p class="mt-1 text-sm text-slate-500">{{ $sectionDescription }}</p>
            @endif
        </div>
    </div>
</div>
