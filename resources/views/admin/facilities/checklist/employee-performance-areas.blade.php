<div class="mb-4">
    <h3 class="font-bold mb-2">PERFORMANCE AREAS</h3>
    <div class="italic text-xs text-slate-600 mb-2">
        Assess the employee’s knowledge, skills, and abilities, as outlined below. For each area, you can verify,
        revoke, or view details. Ratings and comments are editable and saved via AJAX.
    </div>
    @php
    use App\Models\EmployeePerformanceItem;
    $partFSections = EmployeePerformanceItem::orderBy('order')->get()->groupBy('section');
    $hasAssessmentPeriod = !empty($selectedAssessmentPeriodId);
    $partFSelectedAssessment = $selectedPerformanceAssessment ?? ($assessment ?? null);
    $partFAssessmentLocked = !empty(optional($partFSelectedAssessment)->finalized);
    @endphp
    @if(!$hasAssessmentPeriod)
    <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 shadow-sm">
        <strong>No assessment period selected.</strong> Please create or select an assessment period above to enable
        performance appraisal actions.
    </div>
    @endif
    <div id="partFTableContainer">
        @foreach ($partFSections as $sectionLabel => $items)
        @php
        $docType = \App\Models\DocType::where('name', $sectionLabel)->first();
        $docTypeId = $docType ? $docType->id : null;
        @endphp
        <table class="min-w-full table-fixed overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs mb-2">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th colspan="2" class="border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide"><em>{{ $sectionLabel }}</em></th>
                    <th class="w-16 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">RATING</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ASSESSED BY</th>
                    <th class="w-16 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items->values() as $itemIdx => $item)
                @php
                $itemKey = 'F_' . $item->id;
                $legacyItemKey = 'F_' . md5($sectionLabel . '_' . $item->id);
                $empChecklist = $empPerformanceChecklist[$itemKey] ?? ($empPerformanceChecklist[$legacyItemKey] ?? null);
                $ratingText = match ($empChecklist['rating'] ?? null) {
                    'E' => 'Excellent',
                    'S' => 'Satisfactory',
                    'U' => 'Unsatisfactory',
                    'N' => 'Not Applicable',
                    default => '',
                };
                $rawItemText = trim(strip_tags($item->item ?? ''));
                preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
                $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
                $displayItem = ltrim(preg_replace('/^(-+)/', '', $rawItemText) ?? $rawItemText);
                $nextItem = $items->values()->get($itemIdx + 1);
                $nextRawItemText = trim(strip_tags($nextItem?->item ?? ''));
                preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
                $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
                $hasChildItems = $nextItem && $nextIndentLevel > $indentLevel;
                $collapsibleParentItems = ['PERINEAL CARE', 'CNA SKILLS CHECKLIST'];
                $isMainParentItem = $indentLevel === 0 && $hasChildItems && in_array($displayItem, $collapsibleParentItems, true);
                $indentClass = match ($indentLevel) {
                    1 => 'pl-6',
                    2 => 'pl-10',
                    default => '',
                };
                $rowClasses = $itemIdx % 2 === 0 ? 'bg-white text-slate-900' : 'bg-slate-50 text-slate-900';
                @endphp
                <tr class="{{ $rowClasses }} hover:bg-slate-100 transition-colors" data-summary-exclude="{{ $hasChildItems ? '1' : '0' }}" data-indent-level="{{ $indentLevel }}" data-has-child-items="{{ $hasChildItems ? '1' : '0' }}">
                    @if($isMainParentItem)
                    <td colspan="6" class="border border-slate-500 px-2 py-1.5 text-sm font-semibold">
                        <span class="inline-flex items-center gap-2">
                            <button type="button" class="hierarchy-toggle inline-flex h-5 w-5 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse child items">▲</button>
                            <span>{{ $displayItem }}</span>
                        </span>
                    </td>
                    @else
                    <td class="border border-slate-500 px-2 py-1.5 text-sm align-top">{{ $item->label ?? '' }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-sm align-top">
                        <span class="{{ $indentClass }} inline-flex items-center gap-2 text-[11px] leading-tight md:text-xs">
                            <span>{{ $displayItem }}</span>
                        </span>
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        {{ $ratingText }}
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        {{ $empChecklist['verified_dt'] ?? '' }}
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if(isset($users) && !empty($empChecklist['verified_by']))
                        {{ optional($users->firstWhere('id', $empChecklist['verified_by']))->name ??
                        $empChecklist['verified_by'] }}
                        @endif
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if(!$hasAssessmentPeriod)
                        <span class="text-gray-400 text-xs">Create or select period</span>
                        @elseif($partFAssessmentLocked)
                        @if(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-slate-700 underline ml-1 view-link cursor-pointer text-sm"
                            title="View Assessment Details" data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}" data-doc-type-id="{{ $docTypeId }}"
                            data-item-label="{{ strip_tags($item->item) }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">View</a>
                        @else
                        <span class="text-gray-400 text-xs">Locked</span>
                        @endif
                        @elseif(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-red-600 underline mr-1 unverify-link cursor-pointer text-xs"
                            title="Revoke Assessment" data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}" data-doc-type-id="{{ $docTypeId }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-slate-700 underline ml-1 view-link cursor-pointer text-xs"
                            title="View Assessment Details" data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}" data-doc-type-id="{{ $docTypeId }}"
                            data-item-label="{{ strip_tags($item->item) }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">View</a>
                        @else
                        <a href="#" class="text-slate-700 underline verify-link cursor-pointer" title="Assess Item"
                            data-item-key="{{ $itemKey }}" data-emp-id="{{ $employee->employee_num }}"
                            data-doc-type-id="{{ $docTypeId }}"
                            data-item-label="{{ strip_tags($item->item) }}"
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
            </tbody>
        </table>
        <div class="mb-2 rounded-md border border-slate-400 bg-slate-50 px-3 py-2 shadow-sm">
            <div class="flex flex-col md:flex-row items-center gap-2">
            <div class="flex flex-col flex-1 w-full">
                <label class="font-semibold text-[11px] text-slate-700 mb-1 ml-2 md:mb-0 w-full">Comments:</label>
                <textarea class="border border-slate-300 rounded-md w-full min-h-[40px] mt-1 md:mt-0 section-comment-textarea bg-white px-2 py-1 text-sm text-slate-900 placeholder:text-slate-500"
                    rows="2" data-doc-type-id="{{ $docTypeId }}" data-section-label="{{ $sectionLabel }}"
                    data-emp-id="{{ $employee->employee_num }}" data-assessment-period-id="{{ $selectedAssessmentPeriodId }}"
                    placeholder="{{ $hasAssessmentPeriod ? 'Enter comments for this section...' : 'Create or select an assessment period to enable comments.' }}"
                    @disabled(!$hasAssessmentPeriod || $partFAssessmentLocked)>{{ $sectionComments[$docTypeId] ?? '' }}</textarea>
            </div>
            <div class="flex flex-col">
                <div class="flex flex-col">
                    <button type="button"
                        class="ml-0 md:ml-2 px-3 py-1 bg-slate-700 text-white rounded-md section-comment-save-btn cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
                        data-doc-type-id="{{ $docTypeId }}" data-section-label="{{ $sectionLabel }}"
                        data-emp-id="{{ $employee->employee_num }}"
                        data-assessment-period-id="{{ $selectedAssessmentPeriodId }}"
                        @disabled(!$hasAssessmentPeriod || $partFAssessmentLocked)>Save</button>
                    <span class="section-comment-status text-xs ml-2"></span>
                </div>
            </div>
            </div>
        </div>
        @endforeach
    </div>

</div>