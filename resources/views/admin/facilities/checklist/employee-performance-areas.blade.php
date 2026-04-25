<div class="mb-4">
    <h3 class="font-bold mb-2">PERFORMANCE AREAS</h3>
    <div class="italic text-xs text-gray-600 mb-2">
        Assess the employee’s knowledge, skills, and abilities, as outlined below. For each area, you can verify,
        revoke, or view details. Ratings and comments are editable and saved via AJAX.
    </div>
    @php
    use App\Models\EmployeePerformanceItem;
    $partFSections = EmployeePerformanceItem::orderBy('order')->get()->groupBy('section');
    @endphp
    @if(empty($selectedAssessmentPeriodId))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
        <strong>No assessment period selected.</strong> Please create or select an assessment period above to enable
        performance appraisal actions.
    </div>
    @else
    <div id="partFTableContainer">
        @foreach ($partFSections as $sectionLabel => $items)
        @php
        $docType = \App\Models\DocType::where('name', $sectionLabel)->first();
        $docTypeId = $docType ? $docType->id : null;
        @endphp
        <table class="min-w-full border text-xs md:text-sm mb-2">
            <thead>
                <tr class="bg-gray-100">
                    <th colspan="2" class="border px-2 py-1 text-left"><em>{{ $sectionLabel }}</em></th>
                    <th class="border px-2 py-1 text-center">RATING</th>
                    <th class="border px-2 py-1 text-center">ASSESSED DATE</th>
                    <th class="border px-2 py-1 text-center">ASSESSED BY</th>
                    <th class="border px-2 py-1 text-center">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $itemIdx => $item)
                @php
                $itemKey = 'F_' . md5($sectionLabel . '_' . $item->id);
                $empChecklist = $empPerformanceChecklist[$itemKey] ?? null;
                @endphp
                <tr>
                    <td class="border px-2 py-1 text-sm">{{ $item->label ?? '' }}</td>
                    <td class="border px-2 py-1 text-sm">{!! $item->item !!}</td>
                    <td class="border px-2 py-1 text-center">
                        @php
                        $ratingText = '';
                        if (isset($empChecklist['rating'])) {
                        if ($empChecklist['rating'] == 1) $ratingText = 'Below';
                        elseif ($empChecklist['rating'] == 2) $ratingText = 'Meets';
                        elseif ($empChecklist['rating'] == 3) $ratingText = 'Exceeds';
                        }
                        @endphp
                        {{ $ratingText }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $empChecklist['verified_dt'] ?? '' }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if(isset($users) && !empty($empChecklist['verified_by']))
                        {{ optional($users->firstWhere('id', $empChecklist['verified_by']))->name ??
                        $empChecklist['verified_by'] }}
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-red-600 underline mr-1 unverify-link cursor-pointer text-sm"
                            title="Revoke Assessment" data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}" data-doc-type-id="{{ $docTypeId }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link cursor-pointer text-sm"
                            title="View Assessment Details" data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}" data-doc-type-id="{{ $docTypeId }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline verify-link cursor-pointer" title="Assess Item"
                            data-item-key="{{ $itemKey }}" data-emp-id="{{ $employee->employee_num }}"
                            data-doc-type-id="{{ $docTypeId }}">Assess</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mb-2 flex flex-col md:flex-row items-center gap-2">
            <div class="flex flex-col flex-1 w-full">
                <label class="font-semibold mb-1 ml-2 md:mb-0 w-full">Comments:</label>
                <textarea class="border rounded w-full min-h-[40px] mt-1 md:mt-0 section-comment-textarea px-2 py-1"
                    rows="2" data-doc-type-id="{{ $docTypeId }}" data-section-label="{{ $sectionLabel }}"
                    data-emp-id="{{ $employee->employee_num }}" data-assessment-period-id="{{ $selectedAssessmentPeriodId }}"
                    placeholder="Enter comments for this section...">{{ $sectionComments[$docTypeId] ?? '' }}</textarea>
            </div>
            <div class="flex flex-col">
                <div class="flex flex-col">
                    <button type="button"
                        class="ml-0 md:ml-2 px-3 py-1 bg-teal-600 text-white rounded section-comment-save-btn cursor-pointer"
                        data-doc-type-id="{{ $docTypeId }}" data-section-label="{{ $sectionLabel }}"
                        data-emp-id="{{ $employee->employee_num }}"
                        data-assessment-period-id="{{ $selectedAssessmentPeriodId }}">Save</button>
                    <span class="section-comment-status text-xs ml-2"></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    <!-- Totals and overall rating can be dynamically calculated and displayed here as needed -->
</div>