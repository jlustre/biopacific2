<div id="partB" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">PART B - ACKNOWLEDGEMENT OF RECEIPTS</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full border text-xs md:text-sm mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left w-1/4">ACKNOWLEDGEMENT OF RECEIPTS</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @php
                $ackRows = [
                ['name' => 'Abuse, Neglect and Exploitation', 'disabled' => false],
                ['name' => 'Resident Rights', 'disabled' => false],
                ['name' => 'Employee Handbook', 'disabled' => false],
                ['name' => 'Code of Conduct', 'disabled' => false],
                ['name' => 'Employee Dress Code', 'disabled' => true],
                ['name' => 'Spoken Language', 'disabled' => false],
                ['name' => 'Agreement to Arbitrate', 'disabled' => true],
                ];
                @endphp
                @foreach ($ackRows as $item)
                @php
                $empChecklist = null;
                if (isset($empChecklists) && $empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('employee_num', $employee->employee_num);
                if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                $empChecklist = (object) $empChecklistRow->items[$item['name']];
                }
                }
                @endphp
                <tr data-doc-type-id="4">
                    <td class="border px-2 py-1 @if($item['disabled']) line-through @endif">{{ $item['name'] }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;" @if($item['disabled']) disabled @endif>
                        @if(!$item['disabled'])
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link" title="Verify Item"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}"
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
        <table class="min-w-full border text-xs md:text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left w-1/4">ACKNOWLEDGEMENT OF RECEIPTS</th>
                    <th class="border px-2 py-1 text-center w-1/8">ON FILE</th>
                    <th class="border px-2 py-1 text-center w-1/6">VERIFIED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/6">EXPIRED DATE</th>
                    <th class="border px-2 py-1 text-center w-1/4">VERIFIED BY</th>
                </tr>
            </thead>
            <tbody>
                @php
                $ackRows2 = [
                ['name' => 'Missed Punch Policy', 'disabled' => false],
                ['name' => 'Rest and Meal Break Policy: Hydration Program', 'disabled' => false],
                ['name' => 'Cell Phone Use Policy: CST Policies', 'disabled' => true],
                ['name' => 'Use of ID Badge Policy: Second meal period waiver', 'disabled' => false],
                ['name' => 'Six-hour meal period waiver', 'disabled' => false],
                ];
                @endphp
                @foreach ($ackRows2 as $item)
                @php
                $empChecklist = null;
                if (isset($empChecklists) && $empChecklists && count($empChecklists)) {
                $empChecklistRow = $empChecklists->firstWhere('employee_num', $employee->employee_num);
                if ($empChecklistRow && isset($empChecklistRow->items[$item['name']])) {
                $empChecklist = (object) $empChecklistRow->items[$item['name']];
                }
                }
                @endphp
                <tr data-doc-type-id="4">
                    <td class="border px-2 py-1 @if($item['disabled']) line-through @endif">{{ $item['name'] }}</td>
                    <td class="border px-2 py-1">
                        <input type="checkbox" {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }} readonly
                        tabindex="-1" style="pointer-events:none;" @if($item['disabled']) disabled @endif>
                        @if(!$item['disabled'])
                        @if($empChecklist && $empChecklist->verified_by)
                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Revoke Verification"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link" title="View Verification Details"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline ml-2 verify-link"
                            data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->employee_num }}"
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
    </div>
</div>