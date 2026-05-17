<div id="partA" class="tab-content">
    <h2 class="text-xl font-bold mb-4">Part A - APPLICANT INFO, IDENTIFICATIONS, VERIFICATIONS
    </h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">APPLICANT INFORMATION</th>
                    <th class="border px-2 py-1 text-center">ON FILE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center">EXPIRY DATE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED BY</th>
                </tr>
            </thead>

            <tbody>
                @php
                $applicantItems = $checklistItems->where('section', 'PART A')->where('doc_type_id',
                1)->sortBy('order');
                $identificationItems = $checklistItems->where('section', 'PART A')->where('doc_type_id',
                2)->sortBy('order');
                $verificationItems = $checklistItems->where('section', 'PART A')->where('doc_type_id',
                3)->sortBy('order');
                $partBItems = $checklistItems->where('section', 'PART B')->sortBy('order');
                $partCItems = $checklistItems->where('section', 'PART C')->sortBy('order');
                $partDItems = $checklistItems->where('section', 'PART D')->sortBy('order');
                $partEItems = $checklistItems->where('section', 'PART E')->sortBy('order');
                @endphp
                @foreach ($applicantItems as $item)
                @php
                $empChecklist = $resolveChecklistEntry($item);
                $checklistKey = $resolveChecklistKey($item);
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id }}">
                    <td class="border px-2 py-1">{{ $item->name }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ ($item->isExpiring ?? false) ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ ($item->isExpiring ?? false) ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ ($item->isExpiring ?? false) ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
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
                        @if($empChecklist && $empChecklist->verified_by)
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ??
                        $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="min-w-full border mt-6 text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">IDENTIFICATIONS</th>
                    <th class="border px-2 py-1 text-center">ON FILE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center">EXPIRY DATE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($identificationItems as $item)
                @php
                $empChecklist = $resolveChecklistEntry($item);
                $checklistKey = $resolveChecklistKey($item);
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id }}">
                    <td class="border px-2 py-1">{{ $item->name }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
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
                        @if($empChecklist && $empChecklist->verified_by)
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ??
                        $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="min-w-full border mt-6 text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">VERIFICATIONS</th>
                    <th class="border px-2 py-1 text-center">ON FILE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center">EXPIRY DATE</th>
                    <th class="border px-2 py-1 text-center">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($verificationItems as $item)
                @php
                $empChecklist = $resolveChecklistEntry($item);
                $checklistKey = $resolveChecklistKey($item);
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id }}">
                    <td class="border px-2 py-1">{{ $item->name }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ $item->name }}"
                            data-item-id="{{ $item->id }}"
                            data-checklist-key="{{ $checklistKey }}"
                            data-is-expiring="{{ $item->isExpiring ? 1 : 0 }}"
                            data-emp-id="{{ $employee->employee_num }}"
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
                        @if($empChecklist && $empChecklist->verified_by)
                        {{ optional($users->firstWhere('id', $empChecklist->verified_by))->name ??
                        $empChecklist->verified_by }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>