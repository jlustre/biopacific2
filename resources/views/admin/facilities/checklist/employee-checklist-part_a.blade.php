<div id="partA" class="tab-content">
    <h2 class="text-xl font-bold mb-4">Part A - APPLICANT INFO, IDENTIFICATIONS, VERIFICATIONS
    </h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left w-1/4">APPLICANT INFORMATION</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
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
                $empChecklist = null;
                if ($empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                if ($empChecklistRow && isset($empChecklistRow->items[$item->name])) {
                $empChecklist = (object) $empChecklistRow->items[$item->name];
                }
                }
                @endphp
                <tr data-doc-type-id="{{ $item->doc_type_id }}">
                    <td class="border px-2 py-1">{{ $item->name }}</td>
                    <td class="border px-2 py-1">
                        @php
                        $empChecklist = null;
                        if ($empChecklists && count($empChecklists)) {
                        $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                        if ($empChecklistRow && isset($empChecklistRow->items[$item->name])) {
                        $empChecklist = (object) $empChecklistRow->items[$item->name];
                        }
                        }
                        @endphp
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}"
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
                    <th class="border px-2 py-1 text-left w-1/4">IDENTIFICATIONS</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @php
                $identificationRows = [
                ['name' => 'I - 9', 'doc_type_id' => 2],
                ['name' => 'Social Security Card - Copy', 'doc_type_id' => 2],
                ['name' => "Driver's License - Copy", 'doc_type_id' => 2],
                ['name' => 'Green Card or Work Permit Autho. - Copy', 'doc_type_id' => 2],
                ['name' => 'Passport - Copy', 'doc_type_id' => 2],
                ['name' => 'Professional License - Copy', 'doc_type_id' => 2],
                ];
                @endphp
                @foreach ($identificationRows as $item)
                @php
                $empChecklist = null;
                if ($empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                $empChecklist = (object) $empChecklistRow->items[$item['name']];
                }
                }
                @endphp
                <tr data-doc-type-id="{{ $item['doc_type_id'] }}">
                    <td class="border px-2 py-1">{{ $item['name'] }}</td>
                    <td class="border px-2 py-1">
                        @php
                        $empChecklist = null;
                        if ($empChecklists && count($empChecklists)) {
                        $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                        if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                        $empChecklist = (object) $empChecklistRow->items[$item['name']];
                        }
                        }
                        @endphp
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}"
                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                            data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                            data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                            data-comments="{{ $empChecklist->comments ?? '' }}"
                            data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
                        @endif
                    </td>
                    <td class="border px-2 py-1">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->verified_dt === null || $empChecklist->verified_dt === '') ? 'N/A' :
                        $empChecklist->verified_dt }}
                        @else
                        {{ $empChecklist->verified_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1">
                        @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                        {{ ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '') ? 'N/A' :
                        $empChecklist->exp_dt }}
                        @else
                        {{ $empChecklist->exp_dt ?? '' }}
                        @endif
                    </td>
                    <td class="border px-2 py-1">
                        @if($empChecklist && $empChecklist->verified_by)
                        @php
                        $user = $users->firstWhere('id', $empChecklist->verified_by ?? null);
                        @endphp
                        {{ $user ? $user->name : $empChecklist->verified_by }}
                        @else
                        {{ $empChecklist->verified_by ?? '' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="min-w-full border mt-6 text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left w-1/4">VERIFICATIONS</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @php
                $verificationRows = [
                ['name' => 'CPR Card (License Nurses)', 'doc_type_id' => 3],
                ['name' => 'C.N.A. Certificate', 'doc_type_id' => 3],
                ['name' => 'Professional License', 'doc_type_id' => 3],
                ['name' => 'Background Check', 'doc_type_id' => 3],
                ['name' => 'OIG Verification', 'doc_type_id' => 3],
                ['name' => 'SAM Verification', 'doc_type_id' => 3],
                ['name' => 'Medical Exclusion/Ineligible Provider List', 'doc_type_id' => 3],
                ];
                @endphp
                @foreach ($verificationRows as $item)
                @php
                $empChecklist = null;
                if ($empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                $empChecklist = (object) $empChecklistRow->items[$item['name']];
                }
                }
                @endphp
                <tr data-doc-type-id="{{ $item['doc_type_id'] }}">
                    <td class="border px-2 py-1">{{ $item['name'] }}</td>
                    <td class="border px-2 py-1">
                        @php
                        $empChecklist = null;
                        if ($empChecklists && count($empChecklists)) {
                        $empChecklistRow = $empChecklists->firstWhere('emp_id', $employee->emp_id);
                        if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                        $empChecklist = (object) $empChecklistRow->items[$item['name']];
                        }
                        }
                        @endphp
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ?
                        'checked' : '' }} readonly tabindex="-1" style="pointer-events:none;">
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ is_array($item) ? $item['name'] : $item->name }}"
                            data-emp-id="{{ $employee->emp_id }}"
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
                        @php
                        $user = $users->firstWhere('id', $empChecklist->verified_by ?? null);
                        @endphp
                        {{ $user ? $user->name : $empChecklist->verified_by }}
                        @else
                        {{ $empChecklist->verified_by ?? '' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    window.checklistItemsByName = {};
@foreach($checklistItems as $item)
window.checklistItemsByName[@json($item->name)] = { isExpiring: {{ $item->isExpiring ? 1 : 0 }} };
@endforeach
</script>