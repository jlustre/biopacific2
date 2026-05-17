@php
    $trainingsCenter = $trainingsCenter ?? [];
    $facilityReport = $facilityTrainingsReport ?? null;
    $showFacilityTab = ($isFacilityTrainingsAdmin ?? false) && !empty($facilityReport);
    $groups = $trainingsCenter['groups'] ?? [];
    $summary = $trainingsCenter['summary'] ?? ($stats ?? []);
    $hasEmployeeRecord = $trainingsCenter['has_employee_record'] ?? false;
    $hasPreEmployment = $trainingsCenter['has_pre_employment'] ?? false;
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
    $preEmploymentUrl = \Illuminate\Support\Facades\Route::has('pre-employment.portal') ? route('pre-employment.portal') : '#';
@endphp

<section id="trainings" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card" x-data="{ tab: 'mine', facilityFilter: '' }">
    <div class="border-b border-slate-200 bg-gradient-to-r from-teal-50 via-cyan-50/50 to-white p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    <i class="fa-solid fa-graduation-cap"></i>
                    Trainings
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">Learning & compliance</h2>
                <p class="mt-1 text-sm text-slate-500">Orientation, competency evaluations, and required training from your employee file</p>
            </div>
            @if($hasEmployeeRecord)
                <a href="{{ $employmentPortalUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                    <i class="fa-solid fa-briefcase"></i>
                    Employment portal
                </a>
            @elseif($hasPreEmployment)
                <a href="{{ $preEmploymentUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Pre-employment portal
                </a>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-2 border-b border-slate-200 pb-0">
            <button type="button"
                @click="tab = 'mine'"
                :class="tab === 'mine' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-2.5 text-sm font-bold transition">
                My trainings
            </button>
            @if($showFacilityTab)
                <button type="button"
                    @click="tab = 'facility'"
                    :class="tab === 'facility' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="border-b-2 px-4 py-2.5 text-sm font-bold transition">
                    Facility overview
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'mine'" class="p-6">
        @unless($hasEmployeeRecord || $hasPreEmployment)
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-2xl text-slate-500">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-950">No employee record linked</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Your account is not linked to an employee file yet. Training and orientation records will appear here once HR links your profile.</p>
            </div>
        @else
            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total tracked</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $summary['total'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Completed</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ $summary['completed'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-700">In progress</p>
                    <p class="mt-1 text-2xl font-black text-sky-900">{{ $summary['in_progress'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Needs signature</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $summary['pending_signature'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-600">Needs action</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $summary['needs_action'] ?? 0 }}</p>
                </div>
            </div>

            @php
                $hasAnyItems = collect($groups)->sum(fn ($g) => count($g['items'] ?? [])) > 0;
            @endphp

            @if(!$hasAnyItems)
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 text-2xl text-teal-700">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-950">No training records yet</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">When orientation, competency, or required training items are assigned to you, they will appear here.</p>
                </div>
            @else
                @foreach($groups as $group)
                    @php
                        $groupItems = $group['items'] ?? [];
                    @endphp
                    @if(count($groupItems) === 0)
                        @continue
                    @endif
                    <div class="mb-8 last:mb-0">
                        <div class="mb-4">
                            <h3 class="text-base font-bold text-slate-950">{{ $group['label'] ?? 'Training' }}</h3>
                            @if(!empty($group['description']))
                                <p class="mt-1 text-sm text-slate-500">{{ $group['description'] }}</p>
                            @endif
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full min-w-[720px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Due</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($groupItems as $training)
                                        @php
                                            $rowClass = match ($training['status'] ?? '') {
                                                'overdue', 'pending_signature' => 'bg-amber-50/40',
                                                'not_started' => 'bg-slate-50/60',
                                                default => '',
                                            };
                                            $daysLabel = isset($training['days_until'])
                                                ? ($training['days_until'] < 0
                                                    ? abs($training['days_until']) . 'd overdue'
                                                    : ($training['days_until'] === 0 ? 'Due today' : $training['days_until'] . 'd left'))
                                                : null;
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-slate-950">{{ $training['title'] ?? '—' }}</p>
                                                @if(!empty($training['subtitle']))
                                                    <p class="mt-0.5 text-xs text-slate-500">{{ $training['subtitle'] }}</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                @if(!empty($training['due_at_formatted']))
                                                    <span>{{ $training['due_at_formatted'] }}</span>
                                                    @if($daysLabel)
                                                        <span class="mt-0.5 block text-xs font-semibold text-slate-500">{{ $daysLabel }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $training['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                                                    {{ $training['status_label'] ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if(!empty($training['action_url']) && $training['action_url'] !== '#')
                                                    <a href="{{ $training['action_url'] }}" class="font-bold text-brand-600 hover:text-brand-700">
                                                        {{ $training['action_label'] ?? 'Open' }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if(($summary['needs_action'] ?? 0) > 0)
                    <p class="mt-6 rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Complete outstanding trainings through the employment portal or contact your supervisor for competency reviews.
                        @if($hasEmployeeRecord)
                            <a href="{{ $employmentPortalUrl }}" class="font-bold text-brand-600 hover:text-brand-700">Open employment portal</a>
                        @endif
                    </p>
                @endif
            @endif
        @endunless
    </div>

    @if($showFacilityTab)
        @php
            $facilitySummary = $facilityReport['summary'] ?? [];
            $facilityEmployees = $facilityReport['employees'] ?? [];
        @endphp
        <div x-show="tab === 'facility'" x-cloak class="p-6">
            <div class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    <span class="font-bold text-slate-700">{{ $facilityReport['facility']['name'] ?? 'Facility' }}</span>
                    — training & orientation compliance across active employees
                </p>
                @if(!empty($facilityReport['employees_list_url']))
                    <a href="{{ $facilityReport['employees_list_url'] }}" class="text-sm font-bold text-brand-600 hover:text-brand-700">
                        Manage all employees →
                    </a>
                @endif
            </div>

            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase text-slate-500">Employees</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $facilitySummary['total_employees'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">With issues</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $facilitySummary['employees_with_issues'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                    <p class="text-xs font-medium uppercase text-sky-700">Orientation gaps</p>
                    <p class="mt-1 text-2xl font-black text-sky-900">{{ $facilitySummary['incomplete_orientation'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">Unsigned competency</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $facilitySummary['unsigned_competency'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase text-rose-600">Training incomplete</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $facilitySummary['incomplete_training'] ?? 0 }}</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="facility-training-filter" class="sr-only">Filter employees</label>
                <input type="search" id="facility-training-filter" x-model="facilityFilter" placeholder="Filter by employee name…"
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100" />
            </div>

            @if(count($facilityEmployees) === 0)
                <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">No employees with active assignments at this facility.</p>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3">Position</th>
                                <th class="px-4 py-3">Orientation</th>
                                <th class="px-4 py-3">Competency</th>
                                <th class="px-4 py-3">Training</th>
                                <th class="px-4 py-3">Top issues</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($facilityEmployees as $row)
                                <tr x-show="!facilityFilter || ($el.dataset.name || '').includes(facilityFilter.toLowerCase())" data-name="{{ strtolower($row['name'] ?? '') }}">
                                    <td class="px-4 py-3 font-semibold text-slate-950">{{ $row['name'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['position'] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if(($row['incomplete_orientation'] ?? 0) > 0)
                                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">{{ $row['incomplete_orientation'] }}</span>
                                        @else
                                            <span class="text-emerald-600 text-xs font-bold">OK</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($row['unsigned_competency'] ?? 0) > 0)
                                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $row['unsigned_competency'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($row['incomplete_training'] ?? 0) > 0)
                                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">{{ $row['incomplete_training'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-3 text-slate-600">
                                        @if(!empty($row['top_issues']))
                                            <ul class="list-inside list-disc text-xs">
                                                @foreach($row['top_issues'] as $issue)
                                                    <li>{{ $issue }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-emerald-600">All clear</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if(!empty($row['manage_url']))
                                            <a href="{{ $row['manage_url'] }}" class="font-bold text-brand-600 hover:text-brand-700">Manage</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</section>
