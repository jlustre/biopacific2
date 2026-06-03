@php
    $hrManagementCards = $hrManagementCards ?? [];
    $hrManagementIntro = $hrManagementIntro ?? 'Manage staffing, hiring, credentials, and facility data.';
    $toneMap = [
        'teal' => [
            'card' => 'border-teal-400/40 bg-gradient-to-br from-teal-500 via-teal-600 to-teal-900 shadow-teal-900/30 hover:shadow-teal-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-teal-100',
            'arrow' => 'text-white',
        ],
        'sky' => [
            'card' => 'border-sky-400/40 bg-gradient-to-br from-sky-500 via-sky-600 to-sky-900 shadow-sky-900/30 hover:shadow-sky-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-sky-100',
            'arrow' => 'text-white',
        ],
        'emerald' => [
            'card' => 'border-emerald-400/40 bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-900 shadow-emerald-900/30 hover:shadow-emerald-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-emerald-100',
            'arrow' => 'text-white',
        ],
        'cyan' => [
            'card' => 'border-cyan-400/40 bg-gradient-to-br from-cyan-500 via-cyan-600 to-cyan-900 shadow-cyan-900/30 hover:shadow-cyan-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-cyan-100',
            'arrow' => 'text-white',
        ],
        'rose' => [
            'card' => 'border-rose-400/40 bg-gradient-to-br from-rose-500 via-rose-600 to-rose-900 shadow-rose-900/30 hover:shadow-rose-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-rose-100',
            'arrow' => 'text-white',
        ],
        'amber' => [
            'card' => 'border-amber-400/40 bg-gradient-to-br from-amber-500 via-amber-600 to-amber-900 shadow-amber-900/30 hover:shadow-amber-900/45',
            'icon' => 'bg-white/20 text-white ring-white/25',
            'title' => 'text-white',
            'desc' => 'text-amber-100',
            'arrow' => 'text-white',
        ],
    ];
@endphp

@if(count($hrManagementCards) > 0)
<section>
    <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-base font-black text-slate-900 sm:text-lg">HR Management</h2>
            <p class="text-sm text-slate-500">{{ $hrManagementIntro }}</p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($hrManagementCards as $card)
            @php $t = $toneMap[$card['tone'] ?? 'teal'] ?? $toneMap['teal']; @endphp
            @if(($card['type'] ?? 'link') === 'import')
            <button type="button"
                onclick="document.getElementById('importModal')?.classList.remove('hidden')"
                class="group relative flex min-h-[8.5rem] w-full flex-col overflow-hidden rounded-2xl border p-4 text-left shadow-lg ring-1 ring-white/10 transition duration-200 hover:-translate-y-1 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 {{ $t['card'] }}">
                <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></span>
                <span class="pointer-events-none absolute -bottom-10 -left-6 h-24 w-24 rounded-full bg-black/10"></span>
                <div class="relative flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg ring-1 {{ $t['icon'] }}">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </span>
                        <h3 class="truncate text-base font-bold {{ $t['title'] }}">{{ $card['label'] }}</h3>
                    </div>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20">
                        <i class="fas fa-plus text-xs {{ $t['arrow'] }}"></i>
                    </span>
                </div>
                <p class="relative mt-3 text-sm {{ $t['desc'] }}">{{ $card['desc'] }}</p>
            </button>
            @else
            <a href="{{ $card['route'] ?? '#' }}"
               class="group relative flex min-h-[8.5rem] flex-col overflow-hidden rounded-2xl border p-4 shadow-lg ring-1 ring-white/10 transition duration-200 hover:-translate-y-1 hover:scale-[1.02] {{ $t['card'] }}">
                <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></span>
                <span class="pointer-events-none absolute -bottom-10 -left-6 h-24 w-24 rounded-full bg-black/10"></span>
                <div class="relative flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg ring-1 {{ $t['icon'] }}">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </span>
                        <h3 class="truncate text-base font-bold {{ $t['title'] }}">{{ $card['label'] }}</h3>
                    </div>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 opacity-0 ring-1 ring-white/20 transition group-hover:opacity-100">
                        <i class="fas fa-arrow-right text-xs {{ $t['arrow'] }}"></i>
                    </span>
                </div>
                <p class="relative mt-3 text-sm {{ $t['desc'] }}">{{ $card['desc'] }}</p>
            </a>
            @endif
        @endforeach
    </div>
</section>
@endif
