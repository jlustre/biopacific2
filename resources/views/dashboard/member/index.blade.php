@extends('layouts.member-portal')

@php
    $personaLabel = $dashboardPersonaLabel ?? 'Team Member';
    $intro = $dashboardIntro ?? '';
    $kpis = $dashboardKpis ?? [];
    $myTasks = $dashboardMyTasks ?? [];
    $quickActions = $dashboardQuickActions ?? [];
    $toneMap = [
        'brand' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-100'],
        'teal' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200'],
    ];
@endphp

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $todayLabel ?? now()->format('l, M j') }}</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-900 sm:text-xl">My Dashboard</h1>
            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-600 sm:text-sm">{{ $intro }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-teal-50 px-2.5 py-1 font-bold text-teal-800">{{ $personaLabel }}</span>
            <a href="{{ route('settings.profile') }}"
               class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700 hover:shadow">My Profile</a>
        </div>
    </div>

    <div class="mt-3">
        <h2 class="sr-only">My stats</h2>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($kpis as $card)
                @php $tone = $toneMap[$card['tone'] ?? 'brand'] ?? $toneMap['brand']; @endphp
                <a href="{{ $card['route'] ?? '#' }}"
                   class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition hover:border-teal-300 hover:shadow">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $tone['bg'] }} {{ $tone['text'] }} ring-1 {{ $tone['ring'] }}">
                        <i class="fa-solid {{ $card['icon'] ?? 'fa-chart-simple' }} text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="text-xl font-black leading-none text-slate-900">{{ $card['value'] }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-3 grid gap-3 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-2.5">
                    <h2 class="text-sm font-black text-slate-900">My tasks</h2>
                    <p class="text-[11px] text-slate-500">Your compliance work — profile and contact info are under My Profile</p>
                </div>
                @if(count($myTasks) === 0)
                <p class="px-4 py-6 text-center text-sm text-slate-500">You’re caught up. Check back when HR assigns new items.</p>
                @else
                <ul class="divide-y divide-slate-100">
                    @foreach($myTasks as $task)
                    <li class="flex items-start gap-3 px-4 py-2.5 text-sm">
                        <span class="mt-0.5 text-teal-600"><i class="fa-regular fa-circle"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900">{{ $task['title'] ?? 'Task' }}</p>
                            @if(!empty($task['description']))
                            <p class="text-xs text-slate-500">{{ $task['description'] }}</p>
                            @endif
                        </div>
                        @if(!empty($task['route']))
                        <a href="{{ $task['route'] }}" class="shrink-0 text-xs font-bold text-teal-700 hover:text-teal-900">Open</a>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Work center</h3>
                <div class="mt-2 grid gap-1.5 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach($quickActions as $action)
                    <a href="{{ $action['route'] }}" class="flex items-center gap-2.5 rounded-lg border border-slate-100 px-2.5 py-2 hover:border-teal-200 hover:bg-teal-50/50">
                        <i class="fa-solid {{ $action['icon'] }} text-teal-700"></i>
                        <span>
                            <span class="block text-xs font-bold text-slate-900">{{ $action['title'] }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $action['subtitle'] }}</span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            <p class="mt-2 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-[11px] leading-relaxed text-slate-600">
                Employee ID, department, hire date, and account settings are on
                <a href="{{ route('settings.profile') }}" class="font-bold text-teal-700 hover:underline">My Profile</a>.
            </p>
        </aside>
    </div>
</section>
@endsection
