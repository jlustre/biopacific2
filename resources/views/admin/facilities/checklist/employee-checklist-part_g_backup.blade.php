<div id="partG" class="tab-content">
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
            Rating Legend: E = Excellent (3) &nbsp;&nbsp;&nbsp; S = Satisfactory (2) &nbsp;&nbsp;&nbsp; U = Unsatisfactory (1) &nbsp;&nbsp;&nbsp; N = Not Applicable
        </div>
        @if($partGShowLicensedNurseGuidance)
            <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
                These competencies checklists are intended for all licensed nurses. If a section does not apply to the employee&rsquo;s position, check that checkbox <strong>Exclude</strong> so it is not counted in the assessment.
            </p>
            @include('admin.facilities.checklist.partg_sections.ln_competency_skills', [
                'lnCompetencyItems' => \App\Models\EmployeeCompetencyItem::where('section', 'LICENSED NURSE COMPETENCY SKILLS')->orderBy('order')->get()
            ])
            
            @livewire('admin.facilities.checklist.part-g-sections.blood-administration-competency', ['managerId' => 'partG'])
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

        @include('admin.facilities.checklist.partg_sections.competency-assessment-history')
    </div>
</div>
