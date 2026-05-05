<div id="partE" class="tab-content hidden">
    <div class="overflow-x-auto">
        @php
        $partEItems = $checklistItems->where('section', 'PART E')->sortBy('order');
        $currentPositionTitle = trim((string) ($employee->currentAssignment?->position?->position_title ?? ''));
        $normalizedPositionTitle = \Illuminate\Support\Str::lower($currentPositionTitle);
        $orientationPositionTitles = collect([
            'administrator',
            'director of staff development',
        ]);
        $orientationPositionKeywords = collect([
            'administrator',
            'staff development',
        ]);
        $isOrientationChecklist = $orientationPositionTitles->contains($normalizedPositionTitle)
            || $orientationPositionKeywords->contains(function ($keyword) use ($normalizedPositionTitle) {
                return $normalizedPositionTitle !== '' && \Illuminate\Support\Str::contains($normalizedPositionTitle, $keyword);
            });
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
        <h2 class="text-xl font-bold mb-4">{{ $isOrientationChecklist ? 'ORIENTATION' : 'SKILLS' }} CHECKLIST: {{ $employee->currentAssignment?->position?->position_title ?? 'No Position Assigned' }}</h2>
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">{{ $isOrientationChecklist ? 'ORIENTATION ITEMS' : 'SKILLS/ITEMS' }}</th>
                    <th class="border px-2 py-1 text-center w-1/4">CONFIRMATION</th>
                    <th class="border px-2 py-1 text-center w-1/8">CONFIRMED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/8">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/8">CONFIRMED BY</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partEItems as $item)
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
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id ?? 5 }}" data-item-name="{{ $item->name }}"
                    data-item-level="{{ $itemLevel }}" data-item-disabled="{{ !empty($item->disabled) ? 1 : 0 }}">
                    <td class="border px-2 py-1 @if(isset($item->disabled) && $item->disabled) line-through @endif">
                        <span class="text-sm {{ $displayIndentClass }} {{ $itemLevel === 0 ? 'font-bold' : '' }}">{{ $displayName }}</span>
                    </td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;" @if(isset($item->disabled) && $item->disabled)
                        disabled @endif>
                        @if(empty($item->disabled))
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Click to unconfirm Item"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}">Confirmed</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Confirmation Details"
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
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Confirm Item"
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
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->verified_dt === null || $empChecklist->verified_dt === '') ? 'N/A' :
                        $empChecklist->verified_dt }}
                        @else
                        {{ $empChecklist->verified_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '') ? 'N/A' :
                        $empChecklist->exp_dt }}
                        @else
                        {{ $empChecklist->exp_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && $empChecklist->verified_by && isset($users))
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ??
                        $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border px-4 py-6 text-center text-gray-500">
                        No Part E checklist items apply to this employee's current position.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{-- <div class="mt-4">
            <label class="font-semibold">Comments2:</label>
            <textarea class="border rounded w-full min-h-[60px] mt-1" rows="3"></textarea>
        </div> --}}
    </div>
</div>