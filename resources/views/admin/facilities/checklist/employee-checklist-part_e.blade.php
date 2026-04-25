<div id="partE" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">PART E - NURSING COMPETENCY AND SKILLS EVALUATION (CNA)</h2>
    <div class="mb-4">
        <div class="flex flex-col md:flex-row md:space-x-8 mb-2">
            <div class="mb-2 md:mb-0">
                <label class="font-semibold">Name:</label>
                <input type="text" class="border rounded px-2 py-1" style="min-width:180px;">
            </div>
            <div>
                <label class="font-semibold">Date of Hire:</label>
                <input type="date" class="border rounded px-2 py-1">
            </div>
        </div>
        <div class="italic text-xs text-gray-600 mb-2">
            Instructions: Completed on Orientation, Annual Performance Evaluation, and as deemed appropriate by
            the DON/Administrator.
        </div>
    </div>
    <div class="overflow-x-auto">
        @php
        $partEItems = $checklistItems->where('section', 'PART E')->sortBy('order');
        @endphp
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left w-1/4">SKILL</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($partEItems as $item)
                @php
                $empChecklist = null;
                if ($empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('employee_num', $employee->employee_num);
                if ($empChecklistRow && isset($empChecklistRow->items[$item->name])) {
                $empChecklist = (object) $empChecklistRow->items[$item->name];
                }
                }
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id ?? 5 }}">
                    <td class="border px-2 py-1 @if(isset($item->disabled) && $item->disabled) line-through @endif">{!!
                        $item->name !!}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;" @if(isset($item->disabled) && $item->disabled)
                        disabled @endif>
                        @if(empty($item->disabled))
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ $item->name }}" data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item->name }}" data-emp-id="{{ $employee->employee_num }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ $item->name }}" data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
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
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            <label class="font-semibold">Comments:</label>
            <textarea class="border rounded w-full min-h-[60px] mt-1" rows="3"></textarea>
        </div>
    </div>
</div>