@extends('layouts.member-portal')

@php
    $toneBadge = fn (string $tone) => match ($tone) {
        'rose' => 'bg-rose-100 text-rose-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'brand' => 'bg-teal-100 text-teal-800',
        default => 'bg-slate-100 text-slate-700',
    };
    $toneDot = fn (string $tone) => match ($tone) {
        'rose' => 'bg-rose-500',
        'amber' => 'bg-amber-500',
        'brand' => 'bg-teal-500',
        default => 'bg-slate-400',
    };
@endphp

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Inbox</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-900 sm:text-xl">My Messages</h1>
            <p class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-600 sm:text-sm">
                Action alerts, assigned tasks, training completion notices, help requests, and feedback replies in one place.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('member.help.hr') }}"
               class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50">
                Contact HR
            </a>
            <a href="{{ route('member.help.support') }}"
               class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50">
                Technical Support
            </a>
            <a href="{{ route('member.feedback.create') }}"
               class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
                Report issue
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach($messageFilters as $filter)
            @php
                $isActive = ($activeMessageSource ?? 'all') === $filter['key'];
                $href = $filter['key'] === 'all'
                    ? route('member.messages')
                    : route('member.messages', ['source' => $filter['key']]);
            @endphp
            <a href="{{ $href }}"
               class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold transition {{ $isActive ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:bg-teal-50' }}">
                <span>{{ $filter['label'] }}</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $filter['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($messages->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl">💬</div>
                <p class="mt-4 text-sm font-semibold text-slate-800">You’re all caught up</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    No messages in this view. Portal alerts, open tasks, help tickets, and feedback updates will show here.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('member.tasks') }}" class="text-sm font-semibold text-teal-700 hover:underline">My Tasks →</a>
                    <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">Help requests →</a>
                    <a href="{{ route('member.feedback.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">Feedback →</a>
                </div>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($messages as $message)
                    @php
                        $tone = (string) ($message['tone'] ?? 'slate');
                        $occurred = $message['occurred_at'] ?? null;
                        $when = $occurred
                            ? \Illuminate\Support\Carbon::parse($occurred)->timezone(config('app.timezone'))->diffForHumans()
                            : null;
                        $from = $message['meta']['from'] ?? null;
                        $rowClass = 'block px-4 py-4 transition hover:bg-slate-50 sm:px-5'.(!empty($message['attention']) ? '' : ' opacity-90');
                    @endphp
                    @if(!empty($message['route']))
                        <a href="{{ $message['route'] }}" class="{{ $rowClass }}">
                    @else
                        <div class="{{ $rowClass }}">
                    @endif
                            <div class="flex gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $toneDot($tone) }}"
                                      title="{{ !empty($message['attention']) ? 'Needs attention' : 'Update' }}"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $toneBadge($tone) }}">
                                            {{ $message['category'] ?? $message['source'] }}
                                        </span>
                                        @if(!empty($message['attention']))
                                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-700">Needs attention</span>
                                        @endif
                                        @if($when)
                                            <span class="text-[11px] text-slate-400">{{ $when }}</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-1.5 text-sm font-bold text-slate-900 sm:text-base">{{ $message['title'] }}</h2>
                                    @if(!empty($message['body']))
                                        <p class="mt-1 text-xs leading-relaxed text-slate-600 sm:text-sm">{{ $message['body'] }}</p>
                                    @endif
                                    @if($from)
                                        <p class="mt-1 text-[11px] text-slate-500">From {{ $from }}</p>
                                    @endif
                                </div>
                                @if(!empty($message['route']))
                                    <span class="shrink-0 self-center text-xs font-semibold text-teal-700">
                                        {{ $message['action_label'] ?? 'Open' }} →
                                    </span>
                                @endif
                            </div>
                    @if(!empty($message['route']))
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
