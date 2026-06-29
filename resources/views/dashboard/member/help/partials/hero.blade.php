@php
    $tone = $tone ?? 'teal';
    $heroIcon = $heroIcon ?? 'fa-circle-question';
    $heroTitle = $heroTitle ?? 'Help Center';
    $heroSubtitle = $heroSubtitle ?? '';
    $tips = $tips ?? [];
@endphp

<div class="portal-help-hero overflow-hidden rounded-3xl bg-gradient-to-br from-teal-900 via-teal-800 to-teal-950 p-6 text-white shadow-lg sm:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-teal-100 ring-1 ring-white/15">
                <i class="fa-solid {{ $heroIcon }}"></i>
                <span>Secure portal form</span>
            </div>
            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">{{ $heroTitle }}</h1>
            @if($heroSubtitle)
            <p class="mt-3 text-sm leading-relaxed text-teal-100 sm:text-base">{{ $heroSubtitle }}</p>
            @endif
        </div>
        <div class="grid shrink-0 gap-2 sm:grid-cols-3 lg:grid-cols-1 lg:min-w-[12rem]">
            <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                <p class="text-[10px] font-bold uppercase tracking-wide text-teal-200">Response time</p>
                <p class="mt-1 text-sm font-semibold">1–2 business days</p>
            </div>
            <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                <p class="text-[10px] font-bold uppercase tracking-wide text-teal-200">Privacy</p>
                <p class="mt-1 text-sm font-semibold">No PHI in messages</p>
            </div>
            <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                <p class="text-[10px] font-bold uppercase tracking-wide text-teal-200">Tracking</p>
                <p class="mt-1 text-sm font-semibold">Reference code provided</p>
            </div>
        </div>
    </div>
</div>

@if(!empty($tips))
<div class="mt-6 grid gap-4 md:grid-cols-3">
    @foreach($tips as $tip)
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
            <i class="fa-solid {{ $tip['icon'] ?? 'fa-lightbulb' }}"></i>
        </div>
        <h3 class="mt-3 text-sm font-bold text-slate-900">{{ $tip['title'] }}</h3>
        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $tip['body'] }}</p>
    </div>
    @endforeach
</div>
@endif
