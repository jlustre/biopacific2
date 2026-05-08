<div id="partG" class="tab-content hidden">
    <div class="overflow-x-auto">
        @php
        $partGSections = $employeeCompetencyItems->groupBy('section');
        $partGPosition = $employee->currentAssignment?->position?->title ?? 'No Position Assigned';
        $partGSubmissionStatus = $selectedCompetencyAssessment?->status;
        $partGAssessmentLocked = $partGSubmissionStatus === 'completed';
        $partGSubmissionStatusLabel = $partGSubmissionStatus ? ucwords(str_replace('_', ' ', (string) $partGSubmissionStatus)) : null;
           
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

        <div class="mb-4 rounded-md border border-slate-400 bg-slate-100 px-3 py-2 text-[11px] font-semibold text-slate-800 shadow-sm">
            Rating Legend: 3 = Excellent &nbsp;&nbsp;&nbsp; 2 = Satisfactory &nbsp;&nbsp;&nbsp; 1 = Unsatisfactory &nbsp;&nbsp;&nbsp; N = Not Applicable
        </div>
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
                <tr class="bg-slate-100 text-slate-900">
                    <td colspan="5" class="border border-slate-500 px-2 py-1.5 font-bold uppercase tracking-wide">{{ $sectionLabel }}</td>
                </tr>
                @foreach ($items->values() as $item)
                @php
                $itemKey = 'G_' . $item->id;
                $empChecklist = $empCompetencyAssessments[$itemKey] ?? null;
                $bloodTransfusionContinuationItem = '-If no transfusion reaction noted, dispose of blood bag and tubing in biohazard container.';
                $ratingText = $empChecklist['rating'] ?? '';
                $rowClasses = $loop->odd
                    ? 'bg-white text-slate-900'
                    : 'bg-slate-50 text-slate-900';
                preg_match('/^(-+)/', $item->item, $itemIndentMatches);
                $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
                $displayItem = ltrim(preg_replace('/^(-+)/', '', $item->item) ?? $item->item);
                $nextItem = $items->values()->get($loop->index + 1);
                preg_match('/^(-+)/', $nextItem?->item ?? '', $nextItemIndentMatches);
                $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
                $hasChildItems = $nextItem && $nextIndentLevel > $indentLevel;
                $collapsibleParentItems = ['PERINEAL CARE', 'CNA SKILLS CHECKLIST'];
                $isMainParentItem = $indentLevel === 0 && $hasChildItems && in_array($displayItem, $collapsibleParentItems, true);
                $isAssessableItem = !$hasChildItems;
                $indentClass = match ($indentLevel) {
                    1 => 'pl-8',
                    2 => 'pl-12',
                    default => 'pl-4',
                };
                @endphp
                @if($sectionLabel === 'BLOOD TRANSFUSION COMPETENCY' && $item->item === $bloodTransfusionContinuationItem)
                <tr class="bg-white text-slate-900">
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
                <tr class="{{ $rowClasses }} transition-colors hover:bg-slate-100" data-item-key="{{ $itemKey }}" data-summary-exclude="{{ $isAssessableItem ? '0' : '1' }}" data-indent-level="{{ $indentLevel }}" data-has-child-items="{{ $hasChildItems ? '1' : '0' }}" data-assessable-item="{{ $isAssessableItem ? '1' : '0' }}" data-current-rating="{{ $empChecklist['rating'] ?? '' }}">
                    @if($isMainParentItem)
                    <td colspan="5" class="border border-slate-500 px-2 py-1.5 font-semibold text-slate-900">
                        <span class="inline-flex items-center gap-2 text-sm">
                            <button type="button" class="hierarchy-toggle inline-flex h-5 w-5 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse child items">▲</button>
                            <span>{{ $displayItem }}</span>
                        </span>
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

        function getPartGBasePayload() {
            var requiredItemKeys = [];
            if (partGTableContainer) {
                partGTableContainer.querySelectorAll('tbody tr[data-assessable-item="1"]').forEach(function(row) {
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
                comments: partGComments ? partGComments.value : '',
                further_action_required: unsatisfactoryDetails ? unsatisfactoryDetails.value : '',
                reviewer_name: partGReviewerName ? partGReviewerName.value : '',
                reviewer_title: partGReviewerTitle ? partGReviewerTitle.value : '',
                review_date: partGReviewDate ? partGReviewDate.value : '',
                employee_name: partGEmployeeName ? partGEmployeeName.value : '',
                employee_title: partGEmployeeTitle ? partGEmployeeTitle.value : ''
            };
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
                var cells = row.querySelectorAll('td');
                if (cells.length !== 5) {
                    return;
                }

                if (parseCompetencyRating(row.getAttribute('data-current-rating')) === null) {
                    incompleteCount += 1;
                }
            });

            return incompleteCount;
        }

        window.updatePartGSummaryScores = updatePartGSummaryScores;

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

        syncUnsatisfactoryState();
        updatePartGSummaryScores();
    });
</script>