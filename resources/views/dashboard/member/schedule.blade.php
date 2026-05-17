@extends('layouts.member-portal')

@php
    $weekDays = $weekDays ?? [];
    $upcomingEvents = $upcomingEvents ?? [];
    $calendarEvents = $calendarEvents ?? [];
    $hasShiftData = $hasShiftData ?? false;
    $todayLabel = $todayLabel ?? now()->format('l, F j, Y');
    $weekRangeLabel = $weekRangeLabel ?? '';
    $eventTypeStyles = [
        'danger' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-100', 'dot' => 'bg-rose-500', 'text' => 'text-rose-800'],
        'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-100', 'dot' => 'bg-amber-500', 'text' => 'text-amber-800'],
        'info' => ['bg' => 'bg-sky-50', 'border' => 'border-sky-100', 'dot' => 'bg-sky-500', 'text' => 'text-sky-800'],
        'period' => ['bg' => 'bg-brand-50', 'border' => 'border-brand-100', 'dot' => 'bg-brand-500', 'text' => 'text-brand-800'],
        'deadline' => ['bg' => 'bg-violet-50', 'border' => 'border-violet-100', 'dot' => 'bg-violet-500', 'text' => 'text-violet-800'],
        'milestone' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-800'],
        'activity' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400', 'text' => 'text-slate-700'],
    ];
@endphp

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card sm:col-span-2">
                <p class="text-sm font-medium text-slate-500">This week</p>
                <p class="mt-1 text-2xl font-black text-slate-950">{{ $weekRangeLabel }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $todayLabel }}</p>
                @if($facilityName ?? null)
                <p class="mt-1 text-xs text-slate-500">{{ $facilityName }} · {{ $positionTitle ?? '—' }}</p>
                @endif
            </div>
            <div class="rounded-3xl border border-brand-200 bg-gradient-to-br from-brand-50 to-white p-5 shadow-card">
                <p class="text-sm font-medium text-brand-700">HR & compliance events</p>
                <p class="mt-2 text-3xl font-black text-brand-900">{{ count($calendarEvents) }}</p>
                <p class="mt-1 text-xs text-brand-600">Assessment periods, expirations, milestones</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card">
            <div class="border-b border-slate-200 bg-gradient-to-r from-brand-50 via-white to-white p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-brand-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-800">
                            <i class="fa-solid fa-calendar-days"></i>
                            Work schedule
                        </div>
                        <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">Shifts & assignments</h2>
                        <p class="mt-1 text-sm text-slate-500">Your facility work schedule will appear here when connected</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Coming soon</span>
                </div>
            </div>

            <div class="p-6">
                @if($hasShiftData)
                    {{-- Reserved for future shift data --}}
                @else
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center sm:p-10">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl shadow-sm ring-1 ring-slate-200">📅</div>
                    <p class="mt-4 text-lg font-bold text-slate-950">No shifts scheduled yet</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        When your facility enables scheduling, you’ll see today’s shift, weekly hours, and assignment details here.
                    </p>
                    <div class="mx-auto mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-left">
                            <p class="text-xs font-bold uppercase text-slate-400">Today</p>
                            <p class="mt-2 text-sm font-semibold text-slate-400">—</p>
                            <p class="text-xs text-slate-400">No shift</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-left">
                            <p class="text-xs font-bold uppercase text-slate-400">This week</p>
                            <p class="mt-2 text-sm font-semibold text-slate-400">0 hrs</p>
                            <p class="text-xs text-slate-400">Scheduled</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-left">
                            <p class="text-xs font-bold uppercase text-slate-400">Next shift</p>
                            <p class="mt-2 text-sm font-semibold text-slate-400">—</p>
                            <p class="text-xs text-slate-400">Not available</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card">
            <div class="border-b border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-950">Week at a glance</h2>
                <p class="mt-1 text-sm text-slate-500">HR reminders, assessment periods, and milestones on your calendar</p>
            </div>
            <div class="grid min-w-[640px] grid-cols-7 divide-x divide-slate-100 overflow-x-auto border-b border-slate-100">
                @foreach($weekDays as $day)
                <div class="min-w-[5rem] p-3 {{ $day['is_today'] ? 'bg-brand-50/50' : 'bg-white' }}">
                    <p class="text-center text-[10px] font-bold uppercase {{ $day['is_today'] ? 'text-brand-700' : 'text-slate-400' }}">{{ $day['label'] }}</p>
                    <p class="mt-1 text-center text-lg font-black {{ $day['is_today'] ? 'text-brand-800' : 'text-slate-700' }}">{{ $day['day_num'] }}</p>
                    @if($day['is_today'])
                    <p class="mt-0.5 text-center text-[9px] font-bold uppercase text-brand-600">Today</p>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="grid min-h-[8rem] min-w-[640px] grid-cols-7 divide-x divide-slate-100 overflow-x-auto">
                @foreach($weekDays as $day)
                <div class="min-w-[5rem] space-y-1.5 p-2 {{ $day['is_today'] ? 'bg-brand-50/30' : '' }}">
                    @forelse($day['events'] as $event)
                        @php $style = $eventTypeStyles[$event['type'] ?? 'info'] ?? $eventTypeStyles['info']; @endphp
                        <div class="rounded-lg border {{ $style['border'] }} {{ $style['bg'] }} p-1.5" title="{{ $event['description'] ?? '' }}">
                            <p class="truncate text-[10px] font-bold leading-tight {{ $style['text'] }}">{{ $event['title'] }}</p>
                        </div>
                    @empty
                        <p class="px-1 py-2 text-center text-[10px] text-slate-300">—</p>
                    @endforelse
                </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Upcoming</h2>
                    <p class="text-sm text-slate-500">Deadlines and reminders from your employee record</p>
                </div>
                <a href="{{ route('dashboard.index') }}" class="text-sm font-bold text-brand-600 hover:text-brand-700">← Dashboard</a>
            </div>

            @if(count($upcomingEvents) === 0)
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                <p class="text-3xl">✨</p>
                <p class="mt-3 font-semibold text-slate-900">Nothing on the calendar this week</p>
                <p class="mt-1 text-sm text-slate-500">Assessment periods and document expirations will show up here automatically.</p>
            </div>
            @else
            <ul class="space-y-3">
                @foreach($upcomingEvents as $event)
                    @php
                        $style = $eventTypeStyles[$event['type'] ?? 'info'] ?? $eventTypeStyles['info'];
                        $eventDate = !empty($event['date']) ? \Carbon\Carbon::parse($event['date']) : null;
                    @endphp
                    <li class="flex gap-4 rounded-2xl border border-slate-100 p-4 transition hover:border-brand-200 hover:bg-brand-50/30">
                        <div class="flex min-w-[3.5rem] shrink-0 flex-col items-center justify-center rounded-xl px-3 py-2 text-center {{ $style['bg'] }}">
                            @if($eventDate)
                            <span class="text-xs font-bold uppercase {{ $style['text'] }}">{{ $eventDate->format('M') }}</span>
                            <span class="text-xl font-black {{ $style['text'] }}">{{ $eventDate->format('j') }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="h-2 w-2 rounded-full {{ $style['dot'] }}"></span>
                                <p class="font-bold text-slate-950">{{ $event['title'] }}</p>
                            </div>
                            @if(!empty($event['description']))
                            <p class="mt-1 text-sm text-slate-600">{{ $event['description'] }}</p>
                            @endif
                            @if($eventDate)
                            <p class="mt-2 text-xs text-slate-400">{{ $eventDate->format('l, F j, Y') }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            @endif
        </section>
    </div>
</section>
@endsection
