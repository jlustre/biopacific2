@extends('layouts.dashboard', ['title' => 'Training Management'])

@section('content')
@php
    $facility = $facility ?? null;
    $summary = $summary ?? [];
    $employees = $employees ?? [];
    $programs = $programs ?? [];
    $competencyQueue = $competency_queue ?? [];
    $filters = $filters ?? [];
    $facilities = $facilities ?? collect();
    $canPickFacility = $canPickFacility ?? false;
    $generatedAt = $generated_at ?? now();
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-600">Facility workforce compliance</p>
            <h1 class="text-3xl font-bold text-gray-900">Training Management</h1>
            <p class="mt-2 max-w-2xl text-gray-600">
                Monitor orientation progress, competency sign-offs, and required training checklist completion across your facility workforce.
            </p>
            @if($facility)
            <p class="mt-2 text-sm text-gray-500">
                <i class="fas fa-building mr-1 text-teal-600"></i>
                {{ $facility['name'] }}
                <span class="mx-2 text-gray-300">·</span>
                Snapshot generated {{ $generatedAt->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
            </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            @if($facility && !empty($employees_list_url))
            <a href="{{ $employees_list_url }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2 font-semibold text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-users mr-2"></i> All employees
            </a>
            @endif
            <a href="{{ $checklist_items_url ?? route('admin.checklist-items.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2 font-semibold text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-list-check mr-2"></i> Checklist items
            </a>
            <a href="{{ route('user.hr-portal') }}"
               class="inline-flex items-center rounded-lg bg-teal-600 px-6 py-2 font-semibold text-white transition hover:bg-teal-700">
                <i class="fas fa-door-open mr-2"></i> HR Management
            </a>
        </div>
    </div>

    @if(!$facility)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
        <i class="fas fa-building-circle-exclamation mb-3 text-3xl text-amber-600"></i>
        <h2 class="text-lg font-bold text-amber-900">No facility selected</h2>
        <p class="mt-2 text-amber-800">Assign a facility to your account or choose a facility below to view training compliance.</p>
        @if($canPickFacility && $facilities->isNotEmpty())
        <form method="GET" action="{{ route('admin.training-management.index') }}" class="mx-auto mt-6 max-w-md">
            <label for="facility_id" class="mb-1 block text-left text-sm font-medium text-amber-900">Facility</label>
            <div class="flex gap-2">
                <select name="facility_id" id="facility_id" required
                        class="w-full rounded-lg border border-amber-200 px-4 py-2 focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                    <option value="">Select facility…</option>
                    @foreach($facilities as $f)
                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="shrink-0 rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-700">View</button>
            </div>
        </form>
        @endif
    </div>
    @else

    @if($canPickFacility && $facilities->count() > 1)
    <form method="GET" action="{{ route('admin.training-management.index') }}"
          class="flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4">
        <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
        <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}">
        <input type="hidden" name="department_id" value="{{ $filters['department_id'] ?? '' }}">
        <div class="min-w-[220px] flex-1">
            <label for="facility_switch" class="mb-1 block text-sm font-medium text-gray-700">Facility</label>
            <select name="facility_id" id="facility_switch" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-teal-500">
                @foreach($facilities as $f)
                <option value="{{ $f->id }}" @selected((int) ($filters['facility_id'] ?? $facility['id']) === (int) $f->id)>{{ $f->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
    @endif

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Active employees</p>
            <p class="mt-2 text-3xl font-black text-gray-900">{{ $summary['total_employees'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-500">Currently assigned to this facility</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Compliance rate</p>
            <p class="mt-2 text-3xl font-black text-emerald-800">{{ $summary['compliance_rate'] ?? 0 }}%</p>
            <p class="mt-1 text-xs text-emerald-700">{{ $summary['compliant_employees'] ?? 0 }} fully compliant</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-800">Needs attention</p>
            <p class="mt-2 text-3xl font-black text-amber-900">{{ $summary['employees_with_issues'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-amber-700">Employees with open training gaps</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-rose-700">Overdue items</p>
            <p class="mt-2 text-3xl font-black text-rose-800">{{ $summary['overdue_count'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-rose-600">Across orientation & required training</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-700"><i class="fas fa-clipboard-list"></i></span>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['incomplete_orientation'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Orientation gaps</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700"><i class="fas fa-signature"></i></span>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['unsigned_competency'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Pending competency signatures</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-700"><i class="fas fa-graduation-cap"></i></span>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['incomplete_training'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Incomplete required trainings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6" x-data="{ tab: 'employees' }">
        <div class="flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="tab = 'employees'"
                        :class="tab === 'employees' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition">By employee</button>
                <button type="button" @click="tab = 'programs'"
                        :class="tab === 'programs' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition">Training programs</button>
                <button type="button" @click="tab = 'competency'"
                        :class="tab === 'competency' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition">
                    Competency queue
                    @if(count($competencyQueue) > 0)
                    <span class="ml-1 rounded-full bg-white/30 px-2 py-0.5 text-xs">{{ count($competencyQueue) }}</span>
                    @endif
                </button>
            </div>
        </div>

        <div x-show="tab === 'employees'" x-cloak class="mt-6 space-y-6">
            <form method="GET" action="{{ route('admin.training-management.index') }}" class="grid gap-4 md:grid-cols-4">
                @if($canPickFacility)
                <input type="hidden" name="facility_id" value="{{ $facility['id'] }}">
                @endif
                <div class="md:col-span-2">
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Search employee</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Name, ID, position, or department…"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-teal-500">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All employees</option>
                        <option value="needs_attention" @selected(($filters['status'] ?? '') === 'needs_attention')>Needs attention</option>
                        <option value="compliant" @selected(($filters['status'] ?? '') === 'compliant')>Compliant only</option>
                    </select>
                </div>
                <div>
                    <label for="department_id" class="mb-1 block text-sm font-medium text-gray-700">Department</label>
                    <select name="department_id" id="department_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-teal-500">
                        <option value="">All departments</option>
                        @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" @selected((int) ($filters['department_id'] ?? 0) === (int) $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 md:col-span-4">
                    <button type="submit"
                            class="rounded-lg bg-gray-800 px-5 py-2 font-semibold text-white hover:bg-gray-900">
                        <i class="fas fa-filter mr-2"></i> Apply filters
                    </button>
                    <a href="{{ route('admin.training-management.index', $canPickFacility ? ['facility_id' => $facility['id']] : []) }}"
                       class="rounded-lg border border-gray-300 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900">Employee</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900">Position</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900">Department</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900">Orientation</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900">Competency</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900">Required</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($employees as $row)
                        @php
                            $statusBadge = match ($row['status'] ?? '') {
                                'compliant' => 'bg-emerald-100 text-emerald-800',
                                'overdue' => 'bg-rose-100 text-rose-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                            $statusLabel = match ($row['status'] ?? '') {
                                'compliant' => 'Compliant',
                                'overdue' => 'Overdue',
                                default => 'Needs attention',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $row['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $row['employee_num'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['position'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['department'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if(($row['incomplete_orientation'] ?? 0) > 0)
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $row['incomplete_orientation'] }}</span>
                                @else
                                <span class="text-emerald-600"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(($row['unsigned_competency'] ?? 0) > 0)
                                <span class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $row['unsigned_competency'] }}</span>
                                @else
                                <span class="text-emerald-600"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(($row['incomplete_training'] ?? 0) > 0)
                                <span class="inline-flex rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-800">{{ $row['incomplete_training'] }}</span>
                                @else
                                <span class="text-emerald-600"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $statusLabel }}</span>
                                @if(!empty($row['top_issues']))
                                <ul class="mt-1 list-inside list-disc text-xs text-gray-500">
                                    @foreach(array_slice($row['top_issues'], 0, 2) as $issue)
                                    <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ $row['manage_url'] }}"
                                   class="font-semibold text-teal-600 hover:text-teal-800">Manage</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                No employees match your filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="tab === 'programs'" x-cloak class="mt-6">
            <p class="mb-4 text-sm text-gray-600">Completion rates for checklist-based training programs (lowest completion shown first).</p>
            @if(count($programs) === 0)
            <p class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">No training program data available for this facility.</p>
            @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($programs as $program)
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $program['section'] }}</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $program['name'] }}</p>
                        </div>
                        <span class="shrink-0 text-lg font-black {{ ($program['completion_rate'] ?? 0) >= 90 ? 'text-emerald-600' : (($program['completion_rate'] ?? 0) >= 70 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $program['completion_rate'] }}%
                        </span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-teal-500 transition-all"
                             style="width: {{ min(100, max(0, $program['completion_rate'] ?? 0)) }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        {{ $program['complete'] }} complete · {{ $program['incomplete'] }} incomplete
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div x-show="tab === 'competency'" x-cloak class="mt-6">
            <p class="mb-4 text-sm text-gray-600">Competency assessments awaiting employee signature.</p>
            @if(count($competencyQueue) === 0)
            <p class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-gray-500">No pending competency signatures at this time.</p>
            @else
            <div class="space-y-3">
                @foreach($competencyQueue as $item)
                <div class="flex flex-col gap-3 rounded-xl border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $item['employee_name'] }}</p>
                        <p class="text-sm text-gray-600">{{ $item['position'] }} · {{ $item['period'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">Updated {{ $item['updated_at'] }} · Status: {{ str_replace('_', ' ', $item['status']) }}</p>
                    </div>
                    <a href="{{ $item['manage_url'] }}"
                       class="inline-flex shrink-0 items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                        Review assessment
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 to-white p-6">
        <h2 class="text-lg font-bold text-gray-900">Recommended actions</h2>
        <ul class="mt-4 grid gap-3 md:grid-cols-2">
            <li class="flex gap-3 rounded-xl border border-white bg-white/80 p-4 shadow-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-700"><i class="fas fa-user-check"></i></span>
                <div>
                    <p class="font-semibold text-gray-900">Follow up on orientation</p>
                    <p class="text-sm text-gray-600">Employees with incomplete Part E orientation should complete checklists in the employment portal.</p>
                </div>
            </li>
            <li class="flex gap-3 rounded-xl border border-white bg-white/80 p-4 shadow-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700"><i class="fas fa-file-signature"></i></span>
                <div>
                    <p class="font-semibold text-gray-900">Collect competency signatures</p>
                    <p class="text-sm text-gray-600">Review submitted assessments and obtain employee signatures before period close.</p>
                </div>
            </li>
            <li class="flex gap-3 rounded-xl border border-white bg-white/80 p-4 shadow-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-700"><i class="fas fa-folder-open"></i></span>
                <div>
                    <p class="font-semibold text-gray-900">Verify required training files</p>
                    <p class="text-sm text-gray-600">Ensure hazard communication, HIPAA, and other required documents are on file and verified.</p>
                </div>
            </li>
            <li class="flex gap-3 rounded-xl border border-white bg-white/80 p-4 shadow-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700"><i class="fas fa-cog"></i></span>
                <div>
                    <p class="font-semibold text-gray-900">Maintain checklist configuration</p>
                    <p class="text-sm text-gray-600">Update checklist items and position assignments when roles or regulations change.</p>
                </div>
            </li>
        </ul>
    </div>
    @endif
</div>
@endsection
