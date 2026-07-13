@php
    $trainingsCenter = $trainingsCenter ?? [];
    $facilityReport = $facilityTrainingsReport ?? null;
    $showFacilityTab = ($isFacilityTrainingsAdmin ?? false) && !empty($facilityReport);
    $groups = $trainingsCenter['groups'] ?? [];
    $buckets = $trainingsCenter['buckets'] ?? [
        ['key' => 'annual', 'label' => 'Period checklists', 'description' => 'Items tied to the selected assessment period (annual, every 2 years, etc.).', 'uses_assessment_period' => true],
        ['key' => 'upon_hiring', 'label' => 'Upon hiring checklists', 'description' => 'One-time onboarding and hire-period checklists.', 'uses_assessment_period' => false],
    ];
    $summary = $trainingsCenter['summary'] ?? ($stats ?? []);
    $hasEmployeeRecord = $trainingsCenter['has_employee_record'] ?? false;
    $hasPreEmployment = $trainingsCenter['has_pre_employment'] ?? false;
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
    $preEmploymentUrl = \Illuminate\Support\Facades\Route::has('pre-employment.portal') ? route('pre-employment.portal') : '#';
    $assessmentPeriods = $assessmentPeriods ?? collect();
    $selectedAssessmentPeriodId = $selectedAssessmentPeriodId ?? ($trainingsCenter['assessment_period_id'] ?? null);
    $groupsByBucket = collect($groups)->groupBy(fn ($group) => $group['bucket'] ?? 'annual');
    $hasAnyItems = collect($groups)->sum(
        fn ($g) => count($g['items'] ?? []) + count($g['history_documents'] ?? [])
    ) > 0;
@endphp

<section id="checklists" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card"
    x-data="memberChecklistSections()">
    <div class="border-b border-slate-200 bg-teal-50 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Checklists
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">My Checklists</h2>
                <p class="mt-1 text-sm text-slate-500">Annual checklists use one assessment period. Upon hiring checklists are one-time. Expand a section to view status, actions, or PDFs.</p>
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
                My checklists
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
        @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ session('error') }}</div>
        @endif

        @unless($hasEmployeeRecord || $hasPreEmployment)
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-2xl text-slate-500">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-950">No employee record linked</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Your account is not linked to an employee file yet. Checklists for trainings, orientation, competencies, and performance will appear here once HR links your profile.</p>
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

            @if($hasEmployeeRecord)
            <div class="mb-6 flex flex-wrap gap-2">
                @foreach($buckets as $bucket)
                    <a href="#checklist-bucket-{{ $bucket['key'] }}"
                       @click="openBucket('{{ $bucket['key'] }}')"
                       class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:border-teal-300 hover:text-teal-800">
                        {{ $bucket['label'] }}
                    </a>
                @endforeach
            </div>
            @endif

            @if(!$hasAnyItems)
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 text-2xl text-teal-700">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-950">No checklist items yet</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">When trainings, orientation, competencies, or performance appraisals are assigned to you, they will appear here.</p>
                </div>
            @else
                <div class="space-y-8">
                    @foreach($buckets as $bucket)
                        @php
                            $bucketKey = $bucket['key'];
                            $bucketGroups = ($groupsByBucket->get($bucketKey) ?? collect())->values();
                            $bucketItemCount = $bucketGroups->sum(fn ($g) => count($g['items'] ?? []) + count($g['history_documents'] ?? []));
                            $bucketStorageKey = 'bucket-'.$bucketKey;
                        @endphp

                        <div id="checklist-bucket-{{ $bucketKey }}" class="rounded-3xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                            @if(!empty($bucket['uses_assessment_period']) && $hasEmployeeRecord && $assessmentPeriods->isNotEmpty())
                            <form method="GET" action="{{ route('member.checklists') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl border border-teal-100 bg-white px-4 py-3">
                                <div class="min-w-[16rem] flex-1">
                                    <label for="assessment_period_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-teal-800">Assessment period</label>
                                    <select id="assessment_period_id" name="assessment_period_id" class="w-full rounded-xl border-teal-200 text-sm" onchange="this.form.submit()">
                                        @foreach($assessmentPeriods as $period)
                                            <option value="{{ $period->id }}" @selected((int) $selectedAssessmentPeriodId === (int) $period->id)>
                                                {{ $period->displayDateRange() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="text-xs text-teal-800">Applies to all annual checklists below.</p>
                            </form>
                            @endif

                            <button type="button"
                                class="mb-4 flex w-full items-start gap-3 rounded-2xl border border-teal-100 bg-white px-4 py-3 text-left hover:bg-teal-50/40"
                                @click="toggle('{{ $bucketStorageKey }}')"
                                :aria-expanded="isOpen('{{ $bucketStorageKey }}') ? 'true' : 'false'">
                                <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-teal-200 bg-teal-50 text-teal-700 transition-transform"
                                      :class="isOpen('{{ $bucketStorageKey }}') ? 'rotate-0' : '-rotate-90'">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-lg font-bold text-teal-800">{{ $bucket['label'] }}</span>
                                        <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700">
                                            {{ $bucketGroups->count() }} {{ \Illuminate\Support\Str::plural('section', $bucketGroups->count()) }}
                                        </span>
                                        <span class="rounded-full bg-teal-100/80 px-2 py-0.5 text-[11px] font-semibold text-teal-800">
                                            {{ $bucketItemCount }} tracked
                                        </span>
                                    </span>
                                    @if(!empty($bucket['description']))
                                        <span class="mt-1 block text-sm text-teal-700/80">{{ $bucket['description'] }}</span>
                                    @endif
                                </span>
                            </button>

                            <div x-show="isOpen('{{ $bucketStorageKey }}')" x-cloak class="ml-4 space-y-3 border-l-2 border-teal-200 pl-4 sm:ml-6 sm:pl-5">
                                @forelse($bucketGroups as $group)
                                    @include('dashboard.member.partials.checklists-group', [
                                        'group' => $group,
                                        'bucketKey' => $bucketKey,
                                    ])
                                @empty
                                    <p class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">No checklists in this group yet.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-6 rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    <strong>Trainings:</strong> open the module, start it, then mark complete &amp; submit for DSD / supervisor confirmation.
                    <strong>Orientation, competencies, and performance</strong> are started and completed by DSD or supervisors — this page shows your progress only.
                </p>
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

<script>
    function memberChecklistSections() {
        const storageKey = 'member-checklists-sections-v2';

        return {
            tab: 'mine',
            facilityFilter: '',
            sections: {},

            init() {
                try {
                    const saved = sessionStorage.getItem(storageKey);
                    this.sections = saved ? (JSON.parse(saved) || {}) : {};
                } catch (e) {
                    this.sections = {};
                }

                this.$nextTick(() => {
                    const hash = (window.location.hash || '').replace('#', '');
                    if (hash.startsWith('checklist-bucket-')) {
                        this.openBucket(hash.replace('checklist-bucket-', ''));
                    } else if (hash.startsWith('checklist-')) {
                        const parts = hash.replace('checklist-', '').split('-');
                        if (parts.length >= 2) {
                            this.openBucket(parts[0]);
                            const groupKey = parts.slice(1).join('-');
                            this.sections[groupKey] = true;
                            this.persist();
                        }
                    }
                });
            },

            isOpen(key) {
                return this.sections[key] === true;
            },

            toggle(key) {
                this.sections[key] = ! this.isOpen(key);
                this.persist();
            },

            openBucket(bucketKey) {
                this.sections['bucket-' + bucketKey] = true;
                this.persist();
            },

            persist() {
                try {
                    sessionStorage.setItem(storageKey, JSON.stringify(this.sections));
                } catch (e) {
                    // Ignore quota / private mode failures
                }
            },
        };
    }
</script>
