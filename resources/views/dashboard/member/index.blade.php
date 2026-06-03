@extends('layouts.member-portal')

@php
    $mode = $roleDashboardMode ?? 'staff';
    $personaLabel = $dashboardPersonaLabel ?? 'Team Member';
    $scopeLabel = $dashboardScopeLabel ?? ($facilityName ?? '—');
    $facilityLabel = $dashboardFacilityName ?? ($facilityName ?? '—');
    $intro = $dashboardIntro ?? '';
    $kpis = $dashboardKpis ?? [];
    $actionQueue = $dashboardActionQueue ?? [];
    $awareness = $dashboardAwareness ?? [];
    $quickActions = $dashboardQuickActions ?? [];
    $myTasks = $dashboardMyTasks ?? [];
    $expiringDocuments = $dashboardExpiringDocuments ?? [];
    $expiringDocumentsTotal = $dashboardExpiringDocumentsTotal ?? count($expiringDocuments);
    $facilityDocumentsUrl = $dashboardFacilityDocumentsUrl ?? null;
    $assessmentsDue = $dashboardAssessmentsDue ?? [];
    $toneMap = [
        'brand' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-100'],
        'teal' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200'],
    ];
@endphp

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    {{-- Compact header --}}
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $todayLabel ?? now()->format('l, M j') }}</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-900 sm:text-xl">
                @if($mode === 'leadership')
                    {{ $scopeLabel }}
                @else
                    {{ $personaLabel }} dashboard
                @endif
            </h1>
            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-600 sm:text-sm">{{ $intro }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-teal-50 px-2.5 py-1 font-bold text-teal-800">{{ $personaLabel }}</span>
            @if($mode === 'leadership')
            <span class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">{{ $facilityLabel }}</span>
            @endif
            <a href="{{ route('settings.profile') }}"
               class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700 hover:shadow">My Profile</a>
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
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

    <div class="mt-3 grid gap-3 lg:grid-cols-12">
        @if($mode === 'leadership')
        {{-- Staff action queue --}}
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
                    <div>
                        <h2 class="text-sm font-black text-slate-900">Staff needing action</h2>
                        <p class="text-[11px] text-slate-500">Open items on your team’s training, documents, or credentials</p>
                    </div>
                    @if(\Illuminate\Support\Facades\Route::has('admin.training-management.index'))
                    <a href="{{ route('admin.training-management.index', array_filter(['department_id' => $dashboardDepartmentId ?? null, 'facility_id' => $dashboardFacilityId ?? null])) }}"
                       class="text-xs font-bold text-teal-700 hover:text-teal-900">Training queue →</a>
                    @endif
                </div>

                @if(count($actionQueue) === 0)
                <p class="px-4 py-8 text-center text-sm text-slate-500">No staff flagged in this scope right now.</p>
                @else
                <ul class="divide-y divide-slate-100">
                    @foreach($actionQueue as $row)
                    <li class="flex flex-wrap items-center gap-3 px-4 py-2.5 text-sm hover:bg-slate-50/80">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $row['name'] }}</span>
                                <span class="text-[11px] text-slate-500">{{ $row['position'] }}</span>
                                @if(($row['priority'] ?? '') === 'high')
                                <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-rose-800">Urgent</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-xs text-slate-600">{{ $row['summary'] }}</p>
                        </div>
                        <a href="{{ $row['manage_url'] }}"
                           class="shrink-0 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">Review</a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <aside class="space-y-3 lg:col-span-4">
            @if(count($awareness) > 0)
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Awareness</h3>
                <ul class="mt-2 space-y-2">
                    @foreach($awareness as $item)
                    @php
                        $aTone = match ($item['tone'] ?? 'slate') {
                            'amber' => 'bg-amber-50 text-amber-900',
                            'emerald' => 'bg-emerald-50 text-emerald-900',
                            'brand' => 'bg-teal-50 text-teal-900',
                            default => 'bg-slate-50 text-slate-700',
                        };
                    @endphp
                    <li class="flex gap-2 rounded-lg px-2.5 py-2 text-xs {{ $aTone }}">
                        <i class="fa-solid {{ $item['icon'] ?? 'fa-circle-info' }} mt-0.5 shrink-0"></i>
                        <span>{{ $item['message'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Quick actions</h3>
                <div class="mt-2 grid gap-1.5">
                    @foreach($quickActions as $action)
                    <a href="{{ $action['route'] }}" class="flex items-center gap-2.5 rounded-lg border border-slate-100 px-2.5 py-2 text-left hover:border-teal-200 hover:bg-teal-50/50">
                        <i class="fa-solid {{ $action['icon'] }} w-4 text-center text-sm text-teal-700"></i>
                        <span>
                            <span class="block text-xs font-bold text-slate-900">{{ $action['title'] }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $action['subtitle'] }}</span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <div class="mt-3 grid gap-3 lg:col-span-12 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
                    <div>
                        <h2 class="text-sm font-black text-slate-900">Documents, licenses & certifications</h2>
                        <p class="text-[11px] text-slate-500">
                            @if(($dashboardScopeType ?? '') === 'facility')
                                All employees at {{ $facilityLabel }} — expiring within 60 days
                            @else
                                Team uploads, credentials, and checklist items due within 60 days
                            @endif
                        </p>
                    </div>
                    @if($facilityDocumentsUrl)
                    <a href="{{ $facilityDocumentsUrl }}" class="text-[11px] font-bold text-teal-700 hover:text-teal-900">Facility documents →</a>
                    @endif
                </div>
                @if(count($expiringDocuments) === 0)
                <p class="px-4 py-6 text-center text-sm text-slate-500">No expiring documents, licenses, or certifications in this window.</p>
                @else
                @if($expiringDocumentsTotal > count($expiringDocuments))
                <p class="border-b border-slate-100 px-4 py-1.5 text-[11px] text-slate-500">
                    Showing {{ count($expiringDocuments) }} of {{ $expiringDocumentsTotal }} items (most urgent first).
                </p>
                @endif
                <ul class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @foreach($expiringDocuments as $doc)
                    <li class="flex flex-wrap items-center gap-2 px-4 py-2 text-sm {{ $doc['row_class'] ?? '' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $doc['employee_name'] }}</span>
                                <span class="text-[11px] text-slate-500">{{ $doc['position'] ?? '' }}</span>
                            </div>
                            <p class="mt-0.5 text-xs font-semibold text-slate-800">{{ $doc['document'] }}</p>
                            <p class="text-[11px] text-slate-500">{{ $doc['source'] ?? '' }} · {{ $doc['expires_on'] ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-bold {{ $doc['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $doc['status_label'] ?? '' }}
                        </span>
                        @if(!empty($doc['manage_url']))
                        <a href="{{ $doc['manage_url'] }}" class="shrink-0 text-[11px] font-bold text-teal-700 hover:text-teal-900">Open</a>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-2.5">
                    <h2 class="text-sm font-black text-slate-900">Appraisals & competencies due</h2>
                    <p class="text-[11px] text-slate-500">Assessment periods ending in the next 30 days</p>
                </div>
                @if(count($assessmentsDue) === 0)
                <p class="px-4 py-6 text-center text-sm text-slate-500">No performance or competency assessments due in this window.</p>
                @else
                <ul class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @foreach($assessmentsDue as $row)
                    <li class="flex flex-wrap items-center gap-2 px-4 py-2 text-sm {{ $row['row_class'] ?? '' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $row['employee_name'] }}</span>
                                <span class="text-[11px] text-slate-500">{{ $row['position'] ?? '' }}</span>
                            </div>
                            <p class="mt-0.5 text-xs font-semibold text-slate-800">{{ $row['type'] }}</p>
                            <p class="text-[11px] text-slate-500">Period ends {{ $row['due_on'] ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-bold {{ $row['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $row['status_label'] ?? '' }}
                        </span>
                        @if(!empty($row['manage_url']))
                        <a href="{{ $row['manage_url'] }}" class="shrink-0 text-[11px] font-bold text-teal-700 hover:text-teal-900">Open</a>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        @else
        {{-- Staff: personal work queue only --}}
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
        @endif
    </div>
</section>
@endsection
