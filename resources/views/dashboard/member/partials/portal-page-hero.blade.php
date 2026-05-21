@props([
    'badge' => null,
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-[2rem] bg-teal-700 text-white shadow-soft']) }}>
    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-teal-600/40" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-teal-800/50" aria-hidden="true"></div>
    <div class="relative z-10 p-6 sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0 flex-1">
                @if($badge)
                <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">{{ $badge }}</div>
                @endif
                <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl">{{ $title }}</h2>
                @if($subtitle)
                <p class="mt-2 max-w-2xl text-sm text-teal-50 sm:text-base">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($aside))
            <div class="shrink-0 lg:min-w-56">
                {{ $aside }}
            </div>
            @endif
        </div>
    </div>
</div>
