<div id="partE" class="tab-content hidden">
    <div class="overflow-x-auto">
        @php
        $partEItems = $checklistItems
            ->where('section', 'PART E')
            ->reject(fn ($item) => (int) ($item->doc_type_id ?? 0) === 5)
            ->sortBy('order');
        $partEChildItems = [
            'Morning Meeting/Ambassador Rounds',
            'Mini/Thorough Utilization Review',
            'Risk Meetings',
            'Computer software and available reports',
            'Voicemail',
            'Paging system',
            'E-mail',
            'Electronic Risk Management Assistant (ERMA)',
            'Omniview',
            'MatrixCare/POC',
            'Resident Care Manuals (RC 1, RC 2, RC 3)',
            'Infection Control',
            'Updated Clinical Manual',
            'Lippincott Nursing Procedures Book',
            'Dietary P&P and Manual',
            'Operations Manuals (Ops 1, Ops 2, Ops 3, Ops 4, Ops 5)',
            'Anderson\'s Medical Records Manual',
            'Omnicare Policy and Procedure Manual for Skilled Nursing',
            'Omnicare IV Policy and Procedure Manual',
            'Run for Restorative Manual',
            'Annual',
            'Title 22',
            'Life Safety Code',
            'PPD/Staffing Audit',
            'Risk-at least weekly',
            'Falling Stars Program-weekly',
            'Pharmacy/Psychotropics-monthly',
            'Infection Control-monthly, with sub-committee at least quarterly',
            'QA&A/QAPI-monthly',
            'Policy and Procedure-annually and whenever necessary',
            'Safety Committee-monthly',
            'Administrative/Staff Meetings-monthly',
            'Daily Morning Meeting with mini-UR',
            'Weekly thorough UR',
            'Restorative Nursing Program Meeting-weekly/monthly',
        ];
        @endphp
        <h2 class="text-xl font-bold mb-4">ORIENTATION CHECKLIST: {{ $employee->currentAssignment?->position?->title ?? 'No Position Assigned' }}</h2>
        <table class="min-w-full table-fixed overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">ORIENTATION ITEMS</th>
                    <th class="w-28 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMATION</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMED DATE</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">EXPIRED DATE</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMED BY</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partEItems->values() as $itemIdx => $item)
                @php
                $empChecklist = $resolveChecklistEntry($item);
                $checklistKey = $resolveChecklistKey($item);
                $isChildItem = in_array($item->name, $partEChildItems, true);
                $displayName = $item->name;
                $displayIndentClass = '';
                $itemLevel = 0;

                if (str_starts_with($item->name, '--')) {
                    $displayName = ltrim(substr($item->name, 2));
                    $displayIndentClass = 'inline-block pl-8';
                    $itemLevel = 2;
                } elseif (str_starts_with($item->name, '-')) {
                    $displayName = ltrim(substr($item->name, 1));
                    $displayIndentClass = 'inline-block pl-4';
                    $itemLevel = 1;
                } elseif ($isChildItem) {
                    $displayIndentClass = 'inline-block pl-4';
                    $itemLevel = 1;
                }
                $nextItem = $partEItems->values()->get($itemIdx + 1);
                $nextIsChildItem = $nextItem ? in_array($nextItem->name, $partEChildItems, true) : false;
                $nextItemLevel = 0;
                if ($nextItem) {
                    if (str_starts_with($nextItem->name, '--')) {
                        $nextItemLevel = 2;
                    } elseif (str_starts_with($nextItem->name, '-')) {
                        $nextItemLevel = 1;
                    } elseif ($nextIsChildItem) {
                        $nextItemLevel = 1;
                    }
                }
                $hasChildItems = $nextItem && $nextItemLevel > $itemLevel;
                $rowClasses = $loop->odd ? 'bg-white text-slate-900' : 'bg-slate-50 text-slate-900';
                @endphp
                <tr class="{{ $rowClasses }} hover:bg-slate-100 transition-colors" data-doc-type-id="{{ $item->doc_type_id ?? 5 }}" data-item-name="{{ $item->name }}"
                    data-item-level="{{ $itemLevel }}" data-has-child-items="{{ $hasChildItems ? '1' : '0' }}" data-item-disabled="{{ !empty($item->disabled) ? 1 : 0 }}">
                    <td class="border border-slate-500 px-2 py-1.5 align-top @if(isset($item->disabled) && $item->disabled) line-through @endif">
                        <span class="text-[11px] leading-tight {{ $displayIndentClass }} {{ $itemLevel === 0 ? 'font-bold' : '' }}">
                            @if($hasChildItems)
                            <button type="button" class="partE-hierarchy-toggle mr-2 inline-flex h-5 w-5 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse child items">▲</button>
                            @endif
                            <span>{{ $displayName }}</span>
                        </span>
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 align-top text-center whitespace-nowrap">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;" @if(isset($item->disabled) && $item->disabled)
                        disabled @endif>
                        @if(empty($item->disabled))
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Click to unconfirm Item"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}">Confirmed</a>
                        <span>|</span>
                        <a href="#" class="text-slate-700 underline ml-1 view-link" title="View Confirmation Details"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ?? $empChecklist->verified_by }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">View</a>
                        @else
                        <a href="#" class="text-slate-700 underline ml-2 verify-link" title="Confirm Item"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Confirm</a>
                        @endif
                        @endif
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->verified_dt === null || $empChecklist->verified_dt === '') ? 'N/A' :
                        $empChecklist->verified_dt }}
                        @else
                        {{ $empChecklist->verified_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '') ? 'N/A' :
                        $empChecklist->exp_dt }}
                        @else
                        {{ $empChecklist->exp_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                        @if($empChecklist && $empChecklist->verified_by && isset($users))
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ??
                        $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-600">
                        No orientation checklist items apply to this employee's current position.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>