<div id="partB" class="tab-content ">
    <h2 class="text-xl font-bold mb-4">PART B - ACKNOWLEDGEMENT OF RECEIPTS</h2>
    <div class="overflow-x-auto">
        @php
        $partBItems = isset($checklistItems)
            ? $checklistItems->where('section', 'PART B')->sortBy('order')
            : collect();
        @endphp
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                    <th class="border px-2 py-1 text-center">ON FILE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center">EXPIRY DATE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partBItems as $item)
                @php
                $empChecklist = isset($resolveChecklistEntry) ? $resolveChecklistEntry($item) : null;
                $checklistKey = isset($resolveChecklistKey) ? $resolveChecklistKey($item) : ('item_' . $item->id);
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id ?? 4 }}">
                    <td class="border px-2 py-1">{{ $item->name }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Click to Unconfirm Verification"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}">Confirmed</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ $item->name }}" data-item-id="{{ $item->id }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->verified_dt === null || $empChecklist->verified_dt === '') ? 'N/A' : $empChecklist->verified_dt }}
                        @else
                        {{ $empChecklist->verified_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '') ? 'N/A' : $empChecklist->exp_dt }}
                        @else
                        {{ $empChecklist->exp_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1 text-center">
                        @if($empChecklist && $empChecklist->verified_by && isset($users))
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ?? $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border px-2 py-4 text-center text-slate-500">No PART B document types are configured in Documents Settings.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
