<div id="partG" class="tab-content hidden">
    <div class="overflow-x-auto">
        @php
        $partGSections = $employeeCompetencyItems->groupBy('section');
        $partGPosition = $employee->currentAssignment?->position?->title ?? 'No Position Assigned';
        $partGLicensedNurseGuidancePositions = [
            'Director of Nursing',
            'Registered Nurse',
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Charge Nurse',
            'IP Nurse',
        ];
        $partGShowLicensedNurseGuidance = in_array($partGPosition, $partGLicensedNurseGuidancePositions, true);
        $partGSubmissionStatus = $selectedCompetencyAssessment?->status;
        $partGAssessmentLocked = $partGSubmissionStatus === 'completed';
        $partGSubmissionStatusLabel = $partGSubmissionStatus ? ucwords(str_replace('_', ' ', (string) $partGSubmissionStatus)) : null;
        $partGDontIncludeSections = [
            'BLOOD ADMINISTRATION COMPETENCY',
            'BLOOD GLUCOSE MONITORING COMPETENCY',
            'TRACHEOSTOMY CARE COMPETENCY',
            'TREATMENT NURSE SKILLS COMPETENCY',
            'MEDICATION ADMINISTRATION COMPETENCY',
        ];
        $partGExcludedSectionLabels = collect($selectedCompetencyAssessment?->snapshot_json['excluded_section_labels'] ?? [])
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->values()
            ->all();
        $partGTracheostomyEquipmentChecks = collect($selectedCompetencyAssessment?->snapshot_json['tracheostomy_equipment_checks'] ?? [])
            ->map(fn ($itemLabel) => (string) $itemLabel)
            ->filter(fn ($itemLabel) => filled($itemLabel))
            ->values()
            ->all();
        $partGTracheostomyProcedureReviews = collect($selectedCompetencyAssessment?->snapshot_json['tracheostomy_procedure_reviews'] ?? [])
            ->mapWithKeys(fn ($rating, $procedureKey) => [(string) $procedureKey => strtoupper((string) $rating)])
            ->filter(fn ($rating) => in_array($rating, ['E', 'S', 'U'], true))
            ->all();
           
        @endphp
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-bold">COMPETENCIES CHECKLIST: {{ $partGPosition }}</h2>
            @if($partGSubmissionStatusLabel)
            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $partGAssessmentLocked ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">{{ $partGAssessmentLocked ? 'Read Only' : $partGSubmissionStatusLabel }}</span>
            @endif
        </div>
        <div class="mb-4 grid gap-3 xl:grid-cols-2 xl:items-stretch">
            <div>
                @include('admin.facilities.checklist.employee-assessment-subject-summary', [
                    'managerId' => 'partG',
                ])
            </div>

            <div>
                @include('admin.facilities.checklist.employee-assessment-period-manager', [
                    'managerId' => 'partG',
                    'contextLabel' => 'Competency Assessment',
                ])
            </div>
        </div>

        <div class="mb-2 rounded-md border border-slate-400 bg-slate-100 px-3 py-2 text-[11px] font-semibold text-slate-800 shadow-sm">
            Rating Legend: 3 = Excellent &nbsp;&nbsp;&nbsp; 2 = Satisfactory &nbsp;&nbsp;&nbsp; 1 = Unsatisfactory &nbsp;&nbsp;&nbsp; N = Not Applicable
        </div>
        @if($partGShowLicensedNurseGuidance)
        <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
            These competencies checklists are intended for all licensed nurses. If a section does not apply to the employee&rsquo;s position, check that checkbox <strong>Exclude</strong> so it is not counted in the assessment.
        </p>
        @endif
        @if($partGSubmissionStatusLabel)
        <div class="mb-4 rounded-md border {{ $partGAssessmentLocked ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-sky-300 bg-sky-50 text-sky-900' }} px-3 py-2 text-[11px] shadow-sm">
            <strong>Warning:</strong>
            @if($partGAssessmentLocked)
            A competency assessment already exists for this employee in the selected period with status <strong>{{ $partGSubmissionStatusLabel }}</strong>. This loaded assessment is read-only.
            @else
            A competency assessment already exists for this employee in the selected period with status <strong>{{ $partGSubmissionStatusLabel }}</strong>. The reviewer can still update it until it is completed.
            @endif
        </div>
        @endif
        <div id="partGTableContainer">
        <table class="min-w-full table-fixed overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">COMPETENCIES/ITEMS</th>
                    <th class="w-14 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">RATING</th>
                    <th class="w-20 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                    <th class="w-20 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ASSESSED BY</th>
                    <th class="w-16 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partGSections as $sectionLabel => $items)
                @php
                $partGSectionItems = $items->values();
                $partGSectionRenderItems = $partGSectionItems;
                $partGSectionTracheostomyProcedureRows = collect();

                if ($sectionLabel === 'TRACHEOSTOMY CARE COMPETENCY') {
                    $partGSectionTracheostomyProcedureRows = $partGSectionItems
                        ->filter(fn ($item) => preg_match('/^-\d+\./', (string) $item->item) === 1)
                        ->map(function ($item) {
                            $rawValue = (string) $item->item;
                            if (!preg_match('/^-(\d+)\.\s*(.+)$/', $rawValue, $matches)) {
                                return null;
                            }

                            $segments = array_map('trim', explode('||', $matches[2], 2));

                            return [
                                'key' => (string) $matches[1],
                                'text' => $segments[0] ?? '',
                                'note' => $segments[1] ?? null,
                            ];
                        })
                        ->filter()
                        ->values();

                    $partGSectionRenderItems = $partGSectionItems
                        ->reject(fn ($item) => preg_match('/^-\d+\./', (string) $item->item) === 1)
                        ->values();
                }
                @endphp
                <tr class="bg-slate-100 text-slate-900" data-section-header="1" data-section-label="{{ $sectionLabel }}">
                    <td colspan="5" class="border border-slate-500 px-2 py-1.5 font-bold uppercase tracking-wide">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-2 text-sm">
                                <button type="button" class="section-toggle inline-flex h-5 w-5 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse section items">▲</button>
                                <span>{{ $sectionLabel }}</span>
                            </span>
                            @if(in_array($sectionLabel, $partGDontIncludeSections, true))
                            <label class="partg-dont-include-control inline-flex items-center gap-2 text-[11px] font-medium normal-case tracking-normal text-slate-700 {{ $partGAssessmentLocked ? 'opacity-60' : '' }}">
                                <input type="checkbox" class="partg-section-dont-include-checkbox h-4 w-4 rounded border-slate-400 text-slate-700 focus:ring-slate-400" data-section-label="{{ $sectionLabel }}" data-locked="{{ $partGAssessmentLocked ? '1' : '0' }}" @checked(in_array($sectionLabel, $partGExcludedSectionLabels, true))>
                                <span>Exclude</span>
                            </label>
                            @endif
                        </div>
                    </td>
                </tr>
                @if($sectionLabel === 'TRACHEOSTOMY CARE COMPETENCY')
                <tr class="bg-white text-slate-900" data-section-body="1">
                    <td colspan="5" class="border border-slate-500 px-3 py-1.5 text-[11px] leading-snug md:text-xs">
                        <p>Tracheostomy care maintains a patient&rsquo;s airway by evacuating secretions, thereby preventing or reducing infections.</p>
                        <p class="mt-1 font-semibold">Rationale</p>
                        <p>Tracheostomy care maintains a patent airway by evacuating secretions, thereby preventing or reducing infections.</p>
                    </td>
                </tr>
                @endif
                @foreach ($partGSectionRenderItems as $item)
                @php
                $itemKey = 'G_' . $item->id;
                $empChecklist = $empCompetencyAssessments[$itemKey] ?? null;
                $bloodTransfusionTableInsertAfterItem = '-See blood transfusion policy for usual lengths of transfusion. (for whole blood 2-4 hours, must be infused within 4 hours of leaving Blood Bank)';
                $ratingText = $empChecklist['rating'] ?? '';
                $rowClasses = $loop->odd
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-50 text-slate-900';
                preg_match('/^(-+)/', $item->item, $itemIndentMatches);
                $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
                $displayItem = ltrim(preg_replace('/^(-+)/', '', $item->item) ?? $item->item);
                $nextItem = $partGSectionRenderItems->get($loop->index + 1);
                preg_match('/^(-+)/', $nextItem?->item ?? '', $nextItemIndentMatches);
                $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
                $hasChildItems = $nextItem && $nextIndentLevel > $indentLevel;
                $isTracheostomyEquipmentHeader = $sectionLabel === 'TRACHEOSTOMY CARE COMPETENCY' && $indentLevel === 1 && $hasChildItems;
                $isTracheostomyEquipmentItem = $sectionLabel === 'TRACHEOSTOMY CARE COMPETENCY' && $indentLevel >= 2;
                $isAssessableItem = !$hasChildItems && !$isTracheostomyEquipmentItem;
                $indentClass = match ($indentLevel) {
                    1 => 'pl-8',
                    2 => 'pl-12',
                    default => 'pl-4',
                };
                @endphp
                <tr class="{{ $rowClasses }} transition-colors hover:bg-slate-100" data-section-body="1" data-item-key="{{ $itemKey }}" data-summary-exclude="{{ $isAssessableItem ? '0' : '1' }}" data-indent-level="{{ $indentLevel }}" data-has-child-items="{{ $hasChildItems ? '1' : '0' }}" data-assessable-item="{{ $isAssessableItem ? '1' : '0' }}" data-current-rating="{{ $empChecklist['rating'] ?? '' }}">
                    @if($isTracheostomyEquipmentHeader)
                    <td colspan="5" class="border border-slate-500 px-2 py-1.5 align-top font-semibold">
                        <span class="{{ $indentClass }} inline-flex items-center gap-2 text-[11px] leading-tight md:text-xs">
                            <span>{{ $displayItem }}</span>
                        </span>
                    </td>
                    @elseif($isTracheostomyEquipmentItem)
                    <td colspan="4" class="border border-slate-500 px-2 py-1.5 align-top">
                        <span class="{{ $indentClass }} inline-flex items-center gap-2 text-[11px] leading-tight md:text-xs">
                            <span>{{ $displayItem }}</span>
                        </span>
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        <input
                            type="checkbox"
                            class="partg-trach-equipment-checkbox h-4 w-4 rounded border-slate-400 text-slate-700 focus:ring-slate-400"
                            data-item-label="{{ $item->item }}"
                            data-locked="{{ $partGAssessmentLocked ? '1' : '0' }}"
                            aria-label="Mark {{ $displayItem }} as checked"
                            @checked(in_array($item->item, $partGTracheostomyEquipmentChecks, true))
                            @disabled($partGAssessmentLocked)
                        >
                    </td>
                    @else
                    <td class="border border-slate-500 px-2 py-1.5 align-top">
                        <span class="{{ $indentClass }} inline-flex items-center gap-2 text-[11px] leading-tight md:text-xs">
                            <span>{{ $displayItem }}</span>
                        </span>
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top font-semibold whitespace-nowrap">{{ $ratingText }}</td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">{{ $empChecklist['verified_dt'] ?? '' }}</td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if(!empty($empChecklist['verified_by']))
                        {{ $empChecklist['verified_by_name'] ?? (optional($users->firstWhere('id', $empChecklist['verified_by']))->name ?? $empChecklist['verified_by']) }}
                        @endif
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if($partGAssessmentLocked)
                        @if(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-teal-600 underline ml-1 view-link cursor-pointer text-sm" title="View Assessment Details"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">View</a>
                        @else
                        <span class="text-slate-400">Locked</span>
                        @endif
                        @elseif(!$isAssessableItem && empty($empChecklist['verified_by']))
                        @elseif(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-red-600 underline mr-1 unverify-link cursor-pointer text-xs" title="Revoke Assessment"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link cursor-pointer text-xs" title="View Assessment Details"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline verify-link cursor-pointer text-sm" title="Assess Item"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">Assess</a>
                        @endif
                    </td>
                    @endif
                </tr>
                @if($sectionLabel === 'BLOOD ADMINISTRATION COMPETENCY' && $item->item === $bloodTransfusionTableInsertAfterItem)
                <tr class="bg-white text-slate-900" data-section-body="1">
                    <td colspan="5" class="border border-slate-500 px-2 py-2">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-slate-500 text-[10px] leading-tight text-slate-900 md:text-[11px]">
                                <thead>
                                    <tr class="bg-slate-200 text-center font-bold">
                                        <th colspan="3" class="border border-slate-500 px-1.5 py-1">Non-emergent Blood Component Transfusions</th>
                                    </tr>
                                    <tr class="bg-slate-50 text-center font-semibold">
                                        <th rowspan="3" class="border border-slate-500 px-1.5 py-1 align-bottom">Blood Component</th>
                                        <th colspan="2" class="border border-slate-500 px-1.5 py-1">Suggested Adult Flow Rate<br>Reference: AABB Technical Manual, 19th Edition, 2017, Bethesda, MD</th>
                                    </tr>
                                    <tr class="bg-slate-50 text-center font-semibold">
                                        <th class="border border-slate-500 px-1.5 py-1">First 15 minutes</th>
                                        <th class="border border-slate-500 px-1.5 py-1">After first 15 minutes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-500 px-1.5 py-1 font-semibold">Red Blood Cells (RBCs)</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">1-2 mL/min (60-120 mL/hr)</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">As rapidly as tolerated; approximately 4 mL/min or 240 mL/hour</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-500 px-1.5 py-1 font-semibold">Platelets</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">2-5 mL/min (120-300 mL/hr)</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">300 mL/hour or as tolerated</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-500 px-1.5 py-1 font-semibold">Plasma</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">2-5 mL/min (120-300 mL/hr)</td>
                                        <td class="border border-slate-500 px-1.5 py-1 text-center">As rapidly as tolerated; approximately 300 mL/hour</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-500 px-1.5 py-1 font-semibold">Cryoprecipitate</td>
                                        <td colspan="2" class="border border-slate-500 px-1.5 py-1 text-center font-semibold">As rapidly as tolerated</td>
                                    </tr>
                                    <tr class="bg-slate-100">
                                        <td colspan="3" class="border border-slate-500 px-1.5 py-1 text-center font-semibold">Note: For patients at risk for fluid overload, use slower flow.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif
                @if($sectionLabel === 'TRACHEOSTOMY CARE COMPETENCY' && $loop->last && $partGSectionTracheostomyProcedureRows->isNotEmpty())
                <tr class="bg-white text-slate-900" data-section-body="1">
                    <td colspan="5" class="border border-slate-500 px-2 py-2">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-slate-500 text-[10px] leading-tight text-slate-900 md:text-[11px]">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-900">
                                        <th rowspan="2" class="border border-slate-500 px-2 py-2 text-center font-bold">Procedure</th>
                                        <th colspan="3" class="border border-slate-500 px-2 py-2 text-center font-semibold">Check if</th>
                                    </tr>
                                    <tr class="bg-slate-50 text-slate-900">
                                        <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">E</th>
                                        <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">S</th>
                                        <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">U</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($partGSectionTracheostomyProcedureRows as $procedureRow)
                                    <tr>
                                        <td class="border border-slate-500 px-3 py-2">
                                            {{ $procedureRow['key'] }}. {{ $procedureRow['text'] }}
                                            @if(!empty($procedureRow['note']))
                                            <br><span class="italic">({{ $procedureRow['note'] }})</span>
                                            @endif
                                        </td>
                                        @foreach(['E', 'S', 'U'] as $procedureRating)
                                        <td class="border border-slate-500 px-2 py-2 text-center align-middle">
                                            <input
                                                type="radio"
                                                class="partg-trach-procedure-rating h-4 w-4 border-slate-400 text-slate-700 focus:ring-slate-400"
                                                name="partg-trach-procedure-{{ $procedureRow['key'] }}"
                                                value="{{ $procedureRating }}"
                                                data-procedure-key="{{ $procedureRow['key'] }}"
                                                data-locked="{{ $partGAssessmentLocked ? '1' : '0' }}"
                                                aria-label="Procedure {{ $procedureRow['key'] }} rating {{ $procedureRating }}"
                                                @checked(($partGTracheostomyProcedureReviews[$procedureRow['key']] ?? null) === $procedureRating)
                                                @disabled($partGAssessmentLocked)
                                            >
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
                @empty
                <tr>
                    <td colspan="5" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-700">
                        No competency checklist items apply to this employee's current position.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @include('admin.facilities.checklist.employee-assessment-summary-form', [
            'assessmentSummaryMode' => 'competency',
            'assessmentWord' => 'Competency',
            'assessmentSummaryTitle' => 'Competency Evaluation Summary',
            'assessmentSummaryDescription' => 'Review the calculated result, add notes, and complete the signatures.',
        ])

        <div class="mt-4 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment History</h3>
                    <p class="text-[11px] text-slate-700">Compare prior overall competency results by assessment period, and use the item View action above to inspect item-level history across periods.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 md:text-xs">
                    <thead>
                        <tr class="bg-slate-200 text-slate-900">
                            <th class="w-[24rem] border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">ASSESSMENT PERIOD</th>
                            <th class="w-24 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                            <th class="w-20 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ITEMS RATED</th>
                            <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">TOTAL</th>
                            <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">AVERAGE</th>
                            <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">OVERALL</th>
                            <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">STATUS</th>
                            <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencyAssessmentHistory as $historyRow)
                        <tr class="bg-white odd:bg-white even:bg-slate-50">
                            <td class="border border-slate-500 px-2 py-1.5 whitespace-nowrap">
                                <div class="font-semibold">{{ $historyRow['period_label'] }}</div>
                                @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                                <div class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Current Period</div>
                                @endif
                            </td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center">{{ $historyRow['assessment_date'] ?? '' }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center">{{ $historyRow['items_count'] }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['total_score'] }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['average_score'] }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['overall_rating'] }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['status'] ?? 'Draft' }}</td>
                            <td class="border border-slate-500 px-2 py-1.5 text-center">
                                @if(!empty($historyRow['pdf_available']) && !empty($historyRow['competency_assessment_id']))
                                <a href="{{ route('admin.employees.competency-assessment.pdf', $historyRow['competency_assessment_id']) }}" target="_blank" class="text-slate-700 underline">View PDF</a>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-700">
                                No prior competency assessment history is available for this employee yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    @media print {
        #partGTableContainer tr[data-print-excluded="1"] {
            display: none !important;
        }

        #partGTableContainer .partg-dont-include-control {
            display: none !important;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var overallRatingField = document.getElementById('partGOverallRating');
        var overallRatingCard = document.getElementById('partGOverallRatingCard');
        var unsatisfactoryDetails = document.getElementById('partGUnsatisfactoryDetails');
        var unsatisfactoryWrapper = document.getElementById('partGUnsatisfactoryDetailsWrapper');
        var totalScoreField = document.getElementById('partGTotalScore');
        var averageScoreField = document.getElementById('partGAverageScore');
        var partGTableContainer = document.getElementById('partGTableContainer');
        var partGSubmitAssessmentBtn = document.getElementById('partGSubmitAssessmentBtn');
        var partGSaveDraftBtn = document.getElementById('partGSaveDraftBtn');
        var partGSubmitAssessmentMessage = document.getElementById('partGSubmitAssessmentMessage');
        var partGComments = document.getElementById('partGComments');
        var partGReviewerName = document.getElementById('partGReviewerName');
        var partGReviewerTitle = document.getElementById('partGReviewerTitle');
        var partGReviewDate = document.getElementById('partGReviewDate');
        var partGEmployeeName = document.getElementById('partGEmployeeName');
        var partGEmployeeTitle = document.getElementById('partGEmployeeTitle');
        var partGEmployeeDate = document.getElementById('partGEmployeeDate');
        var partGWorkflowStatus = document.getElementById('partGWorkflowStatus');

        function parseCompetencyRating(rawRating) {
            var rating = (rawRating || '').trim().toUpperCase();
            if (rating === 'E' || rating === 'EXCELLENT' || rating === '3') return 3;
            if (rating === 'S' || rating === 'SATISFACTORY' || rating === '2') return 2;
            if (rating === 'U' || rating === 'UNSATISFACTORY' || rating === '1') return 1;
            return null;
        }

        function hasCompetencySelection(rawRating) {
            var rating = (rawRating || '').trim().toUpperCase();
            return ['E', 'EXCELLENT', '3', 'S', 'SATISFACTORY', '2', 'U', 'UNSATISFACTORY', '1', 'N', 'NOT APPLICABLE'].includes(rating);
        }

        function syncOverallEvaluation(average) {
            if (!overallRatingField || !overallRatingCard) {
                return;
            }

            var overall = '';
            if (average >= 2.5) {
                overall = 'Excellent';
            } else if (average >= 1.5) {
                overall = 'Satisfactory';
            } else if (average > 0) {
                overall = 'Unsatisfactory';
            }

            overallRatingField.value = overall;

            overallRatingCard.classList.remove('border-teal-400', 'bg-teal-100', 'border-amber-300', 'bg-amber-50', 'border-slate-400', 'bg-white');
            if (overall === 'Excellent' || overall === 'Satisfactory') {
                overallRatingCard.classList.add('border-teal-400', 'bg-teal-100');
            } else if (overall === 'Unsatisfactory') {
                overallRatingCard.classList.add('border-amber-300', 'bg-amber-50');
            } else {
                overallRatingCard.classList.add('border-slate-400', 'bg-white');
            }

            syncUnsatisfactoryState();
        }

        function getPartGExcludedSectionLabels() {
            if (!partGTableContainer) {
                return [];
            }

            return Array.from(partGTableContainer.querySelectorAll('.partg-section-dont-include-checkbox:checked'))
                .map(function(checkbox) {
                    return checkbox.getAttribute('data-section-label') || '';
                })
                .filter(function(sectionLabel) {
                    return sectionLabel !== '';
                });
        }

        function syncPartGExcludedRows() {
            if (!partGTableContainer) {
                return;
            }

            partGTableContainer.querySelectorAll('tbody tr[data-item-key]').forEach(function(row) {
                row.removeAttribute('data-parent-excluded');
                row.removeAttribute('data-print-excluded');
                row.classList.remove('opacity-60');

                var existingBadge = row.querySelector('.partg-excluded-badge');
                if (existingBadge) {
                    existingBadge.remove();
                }

                var assessLink = row.querySelector('.verify-link');
                if (assessLink) {
                    assessLink.classList.remove('pointer-events-none', 'opacity-50', 'text-slate-400', 'no-underline');
                    assessLink.classList.add('text-teal-600', 'underline', 'cursor-pointer');
                    assessLink.removeAttribute('aria-disabled');
                    assessLink.removeAttribute('tabindex');
                    assessLink.title = 'Assess Item';
                }

                row.querySelectorAll('.partg-trach-equipment-checkbox, .partg-trach-procedure-rating').forEach(function(input) {
                    input.disabled = input.getAttribute('data-locked') === '1';
                });
            });

            partGTableContainer.querySelectorAll('tbody tr[data-section-body="1"] .partg-trach-procedure-rating').forEach(function(input) {
                input.disabled = input.getAttribute('data-locked') === '1';
            });

            partGTableContainer.querySelectorAll('tbody tr[data-section-header="1"]').forEach(function(sectionHeaderRow) {
                var checkbox = sectionHeaderRow.querySelector('.partg-section-dont-include-checkbox');
                if (!checkbox || !checkbox.checked) {
                    return;
                }

                var nextRow = sectionHeaderRow.nextElementSibling;

                while (nextRow) {
                    if (nextRow.getAttribute('data-section-header') === '1') {
                        break;
                    }

                    nextRow.setAttribute('data-parent-excluded', '1');
                    nextRow.setAttribute('data-print-excluded', '1');
                    nextRow.classList.add('opacity-60');

                    var firstCellContent = nextRow.querySelector('td:first-child > span');
                    if (firstCellContent && !firstCellContent.querySelector('.partg-excluded-badge')) {
                        var excludedBadge = document.createElement('span');
                        excludedBadge.className = 'partg-excluded-badge inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800';
                        excludedBadge.textContent = 'Excluded';
                        firstCellContent.appendChild(excludedBadge);
                    }

                    var excludedAssessLink = nextRow.querySelector('.verify-link');
                    if (excludedAssessLink) {
                        excludedAssessLink.classList.remove('text-teal-600', 'underline', 'cursor-pointer');
                        excludedAssessLink.classList.add('pointer-events-none', 'opacity-50', 'text-slate-400', 'no-underline');
                        excludedAssessLink.setAttribute('aria-disabled', 'true');
                        excludedAssessLink.setAttribute('tabindex', '-1');
                        excludedAssessLink.title = 'Excluded items cannot be assessed while this section is marked Don\'t Include.';
                    }

                    nextRow.querySelectorAll('.partg-trach-equipment-checkbox, .partg-trach-procedure-rating').forEach(function(input) {
                        if (input.getAttribute('data-locked') === '1') {
                            return;
                        }

                        input.disabled = true;
                    });

                    nextRow = nextRow.nextElementSibling;
                }
            });
        }

        function updatePartGSummaryScores() {
            if (!totalScoreField || !averageScoreField || !partGTableContainer) {
                return;
            }

            var total = 0;
            var count = 0;

            partGTableContainer.querySelectorAll('tbody tr').forEach(function(row) {
                var cells = row.querySelectorAll('td');
                if (cells.length !== 5) {
                    return;
                }

                if (row.getAttribute('data-summary-exclude') === '1') {
                    return;
                }

                if (row.getAttribute('data-parent-excluded') === '1') {
                    return;
                }

                var numericRating = parseCompetencyRating(row.getAttribute('data-current-rating') || cells[1].textContent);
                if (numericRating === null) {
                    return;
                }

                total += numericRating;
                count += 1;
            });

            totalScoreField.value = String(total);
            var average = count ? (total / count) : 0;
            averageScoreField.value = average.toFixed(2);
            syncOverallEvaluation(average);
        }

        function syncUnsatisfactoryState() {
            if (!overallRatingField || !unsatisfactoryDetails || !unsatisfactoryWrapper) {
                return;
            }

            var enabled = overallRatingField.value === 'Unsatisfactory';
            unsatisfactoryDetails.disabled = !enabled;
            unsatisfactoryDetails.required = enabled;
            unsatisfactoryWrapper.classList.toggle('hidden', !enabled);
            unsatisfactoryWrapper.classList.toggle('opacity-60', !enabled);
            unsatisfactoryWrapper.classList.toggle('opacity-100', enabled);

            if (enabled) {
                unsatisfactoryDetails.focus();
                return;
            }

            unsatisfactoryDetails.value = '';
        }

        function setPartGSubmitMessage(type, message) {
            if (!partGSubmitAssessmentMessage) {
                return;
            }

            partGSubmitAssessmentMessage.className = 'mt-2 rounded-md border px-3 py-2 text-sm shadow-sm';

            if (!message) {
                partGSubmitAssessmentMessage.classList.add('hidden');
                partGSubmitAssessmentMessage.textContent = '';
                return;
            }

            if (type === 'error') {
                partGSubmitAssessmentMessage.classList.add('border-red-300', 'bg-red-50', 'text-red-800');
            } else {
                partGSubmitAssessmentMessage.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-800');
            }

            partGSubmitAssessmentMessage.classList.remove('hidden');
            partGSubmitAssessmentMessage.textContent = message;
        }

        function getCsrfToken() {
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : '';
        }

        function getPartGCurrentAssessmentPeriodId() {
            return window.selectedAssessmentPeriodId || @json($selectedAssessmentPeriodId) || '';
        }

        function getPartGExcludeStorageKey() {
            var assessmentPeriodId = getPartGCurrentAssessmentPeriodId();
            if (!assessmentPeriodId) {
                return '';
            }

            return ['partg-excluded-sections', @json($employee->employee_num), assessmentPeriodId].join(':');
        }

        function getPartGTracheostomyEquipmentStorageKey() {
            var assessmentPeriodId = getPartGCurrentAssessmentPeriodId();
            if (!assessmentPeriodId) {
                return '';
            }

            return ['partg-trach-equipment-checks', @json($employee->employee_num), assessmentPeriodId].join(':');
        }

        function getPartGTracheostomyProcedureStorageKey() {
            var assessmentPeriodId = getPartGCurrentAssessmentPeriodId();
            if (!assessmentPeriodId) {
                return '';
            }

            return ['partg-trach-procedure-reviews', @json($employee->employee_num), assessmentPeriodId].join(':');
        }

        function getPartGTracheostomyEquipmentChecks() {
            if (!partGTableContainer) {
                return [];
            }

            return Array.from(partGTableContainer.querySelectorAll('.partg-trach-equipment-checkbox:checked'))
                .map(function(checkbox) {
                    return checkbox.getAttribute('data-item-label') || '';
                })
                .filter(function(itemLabel) {
                    return itemLabel !== '';
                });
        }

        function getPartGTracheostomyProcedureReviews() {
            var reviews = {};
            if (!partGTableContainer) {
                return reviews;
            }

            partGTableContainer.querySelectorAll('.partg-trach-procedure-rating:checked').forEach(function(radio) {
                var procedureKey = radio.getAttribute('data-procedure-key') || '';
                var rating = (radio.value || '').toUpperCase();
                if (!procedureKey || ['E', 'S', 'U'].indexOf(rating) === -1) {
                    return;
                }

                reviews[procedureKey] = rating;
            });

            return reviews;
        }

        function persistPartGExcludedSectionsLocal() {
            var storageKey = getPartGExcludeStorageKey();
            if (!storageKey) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, JSON.stringify(getPartGExcludedSectionLabels()));
            } catch (error) {
                console.warn('Unable to persist Part G excluded sections locally.', error);
            }
        }

        function persistPartGTracheostomyEquipmentChecksLocal() {
            var storageKey = getPartGTracheostomyEquipmentStorageKey();
            if (!storageKey) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, JSON.stringify(getPartGTracheostomyEquipmentChecks()));
            } catch (error) {
                console.warn('Unable to persist Part G tracheostomy equipment checks locally.', error);
            }
        }

        function persistPartGTracheostomyProcedureReviewsLocal() {
            var storageKey = getPartGTracheostomyProcedureStorageKey();
            if (!storageKey) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, JSON.stringify(getPartGTracheostomyProcedureReviews()));
            } catch (error) {
                console.warn('Unable to persist Part G tracheostomy procedure reviews locally.', error);
            }
        }

        function loadPartGExcludedSectionsLocal() {
            var storageKey = getPartGExcludeStorageKey();
            if (!storageKey || !partGTableContainer) {
                return;
            }

            try {
                var rawValue = window.localStorage.getItem(storageKey);
                if (!rawValue) {
                    return;
                }

                var excludedSectionLabels = JSON.parse(rawValue);
                if (!Array.isArray(excludedSectionLabels)) {
                    return;
                }

                partGTableContainer.querySelectorAll('.partg-section-dont-include-checkbox').forEach(function(checkbox) {
                    checkbox.checked = excludedSectionLabels.includes(checkbox.getAttribute('data-section-label') || '');
                });
            } catch (error) {
                console.warn('Unable to load Part G excluded sections locally.', error);
            }
        }

        function loadPartGTracheostomyEquipmentChecksLocal() {
            var storageKey = getPartGTracheostomyEquipmentStorageKey();
            if (!storageKey || !partGTableContainer) {
                return;
            }

            try {
                var rawValue = window.localStorage.getItem(storageKey);
                if (!rawValue) {
                    return;
                }

                var checkedItems = JSON.parse(rawValue);
                if (!Array.isArray(checkedItems)) {
                    return;
                }

                partGTableContainer.querySelectorAll('.partg-trach-equipment-checkbox').forEach(function(checkbox) {
                    checkbox.checked = checkedItems.includes(checkbox.getAttribute('data-item-label') || '');
                });
            } catch (error) {
                console.warn('Unable to load Part G tracheostomy equipment checks locally.', error);
            }
        }

        function loadPartGTracheostomyProcedureReviewsLocal() {
            var storageKey = getPartGTracheostomyProcedureStorageKey();
            if (!storageKey || !partGTableContainer) {
                return;
            }

            try {
                var rawValue = window.localStorage.getItem(storageKey);
                if (!rawValue) {
                    return;
                }

                var reviews = JSON.parse(rawValue);
                if (!reviews || typeof reviews !== 'object') {
                    return;
                }

                partGTableContainer.querySelectorAll('.partg-trach-procedure-rating').forEach(function(radio) {
                    var procedureKey = radio.getAttribute('data-procedure-key') || '';
                    radio.checked = procedureKey !== '' && reviews[procedureKey] === radio.value;
                });
            } catch (error) {
                console.warn('Unable to load Part G tracheostomy procedure reviews locally.', error);
            }
        }

        function getPartGSectionRows(sectionHeaderRow) {
            var rows = [];
            if (!sectionHeaderRow) {
                return rows;
            }

            var nextRow = sectionHeaderRow.nextElementSibling;
            while (nextRow) {
                if (nextRow.getAttribute('data-section-header') === '1') {
                    break;
                }

                rows.push(nextRow);
                nextRow = nextRow.nextElementSibling;
            }

            return rows;
        }

        function getPartGActiveAssessedRows(sectionHeaderRow) {
            return getPartGSectionRows(sectionHeaderRow).filter(function(row) {
                if (!row.hasAttribute('data-item-key')) {
                    return false;
                }

                return hasCompetencySelection(row.getAttribute('data-current-rating'));
            });
        }

        function revokePartGAssessmentItem(row) {
            var itemKey = row ? row.getAttribute('data-item-key') : '';
            var empId = @json($employee->employee_num);
            var assessmentPeriodId = window.selectedAssessmentPeriodId || @json($selectedAssessmentPeriodId);
            var token = getCsrfToken();

            if (!itemKey || !empId || !assessmentPeriodId || !token) {
                return Promise.reject(new Error('Missing revoke payload data.'));
            }

            return fetch('/admin/employees/performance-assessment/revoke', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    employee_num: empId,
                    item_key: itemKey,
                    assessment_period_id: assessmentPeriodId
                })
            })
                .then(function(response) {
                    return response.text().then(function(rawText) {
                        var data = {};
                        try {
                            data = JSON.parse(rawText);
                        } catch (error) {
                            throw new Error('Failed to parse revoke response.');
                        }

                        if (!response.ok || !data.success || !data.data) {
                            throw new Error(data.message || 'Failed to revoke assessment.');
                        }

                        updateAssessmentRow(itemKey, empId, data.data.latest, data.data.history || []);
                    });
                });
        }

        function getPartGBasePayload() {
            var requiredItemKeys = [];
            if (partGTableContainer) {
                partGTableContainer.querySelectorAll('tbody tr[data-assessable-item="1"]').forEach(function(row) {
                    if (row.getAttribute('data-parent-excluded') === '1') {
                        return;
                    }

                    var itemKey = row.getAttribute('data-item-key');
                    if (itemKey) {
                        requiredItemKeys.push(itemKey);
                    }
                });
            }

            return {
                employee_num: @json($employee->employee_num),
                assessment_period_id: @json($selectedAssessmentPeriodId),
                required_item_keys: requiredItemKeys,
                excluded_section_labels: getPartGExcludedSectionLabels(),
                tracheostomy_equipment_checks: getPartGTracheostomyEquipmentChecks(),
                tracheostomy_procedure_reviews: getPartGTracheostomyProcedureReviews(),
                comments: partGComments ? partGComments.value : '',
                further_action_required: unsatisfactoryDetails ? unsatisfactoryDetails.value : '',
                reviewer_name: partGReviewerName ? partGReviewerName.value : '',
                reviewer_title: partGReviewerTitle ? partGReviewerTitle.value : '',
                review_date: partGReviewDate ? partGReviewDate.value : '',
                employee_name: partGEmployeeName ? partGEmployeeName.value : '',
                employee_title: partGEmployeeTitle ? partGEmployeeTitle.value : ''
            };
        }

        function persistPartGExcludedSections() {
            var csrfToken = getCsrfToken();
            var assessmentPeriodId = getPartGCurrentAssessmentPeriodId();
            if (!csrfToken) {
                return Promise.reject(new Error('CSRF token missing. Refresh the page and try again.'));
            }

            if (!assessmentPeriodId) {
                return Promise.reject(new Error('Please select an assessment period before excluding a section.'));
            }

            return fetch('{{ route('admin.employees.competency-assessment.preferences') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    employee_num: @json($employee->employee_num),
                    assessment_period_id: assessmentPeriodId,
                    excluded_section_labels: getPartGExcludedSectionLabels(),
                    tracheostomy_equipment_checks: getPartGTracheostomyEquipmentChecks(),
                    tracheostomy_procedure_reviews: getPartGTracheostomyProcedureReviews()
                })
            })
                .then(function(response) {
                    return response.json().then(function(data) {
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Failed to save section exclusions.');
                        }

                        return data;
                    });
                });
        }

        function setPartGActionButtonState(disabled) {
            [partGSaveDraftBtn, partGSubmitAssessmentBtn].forEach(function(button) {
                if (!button) {
                    return;
                }

                button.disabled = disabled;
                button.classList.toggle('opacity-60', disabled);
                button.classList.toggle('cursor-not-allowed', disabled);
            });
        }

        function getIncompletePartGAssessmentCount() {
            if (!partGTableContainer) {
                return 0;
            }

            var incompleteCount = 0;

            partGTableContainer.querySelectorAll('tbody tr[data-assessable-item="1"]').forEach(function(row) {
                if (row.getAttribute('data-parent-excluded') === '1') {
                    return;
                }

                var cells = row.querySelectorAll('td');
                if (cells.length !== 5) {
                    return;
                }

                if (!hasCompetencySelection(row.getAttribute('data-current-rating'))) {
                    incompleteCount += 1;
                }
            });

            return incompleteCount;
        }

        window.syncPartGExcludedRows = syncPartGExcludedRows;
        window.updatePartGSummaryScores = updatePartGSummaryScores;

        if (partGTableContainer) {
            partGTableContainer.addEventListener('change', function(event) {
                if (event.target.classList.contains('partg-trach-equipment-checkbox') || event.target.classList.contains('partg-trach-procedure-rating')) {
                    persistPartGTracheostomyEquipmentChecksLocal();
                    persistPartGTracheostomyProcedureReviewsLocal();
                    persistPartGExcludedSections()
                        .then(function() {
                            setPartGSubmitMessage('info', 'Tracheostomy checklist preferences saved.');
                        })
                        .catch(function(error) {
                            setPartGSubmitMessage('error', (error && error.message ? error.message : 'Failed to save tracheostomy checklist preferences.') + ' The change is kept locally for this employee and assessment period.');
                        });
                    return;
                }

                if (!event.target.classList.contains('partg-section-dont-include-checkbox')) {
                    return;
                }

                var checkbox = event.target;
                if (checkbox.dataset.busy === '1') {
                    return;
                }

                if (checkbox.getAttribute('data-locked') === '1') {
                    checkbox.checked = !checkbox.checked;
                    setPartGSubmitMessage('error', 'This competency assessment is already completed for the selected period and can no longer be changed.');
                    return;
                }

                var sectionHeaderRow = checkbox.closest('tr[data-section-header="1"]');

                if (!checkbox.checked) {
                    checkbox.dataset.busy = '1';
                    syncPartGExcludedRows();
                    updatePartGSummaryScores();
                    persistPartGExcludedSectionsLocal();
                    persistPartGExcludedSections()
                        .then(function() {
                            setPartGSubmitMessage('info', 'Section exclusion preference saved.');
                        })
                        .catch(function(error) {
                            setPartGSubmitMessage('error', (error && error.message ? error.message : 'Failed to save section exclusion preference.') + ' The change is kept locally for this employee and assessment period.');
                        })
                        .finally(function() {
                            delete checkbox.dataset.busy;
                        });
                    return;
                }

                var assessedRows = getPartGActiveAssessedRows(sectionHeaderRow);
                if (!assessedRows.length) {
                    checkbox.dataset.busy = '1';
                    syncPartGExcludedRows();
                    updatePartGSummaryScores();
                    persistPartGExcludedSectionsLocal();
                    persistPartGExcludedSections()
                        .then(function() {
                            setPartGSubmitMessage('info', 'Section exclusion preference saved.');
                        })
                        .catch(function(error) {
                            setPartGSubmitMessage('error', (error && error.message ? error.message : 'Failed to save section exclusion preference.') + ' The change is kept locally for this employee and assessment period.');
                        })
                        .finally(function() {
                            delete checkbox.dataset.busy;
                        });
                    return;
                }

                var sectionLabel = checkbox.getAttribute('data-section-label') || 'this section';
                var confirmed = confirm(
                    'This section already has ' + assessedRows.length + ' assessed item(s). Continuing will automatically revoke those assessed items under ' + sectionLabel + '. Do you want to continue?'
                );

                if (!confirmed) {
                    checkbox.checked = false;
                    return;
                }

                checkbox.dataset.busy = '1';
                setPartGSubmitMessage('info', 'Revoking assessed items under ' + sectionLabel + '...');

                assessedRows.reduce(function(promise, row) {
                    return promise.then(function() {
                        return revokePartGAssessmentItem(row);
                    });
                }, Promise.resolve())
                    .then(function() {
                        syncPartGExcludedRows();
                        updatePartGSummaryScores();
                        persistPartGExcludedSectionsLocal();
                        return persistPartGExcludedSections().then(function() {
                            setPartGSubmitMessage('info', 'Assessed items under ' + sectionLabel + ' were revoked and excluded successfully.');
                        });
                    })
                    .catch(function(error) {
                        checkbox.checked = false;
                        syncPartGExcludedRows();
                        updatePartGSummaryScores();
                        setPartGSubmitMessage('error', error && error.message ? error.message : 'Failed to revoke assessed items for this section.');
                    })
                    .finally(function() {
                        delete checkbox.dataset.busy;
                    });
            });
        }

        if (partGTableContainer && typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function() {
                updatePartGSummaryScores();
            });

            observer.observe(partGTableContainer, {
                childList: true,
                subtree: true,
                characterData: true,
            });
        }

        if (partGSubmitAssessmentBtn) {
            partGSubmitAssessmentBtn.addEventListener('click', function() {
                var workflowStatus = partGWorkflowStatus ? partGWorkflowStatus.value : 'draft';
                var incompleteCount = getIncompletePartGAssessmentCount();

                if ((workflowStatus === 'draft' || !workflowStatus) && incompleteCount > 0) {
                    setPartGSubmitMessage('error', 'Complete all competency assessments before submitting. ' + incompleteCount + ' item(s) still need a rating.');
                    return;
                }

                if (overallRatingField && overallRatingField.value === 'Unsatisfactory' && unsatisfactoryDetails && !unsatisfactoryDetails.value.trim()) {
                    setPartGSubmitMessage('error', 'Describe the further action required before submitting an unsatisfactory assessment.');
                    unsatisfactoryDetails.focus();
                    return;
                }

                var csrfToken = getCsrfToken();
                if (!csrfToken) {
                    setPartGSubmitMessage('error', 'CSRF token missing. Refresh the page and try again.');
                    return;
                }

                setPartGActionButtonState(true);
                setPartGSubmitMessage('', '');

                var requestUrl = '{{ route('admin.employees.competency-assessment.submit') }}';
                var payload = getPartGBasePayload();

                if (workflowStatus === 'for_employee_signature') {
                    if (!partGEmployeeDate || !partGEmployeeDate.value) {
                        setPartGSubmitMessage('error', 'Employee date is required before signing.');
                        setPartGActionButtonState(false);
                        return;
                    }

                    requestUrl = '{{ route('admin.employees.competency-assessment.employee-sign') }}';
                    payload = {
                        employee_num: @json($employee->employee_num),
                        assessment_period_id: @json($selectedAssessmentPeriodId),
                        employee_name: partGEmployeeName ? partGEmployeeName.value : '',
                        employee_title: partGEmployeeTitle ? partGEmployeeTitle.value : '',
                        employee_date: partGEmployeeDate.value
                    };
                } else if (workflowStatus === 'for_reviewer_signature') {
                    requestUrl = '{{ route('admin.employees.competency-assessment.reviewer-sign') }}';
                    payload = {
                        employee_num: @json($employee->employee_num),
                        assessment_period_id: @json($selectedAssessmentPeriodId),
                        reviewer_name: partGReviewerName ? partGReviewerName.value : '',
                        reviewer_title: partGReviewerTitle ? partGReviewerTitle.value : '',
                        review_date: partGReviewDate ? partGReviewDate.value : ''
                    };
                }

                fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function(result) {
                        if (!result.ok || !result.data.success) {
                            setPartGSubmitMessage('error', result.data.message || 'Failed to submit competency assessment.');
                            return;
                        }

                        setPartGSubmitMessage('info', result.data.message || 'Competency assessment submitted successfully.');
                        window.location.reload();
                    })
                    .catch(function() {
                        setPartGSubmitMessage('error', 'Failed to submit competency assessment.');
                    })
                    .finally(function() {
                        setPartGActionButtonState(false);
                    });
            });
        }

        if (partGSaveDraftBtn) {
            partGSaveDraftBtn.addEventListener('click', function() {
                var csrfToken = getCsrfToken();
                if (!csrfToken) {
                    setPartGSubmitMessage('error', 'CSRF token missing. Refresh the page and try again.');
                    return;
                }

                setPartGActionButtonState(true);
                setPartGSubmitMessage('', '');

                fetch('{{ route('admin.employees.competency-assessment.draft') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(getPartGBasePayload())
                })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function(result) {
                        if (!result.ok || !result.data.success) {
                            setPartGSubmitMessage('error', result.data.message || 'Failed to save competency assessment draft.');
                            return;
                        }

                        setPartGSubmitMessage('info', result.data.message || 'Competency assessment draft saved successfully.');
                        window.location.reload();
                    })
                    .catch(function() {
                        setPartGSubmitMessage('error', 'Failed to save competency assessment draft.');
                    })
                    .finally(function() {
                        setPartGActionButtonState(false);
                    });
            });
        }

        loadPartGExcludedSectionsLocal();
    loadPartGTracheostomyEquipmentChecksLocal();
    loadPartGTracheostomyProcedureReviewsLocal();
        syncUnsatisfactoryState();
        syncPartGExcludedRows();
        updatePartGSummaryScores();
    });
</script>