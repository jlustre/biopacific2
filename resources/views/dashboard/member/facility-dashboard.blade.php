@extends('layouts.member-portal')

@php
    $profile = $profile ?? [];
    $scope = $scope ?? [];
    $sections = $sections ?? [];
    $webContentMetrics = $webContentMetrics ?? [];
    $hrOperationsMetrics = $hrOperationsMetrics ?? [];
    $actionQueue = $actionQueue ?? [];
    $awareness = $awareness ?? [];
    $facilityLeadership = $facilityLeadership ?? [];
    $expiringDocuments = $expiringDocuments ?? [];
    $expiringDocumentsTotal = $expiringDocumentsTotal ?? count($expiringDocuments);
    $assessmentsDue = $assessmentsDue ?? [];
    $staffDirectoryByDepartment = $staffDirectoryByDepartment ?? [];
    $staffDirectoryCount = $staffDirectoryCount ?? collect($staffDirectoryByDepartment)->sum('count');
    $staffDirectoryOpenDefault = collect($staffDirectoryByDepartment)
        ->mapWithKeys(fn ($group) => [($group['key'] ?? 'department') => true])
        ->all();
    $hrManagementCards = $hrManagementCards ?? [];
    $hrManagementIntro = $hrManagementIntro ?? '';
    $hrQuickActions = $hrQuickActions ?? [];
    $facilityKey = $facilityKey ?? ($facility->slug ?? $facility->id);
    $facilitySwitchRoute = $facilitySwitchRoute ?? 'member.facility.dashboard';
    $facilities = $facilities ?? collect();
    $canSwitchFacility = $facilities->count() > 1;

    $metricToneMap = [
        'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-100'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'ring' => 'ring-sky-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-100'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'ring-violet-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-100'],
        'cyan' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'ring' => 'ring-cyan-100'],
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-100'],
        'brand' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-100'],
    ];
    $statusBadgeClass = match (strtolower((string) ($profile['status'] ?? 'active'))) {
        'active', 'open' => 'bg-emerald-400/20 text-emerald-100 ring-emerald-400/30',
        'inactive', 'closed' => 'bg-rose-400/20 text-rose-100 ring-rose-400/30',
        default => 'bg-amber-400/20 text-amber-100 ring-amber-400/30',
    };
@endphp

@section('content')
<section class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:px-6 lg:py-5">
    @if($canSwitchFacility)
    <div class="flex flex-wrap items-end gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 shadow-sm ring-1 ring-amber-100/80">
        <div class="min-w-[12rem] flex-1">
            <label for="facility-switch" class="text-[11px] font-bold uppercase tracking-wide text-amber-900/70">Facility</label>
            <select id="facility-switch" class="mt-0.5 w-full rounded-lg border-amber-200 bg-white text-sm focus:border-amber-400 focus:ring-amber-200"
                    onchange="if (this.value) window.location.href = this.value">
                @foreach($facilities as $f)
                <option value="{{ route($facilitySwitchRoute, ['facility' => $f->slug ?? $f->id]) }}"
                        @selected(($facility->id ?? null) === $f->id)>{{ $f->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    @if(($sections['facility_profile'] ?? true) && !empty($profile))
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-teal-900 via-teal-800 to-teal-950 p-6 text-white shadow-lg sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold uppercase tracking-wide text-teal-200/90">Facility Dashboard</p>
                <h1 class="mt-1 text-2xl font-black sm:text-3xl">{{ $profile['name'] ?? $facility->name }}</h1>
                <p class="mt-2 text-sm text-teal-100">{{ $scope['intro'] ?? '' }}</p>
                @if(!empty($profile['address']))
                <p class="mt-2 flex items-start gap-2 text-sm text-teal-100">
                    <i class="fa-solid fa-map-marker-alt mt-0.5 shrink-0"></i>
                    <span>{{ $profile['address'] }}</span>
                </p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    @if(!empty($profile['phone']))
                    <a href="tel:{{ preg_replace('/\D+/', '', $profile['phone']) }}" class="rounded-lg bg-white/10 px-3 py-1.5 ring-1 ring-white/15 hover:bg-white/20">
                        <i class="fa-solid fa-phone mr-1"></i>{{ $profile['phone'] }}
                    </a>
                    @endif
                    @if(!empty($profile['email']))
                    <a href="mailto:{{ $profile['email'] }}" class="rounded-lg bg-white/10 px-3 py-1.5 ring-1 ring-white/15 hover:bg-white/20">
                        <i class="fa-solid fa-envelope mr-1"></i>{{ $profile['email'] }}
                    </a>
                    @endif
                    @if(!empty($profile['public_url']))
                    <a href="{{ $profile['public_url'] }}" target="_blank" rel="noopener" class="rounded-lg bg-white/10 px-3 py-1.5 ring-1 ring-white/15 hover:bg-white/20">
                        <i class="fa-solid fa-globe mr-1"></i>Public site
                    </a>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                <span class="rounded-xl px-3 py-1.5 text-sm font-bold ring-1 {{ $statusBadgeClass }}">{{ $profile['status'] ?? 'Active' }}</span>
                <p class="text-xs text-teal-200">{{ $todayLabel ?? now()->format('l, F j, Y') }}</p>
                <a href="{{ route('settings.profile') }}" class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">My Profile</a>
            </div>
        </div>
    </div>
    @endif

    @if(($sections['web_content_metrics'] ?? true) && count($webContentMetrics) > 0)
    <div>
        <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">Public site & web content</h2>
        <div class="mt-2 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($webContentMetrics as $card)
            @php $tone = $metricToneMap[$card['tone'] ?? 'teal'] ?? $metricToneMap['teal']; @endphp
            <a href="{{ $card['route'] ?? '#' }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-teal-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tone['bg'] }} {{ $tone['text'] }} ring-1 {{ $tone['ring'] }}">
                        <i class="fa-solid {{ $card['icon'] ?? 'fa-chart-simple' }}"></i>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $card['value'] }}</p>
                <p class="text-[11px] text-slate-500">{{ $card['hint'] ?? '' }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(($sections['hr_operations_metrics'] ?? true) && count($hrOperationsMetrics) > 0)
    <div>
        <h2 class="text-sm font-black uppercase tracking-wide text-slate-500">HR & compliance</h2>
        <div class="mt-2 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($hrOperationsMetrics as $card)
            @php $tone = $metricToneMap[$card['tone'] ?? 'brand'] ?? $metricToneMap['brand']; @endphp
            <a href="{{ $card['route'] ?? '#' }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-teal-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tone['bg'] }} {{ $tone['text'] }} ring-1 {{ $tone['ring'] }}">
                        <i class="fa-solid {{ $card['icon'] ?? 'fa-users' }}"></i>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $card['value'] }}</p>
                <p class="text-[11px] text-slate-500">{{ $card['hint'] ?? '' }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(($sections['hr_management_cards'] ?? true))
        @include('dashboard.member.partials.facility-dashboard-hr-cards', [
            'hrManagementCards' => $hrManagementCards ?? [],
            'hrManagementIntro' => $hrManagementIntro ?? '',
        ])
    @endif

    @if(($sections['staff_action_queue'] ?? true) || (($sections['hr_quick_actions'] ?? false) && count($hrQuickActions) > 0))
    <div class="grid gap-3 lg:grid-cols-12">
        @if(($sections['staff_action_queue'] ?? true))
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
                    <div>
                        <h2 class="text-sm font-black text-slate-900">Staff needing action</h2>
                        <p class="text-[11px] text-slate-500">Training, documents, or credentials</p>
                    </div>
                    <a href="{{ $employeesListUrl ?? '#' }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">Full roster →</a>
                </div>
                @if(count($actionQueue) === 0)
                <p class="px-4 py-8 text-center text-sm text-slate-500">No staff flagged in this scope.</p>
                @else
                <ul class="divide-y divide-slate-100">
                    @foreach($actionQueue as $row)
                    <li class="flex flex-wrap items-center gap-3 px-4 py-2.5 text-sm hover:bg-slate-50/80">
                        <div class="min-w-0 flex-1">
                            <span class="font-bold text-slate-900">{{ $row['name'] }}</span>
                            <span class="ml-2 text-[11px] text-slate-500">{{ $row['position'] ?? '' }}</span>
                            <p class="mt-0.5 text-xs text-slate-600">{{ $row['summary'] ?? '' }}</p>
                        </div>
                        <a href="{{ $row['manage_url'] }}" class="shrink-0 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">Review</a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
        @endif

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

            @if(($sections['facility_leadership'] ?? true) && count($facilityLeadership) > 0)
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Facility leadership</h3>
                    @if(\Illuminate\Support\Facades\Route::has('admin.facility.leadership.edit') && auth()->user() && (auth()->user()->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']) || auth()->user()->can(\App\Support\Rbac\Permissions::ACCESS_HR_PORTAL)))
                    <a href="{{ auth()->user()->hasRole(['admin', 'super-admin', 'rdhr']) ? route('admin.facilities.leadership.index') : route('admin.facility.leadership.edit', ['facility' => $facilityKey]) }}"
                       class="text-[10px] font-bold text-teal-700 hover:text-teal-900">Manage</a>
                    @endif
                </div>
                <ul class="mt-1.5 max-h-64 divide-y divide-slate-100 overflow-y-auto">
                    @foreach($facilityLeadership as $leader)
                    <li class="flex items-center gap-1.5 py-1 text-[10px] leading-tight" title="{{ $leader['office'] }}">
                        <span class="w-[4.5rem] shrink-0 truncate font-bold uppercase tracking-wide text-slate-500">{{ $leader['abbrev'] }}</span>
                        <span class="min-w-0 flex-1 truncate {{ ($leader['vacant'] ?? false) ? 'font-medium text-slate-400' : 'font-semibold text-slate-900' }}">{{ $leader['name'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(($sections['hr_quick_actions'] ?? true) && count($hrQuickActions) > 0)
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Quick actions</h3>
                <div class="mt-2 grid gap-1.5">
                    @foreach($hrQuickActions as $action)
                    <a href="{{ $action['route'] }}" class="flex items-center gap-2.5 rounded-lg border border-slate-100 px-2.5 py-2 hover:border-teal-200 hover:bg-teal-50/50">
                        <i class="fa-solid {{ $action['icon'] }} w-4 text-center text-teal-700"></i>
                        <span>
                            <span class="block text-xs font-bold text-slate-900">{{ $action['title'] }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $action['subtitle'] }}</span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
    @endif

    @if(($sections['expiring_documents'] ?? true) || ($sections['assessments_due'] ?? true))
    <div class="grid gap-3 lg:grid-cols-2">
        @if($sections['expiring_documents'] ?? true)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Documents, licenses & certifications</h2>
                    <p class="text-[11px] text-slate-500">Expiring within 60 days — all staff in scope</p>
                </div>
                @if(!empty($facilityDocumentsUrl))
                <a href="{{ $facilityDocumentsUrl }}" class="text-[11px] font-bold text-teal-700 hover:text-teal-900">Facility documents →</a>
                @endif
            </div>
            @if(count($expiringDocuments) === 0)
            <p class="px-4 py-6 text-center text-sm text-slate-500">Nothing expiring in this window.</p>
            @else
            @if($expiringDocumentsTotal > count($expiringDocuments))
            <p class="border-b border-slate-100 px-4 py-1.5 text-[11px] text-slate-500">Showing {{ count($expiringDocuments) }} of {{ $expiringDocumentsTotal }} (most urgent first).</p>
            @endif
            <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                @foreach($expiringDocuments as $doc)
                <li class="flex flex-wrap items-center gap-2 px-4 py-2 text-sm {{ $doc['row_class'] ?? '' }}">
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-slate-900">{{ $doc['employee_name'] }}</span>
                        <p class="text-xs font-semibold text-slate-800">{{ $doc['document'] }}</p>
                        <p class="text-[11px] text-slate-500">{{ $doc['source'] ?? '' }} · {{ $doc['expires_on'] ?? '—' }}</p>
                    </div>
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $doc['badge_class'] ?? 'bg-slate-100' }}">{{ $doc['status_label'] ?? '' }}</span>
                    @if(!empty($doc['manage_url']))
                    <a href="{{ $doc['manage_url'] }}" class="text-[11px] font-bold text-teal-700">Open</a>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        @if($sections['assessments_due'] ?? true)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2.5">
                <h2 class="text-sm font-black text-slate-900">Appraisals & competencies due</h2>
                <p class="text-[11px] text-slate-500">Assessment periods ending in 30 days</p>
            </div>
            @if(count($assessmentsDue) === 0)
            <p class="px-4 py-6 text-center text-sm text-slate-500">None due in this window.</p>
            @else
            <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                @foreach($assessmentsDue as $row)
                <li class="flex flex-wrap items-center gap-2 px-4 py-2 text-sm {{ $row['row_class'] ?? '' }}">
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-slate-900">{{ $row['employee_name'] }}</span>
                        <p class="text-xs font-semibold text-slate-800">{{ $row['type'] }}</p>
                        <p class="text-[11px] text-slate-500">Period ends {{ $row['due_on'] ?? '—' }}</p>
                    </div>
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $row['badge_class'] ?? 'bg-slate-100' }}">{{ $row['status_label'] ?? '' }}</span>
                    @if(!empty($row['manage_url']))
                    <a href="{{ $row['manage_url'] }}" class="text-[11px] font-bold text-teal-700">Open</a>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif
    </div>
    @endif

    @if(($sections['staff_directory'] ?? true))
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm"
         x-data="{
            open: @js($staffDirectoryOpenDefault),
            toggle(key) { this.open[key] = !this.open[key]; },
            expandAll() { Object.keys(this.open).forEach(k => this.open[k] = true); },
            collapseAll() { Object.keys(this.open).forEach(k => this.open[k] = false); },
            allExpanded() { return Object.values(this.open).every(v => v); }
         }">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
            <div>
                <h2 class="text-sm font-black text-slate-900">Staff directory</h2>
                <p class="text-[11px] text-slate-500">
                    {{ $staffDirectoryCount }} employee(s)
                    @if(count($staffDirectoryByDepartment) > 0)
                    · {{ count($staffDirectoryByDepartment) }} {{ \Illuminate\Support\Str::plural('department', count($staffDirectoryByDepartment)) }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(count($staffDirectoryByDepartment) > 1)
                <button type="button"
                        @click="allExpanded() ? collapseAll() : expandAll()"
                        class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-50"
                        x-text="allExpanded() ? 'Collapse all' : 'Expand all'"></button>
                @endif
                <a href="{{ $employeesListUrl ?? '#' }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">Manage employees →</a>
            </div>
        </div>
        @if($staffDirectoryCount === 0)
        <p class="px-4 py-8 text-center text-sm text-slate-500">No active staff records for this facility.</p>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($staffDirectoryByDepartment as $group)
            @php $deptKey = $group['key'] ?? 'department'; @endphp
            <div>
                <button type="button"
                        @click="toggle('{{ $deptKey }}')"
                        class="flex w-full items-center gap-3 bg-slate-50/80 px-4 py-3 text-left transition hover:bg-slate-100">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-800">
                        <i class="fa-solid text-xs transition-transform duration-200"
                           :class="open['{{ $deptKey }}'] ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-slate-900">{{ $group['department'] }}</span>
                        <span class="block text-[11px] text-slate-500">{{ $group['count'] }} {{ \Illuminate\Support\Str::plural('employee', $group['count']) }}</span>
                    </span>
                    <span class="shrink-0 rounded-full bg-slate-200 px-2.5 py-0.5 text-[11px] font-bold text-slate-700">{{ $group['count'] }}</span>
                </button>
                <div x-show="open['{{ $deptKey }}']" x-cloak class="overflow-x-auto border-t border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-white text-left text-[11px] font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Position</th>
                                <th class="px-4 py-2">Contact</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($group['members'] as $person)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-2.5">
                                    <p class="font-bold text-slate-900">{{ $person['name'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $person['employee_num'] }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $person['position'] }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-600">
                                    @if(!empty($person['email']))
                                    <div><a href="mailto:{{ $person['email'] }}" class="text-teal-700 hover:underline">{{ $person['email'] }}</a></div>
                                    @endif
                                    @if(!empty($person['phone']))
                                    <div>{{ $person['phone'] }}</div>
                                    @endif
                                    @if(empty($person['email']) && empty($person['phone']))
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="{{ $person['edit_url'] }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</section>

@if(($sections['hr_management_cards'] ?? true) && collect($hrManagementCards ?? [])->contains(fn ($c) => ($c['type'] ?? '') === 'import'))
    @include('admin.facilities.partials.import-mapping-modal')
@endif
@endsection
