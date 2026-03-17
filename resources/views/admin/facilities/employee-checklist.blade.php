<meta name="csrf-token" content="{{ csrf_token() }}">
<div x-show="tab === 'checklist'">
    <div class="bg-white p-4 rounded shadow">
        <!-- Tabs -->
        <ul class="flex border-b mb-6" id="employeeFileTabs">
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold active hover:text-teal-700 hover:border-teal-500"
                    href="#partA">PART A</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-teal-700 hover:border-teal-500"
                    href="#partB">PART B</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partC">PART C</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partD">PART D</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partE">PART E</a>
            </li>

            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partF">PART F</a>
            </li>
        </ul>

        <!-- PART A -->
        <div id="partA" class="tab-content">
            <h2 class="text-xl font-bold mb-4">PART A - APPLICANT, IDENTIFICATIONS, VERIFICATIONS</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">APPLICANT INFORMATION</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                            <th class="border px-2 py-1">VERIFIED BY</th>
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
                            <td class="border px-2 py-1"><input type="checkbox" {{ $empChecklist &&
                                    $empChecklist->on_file ? 'checked' : '' }}>
                                <a href="#" class="text-blue-600 underline ml-2 verify-link"
                                    data-item-name="{{ $item->name }}" data-emp-id="{{ $employee->emp_id }}"
                                    data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                                    data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                                    data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                                    data-comments="{{ $empChecklist->comments ?? '' }}"
                                    data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                                    data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
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
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
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
                            <td class="border px-2 py-1"><input type="checkbox" {{ $empChecklist &&
                                    $empChecklist->on_file ? 'checked' : '' }}><a href="#"
                                    class="text-blue-600 underline ml-2 verify-link"
                                    data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->emp_id }}"
                                    data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                                    data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                                    data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                                    data-comments="{{ $empChecklist->comments ?? '' }}"
                                    data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                                    data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="min-w-full border mt-6 text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">VERIFICATIONS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
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
                            <td class="border px-2 py-1"><input type="checkbox" {{ $empChecklist &&
                                    $empChecklist->on_file ? 'checked' : '' }}><a href="#"
                                    class="text-blue-600 underline ml-2 verify-link"
                                    data-item-name="{{ $item['name'] }}" data-emp-id="{{ $employee->emp_id }}"
                                    data-on-file="{{ $empChecklist && $empChecklist->on_file ? 1 : 0 }}"
                                    data-verified-dt="{{ $empChecklist->verified_dt ?? '' }}"
                                    data-exp-dt="{{ $empChecklist->exp_dt ?? '' }}"
                                    data-comments="{{ $empChecklist->comments ?? '' }}"
                                    data-verified-by="{{ $empChecklist->verified_by ?? '' }}"
                                    data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? 1 : 0 }}">Verify</a>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PART B to PART E (placeholders) -->
        <div id="partB" class="tab-content hidden">
            <h2 class="text-xl font-bold mb-4">PART B - ACKNOWLEDGEMENT OF RECEIPTS</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-xs md:text-sm mb-6">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Abuse, Neglect and Exploitation</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1">N/A</td>
                            <td class="border px-2 py-1">N/A</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Resident Rights</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1">N/A</td>
                            <td class="border px-2 py-1">N/A</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Employee Handbook</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Code of Conduct</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Employee Dress Code</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Spoken Language</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Agreement to Arbitrate</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
                <table class="min-w-full border text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Missed Punch Policy</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Rest and Meal Break Policy: Hydration Program</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Cell Phone Use Policy: CST Policies</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Use of ID Badge Policy: Second meal period waiver</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Six-hour meal period waiver</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="partC" class="tab-content hidden">
            <h2 class="text-xl font-bold mb-4">PART C - ACKNOWLEDGEMENT OF RECEIPTS</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-xs md:text-sm mb-6">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Facility Organizational Chart</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Facility Department Heads' Information</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Facility Floor Plan</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Facility Tour and General Orientation</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
                <table class="min-w-full border text-xs md:text-sm mb-6">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1 line-through">Voluntary & Involuntary Insurance Benefits</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Mariner Health Savings Plan / 401K</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">W-4</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">EDD</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Direct Deposit Authorization (Voided Check)</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Facility Time Processing</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Orientation Time Sheet</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Medical Insurance Premium Acknowledged</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
                <table class="min-w-full border text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">WC - Workplace and Ergonomics Safety</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Illness and Injury Prevention Program</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Individual Safety Responsibility</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Hazard Communication Training</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Notice to Employee <span class='font-bold'>(Labor Code Sec
                                    2810.5)</span></td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Environmental Care Questionnaire</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Required State Notice Acknowledgement Form</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="partD" class="tab-content hidden">
            <h2 class="text-xl font-bold mb-4">PART D - ACKNOWLEDGEMENT OF RECEIPTS</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1 text-left">ACKNOWLEDGEMENT OF RECEIPTS</th>
                            <th class="border px-2 py-1">CHECK IF ON FILE</th>
                            <th class="border px-2 py-1">VERIFICATION DATE</th>
                            <th class="border px-2 py-1">EXPIRATION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Accuracy, Notice of</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Affirmative, Notice of</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Blood Borne Pathogen</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Confidentiality</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Deficit Reduction Act</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Dementia</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">First Aid for Choking</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Gait Belt Utilization</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">HIPAA & Compliance</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 line-through">Hydration Program</td>
                            <td class="border px-2 py-1"><input type="checkbox" disabled></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Infection Control</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">No Solicitation, Distribution and Access</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Private Duty</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Pulmonary Tuberculosis</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Substance Abuse and Testing</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Other:</td>
                            <td class="border px-2 py-1"><input type="checkbox"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
                <table class="min-w-full border text-xs md:text-sm mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">Skill</th>
                            <th class="border px-2 py-1">Date</th>
                            <th class="border px-2 py-1">Initial</th>
                            <th class="border px-2 py-1">Skill</th>
                            <th class="border px-2 py-1">Date</th>
                            <th class="border px-2 py-1">Initial</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Ambulation</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Pain Identification/Management</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Back Rub</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Protective Devices</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bed Bath</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Post-mortem care</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bed Making, Occupied</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Range of motion</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bed Making, Unoccupied</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Restraint Devices</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bed Pan, Urinal</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Scales, weighing</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bladder Management/Toileting</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Shaving</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Bladder Patterning/Retraining</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Shower / Bathing</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Body Mechanics- Gen. Rules</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Specimen Collection</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Lifting and Moving</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Splints/Orthosis</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Positioning</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Standard Precautions</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Transferring</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Use of Cane</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Catheter Care</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Walker</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Choking, Heimlich Maneuver</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Prosthetic devices</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Dementia Training</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Bed controls</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Dialysis</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Wheelchair</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Dressing/undressing</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Vital signs</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Emergency Procedures/Reporting</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Temperature, axilla</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Feeding, Special Issues</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Temperature, ear</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Tray service</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Temperature, oral</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Dining Program</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Temperature, rectal</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Feeding Tubes</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Pulse Rate</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Gastric</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Respiratory Rate</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Jejunostomy</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Blood Pressure</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Nasogastric</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1 font-bold">Documentation:</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Fluid Restrictions, Dot system</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-RFPR</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Grooming</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Bed mobility</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Hand Washing</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Transfers</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Incontinence Care/Perineal Care</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Eating</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Infection Control, waste</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Toileting</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Linen Handling</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-Meal Monitoring</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Mechanical Lift</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-Intake/Output, measurement</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Oral Hygiene</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-STOP and WATCH</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Ostomy protocol review</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-Shower Skin Sheet</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Oxygen, CPAP, BiPAP Tubing Care</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">-RNA Form (for RNAs)</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Tracheostomy-ADL care (CNA scope of practice)</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1">Other:</td>
                            <td class="border px-2 py-1"></td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-4">
                    <label class="font-semibold">Comments:</label>
                    <textarea class="border rounded w-full min-h-[60px] mt-1" rows="3"></textarea>
                </div>
            </div>
        </div>

        <!-- PART F: Performance Appraisal -->
        <div id="partF" class="tab-content hidden">
            <h2 class="text-xl font-bold mb-4">PART F - EMPLOYEE PERFORMANCE APPRAISAL (Non-Management title)</h2>
            <div class="mb-4">
                <div class="flex flex-col md:flex-row md:space-x-8 mb-2">
                    <div class="mb-2 md:mb-0">
                        <label class="font-semibold">Facility Name:</label>
                        <input type="text" class="border rounded px-2 py-1" style="min-width:180px;">
                    </div>
                </div>
                <table class="min-w-full border text-xs md:text-sm mb-4">
                    <tr>
                        <td class="border px-2 py-1 font-semibold">Name</td>
                        <td class="border px-2 py-1"></td>
                        <td class="border px-2 py-1 font-semibold">Review Date</td>
                        <td class="border px-2 py-1"></td>
                    </tr>
                    <tr>
                        <td class="border px-2 py-1 font-semibold">title</td>
                        <td class="border px-2 py-1"></td>
                        <td class="border px-2 py-1 font-semibold">Date Employed</td>
                        <td class="border px-2 py-1"></td>
                    </tr>
                    <tr>
                        <td class="border px-2 py-1 font-semibold">Department</td>
                        <td class="border px-2 py-1"></td>
                        <td class="border px-2 py-1 font-semibold">Review</td>
                        <td class="border px-2 py-1"></td>
                    </tr>
                    <tr>
                        <td class="border px-2 py-1 font-semibold">Reviewers Name</td>
                        <td class="border px-2 py-1"></td>
                        <td class="border px-2 py-1 font-semibold">Type of Review</td>
                        <td class="border px-2 py-1"></td>
                    </tr>
                </table>
                <div class="italic text-xs text-gray-600 mb-2">
                    <strong>Instructions on Completing This Form:</strong> The performance appraisal form is designed to
                    communicate behaviors that model Company's performance expectations. This appraisal should reflect
                    overall performance of the employee considering such factors as knowledge, skills, and abilities,
                    but primarily on whether the employee’s performance produced the desired results. An explanation
                    will be required in the comments section where the rating either exceeds or is below the expectation
                    level.
                </div>
                <div class="italic text-xs text-gray-600 mb-2">
                    <strong>When to Use This Form:</strong> The Employee Appraisal should be used for both exempt and
                    non-exempt employees. This performance appraisal form is to be completed in conjunction with salary
                    reviews, promotions, transfers, and may also be completed on terminations, or otherwise when
                    considered desirable.
                </div>
                <div class="italic text-xs text-gray-600 mb-2">
                    <strong>How to Use This Form:</strong> The immediate supervisor will complete all sections and
                    discuss with employee. Employees should be asked to sign the appraisal, acknowledging that he/she
                    has participated in the review. Place one copy of the performance appraisal in the employee’s file.
                    Provide a copy to the employee.
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">PERFORMANCE RATING CATEGORIES</h3>
                <table class="min-w-full border text-xs md:text-sm mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">RATING DESCRIPTION</th>
                            <th class="border px-2 py-1">CODES</th>
                            <th class="border px-2 py-1">RATING VALUE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">The employee exceeds the majority of performance expectations.
                            </td>
                            <td class="border px-2 py-1">E = Exceeds</td>
                            <td class="border px-2 py-1">3</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">The employee meets performance expectations with occasional
                                deviations above and below expectations.</td>
                            <td class="border px-2 py-1">M = Meets</td>
                            <td class="border px-2 py-1">2</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">The employee has failed to meet one or more of the significant
                                performance expectations.</td>
                            <td class="border px-2 py-1">B = Below</td>
                            <td class="border px-2 py-1">1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">PERFORMANCE AREAS</h3>
                <div class="italic text-xs text-gray-600 mb-2">
                    Assess the employee’s knowledge, skills, and abilities, as outlined below. Place the appropriate
                    number in the box that describes the employee’s performance for each description. The rating value
                    column will auto fill, as will the section totals.
                </div>
                <!-- Section I -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>I. JOB SKILLS AND KNOWLEDGE</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Understands the job role and duties.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Initiates work projects without prompting, once
                                briefed.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Demonstrates proficiency in all phases of the job.
                            </td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Produces complete and accurate work.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section I Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section II -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>II. DEPENDABILITY</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Starts work promptly and can be depended upon to be
                                available for work.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Remains at work area as required.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Maintains confidentiality.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Takes appropriate actions and follows instructions
                                as directed.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section II Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section III -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>III. INTERPERSONAL SKILLS</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Adapts to changing situations.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Willing to assist others in accomplishing
                                additional work.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Considers viewpoints of others and accepts
                                constructive feedback.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Cooperates with other employees in a positive,
                                supportive, and courteous manner.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section III Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section IV -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>IV. ORGANIZATIONAL SKILLS</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Coordinates and maintains current work flow.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Recognizes priorities and meets deadlines.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Pays attention to detail.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Works well under pressure.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section IV Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section V -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>V. COMMUNICATION SKILLS</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Listens effectively and expresses understanding.
                            </td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Consistently fosters respect in the workplace and
                                demonstrates Company's Guiding Principles and Values.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Promotes understanding and acceptance of individual
                                and cultural differences in the workplace.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Provides clear, concise, and accurate verbal and
                                written information in an appropriate and timely manner.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section V Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section VI -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>VI. PROBLEM SOLVING</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Identifies existing problems.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Anticipates and identifies potential problems.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Knows how and where to obtain necessary
                                information.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Considers possible alternatives and makes
                                thoughtful recommendations.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section VI Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Section VII -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th colspan="2" class="border px-2 py-1 text-left"><em>VII. SAFETY & HEALTH</em></th>
                            <th class="border px-2 py-1">RATING 1-3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Follows best practices for workstation ergonomics
                                as guided by management</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Maintains good housekeeping in and around work area
                                (clear aisles and cooridors, under desk clearance, etc.)</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Participates is safety and health initiatives as
                                needed and/or requested</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Attends to created and observed spills and other
                                slip hazards immediately upon discovery</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Wears slip resistant footwear</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Keeps electrical cords and similar hazards out of
                                walking paths</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Removes or otherwise protects trip hazards</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Uses proper equipment such as stools or proper
                                ladder for tasks involving reaching overhead</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Disposes of trash and waste, including biohazardous
                                waste that requires special handling, in accordance with Company policies and procedures
                            </td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Reports unsafe conditions and practices as
                                observed. Corrects on the spot if possible.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Takes ownership of observed hazards (correct or
                                protect and report). Contribute sustainable ideas to help build and maintain a safety
                                culture</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Exhibits concern for the safety & health of
                                residents and colleagues</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border px-2 py-1">Considers possible alternatives and makes
                                thoughtful recommendations to safety committee, actively participates in training, and
                                promotes safety culture.</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="2" class="border px-2 py-1">Section VII Rating</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-2"><label class="font-semibold">Comments:</label><textarea
                        class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea></div>
                <!-- Totals -->
                <table class="min-w-full border text-xs md:text-sm mb-2">
                    <tbody>
                        <tr class="bg-red-100 font-bold">
                            <td class="border px-2 py-1">Total of All Sections</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                        <tr class="bg-red-100 font-bold">
                            <td class="border px-2 py-1">Overall Rating (divided by 7 for equal weighting)</td>
                            <td class="border px-2 py-1">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <div class="italic text-xs text-gray-600 mb-2">
                    Use the number from the shaded box above to determine the overall performance rating according to
                    the guidelines below. Write the appropriate E, M, or B in the shaded box below.
                </div>
                <table class="min-w-full border text-xs md:text-sm mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">RATING DESCRIPTION</th>
                            <th class="border px-2 py-1">EXPECTATIONS</th>
                            <th class="border px-2 py-1">RATING GUIDELINES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">The employee exceeds the majority of performance expectations.
                            </td>
                            <td class="border px-2 py-1">E = EXCEEDS EXPECTATIONS</td>
                            <td class="border px-2 py-1">2.51 – 3.00</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">The employee meets performance expectations with occasional
                                deviations above and below expectations.</td>
                            <td class="border px-2 py-1">M = MEETS EXPECTATIONS</td>
                            <td class="border px-2 py-1">1.75 – 2.50</td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">The employee has failed to meet one or more of the significant
                                performance expectations.</td>
                            <td class="border px-2 py-1">B = BELOW EXPECTATIONS</td>
                            <td class="border px-2 py-1">1.00 – 1.74</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex items-center mb-2">
                    <label class="font-semibold mr-2">Overall Performance Rating:</label>
                    <input type="text" class="border rounded px-2 py-1 w-24">
                    <span class="ml-2">Write an E, M, or B in the shaded box →</span>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">AREAS FOR DEVELOPMENT</h3>
                <div class="mb-2">
                    <label class="font-semibold">Areas Requiring Further Development:</label>
                    <div class="italic text-xs text-gray-600 mb-1">Describe the areas in the employee’s performance that
                        need to be further developed.</div>
                    <textarea class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="font-semibold">Development Plans:</label>
                    <div class="italic text-xs text-gray-600 mb-1">Indicate plans to develop or improve the employee’s
                        performance or potential.</div>
                    <textarea class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="font-semibold">Employee Comments (Optional):</label>
                    <textarea class="border rounded w-full min-h-[40px] mt-1" rows="2"></textarea>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">Signatures</h3>
                <table class="min-w-full border text-xs md:text-sm mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">Signatures</th>
                            <th class="border px-2 py-1">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-2 py-1">Supervisor:</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1">Employee:</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Verify Checklist Item -->
<div id="verifyModal" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeVerifyModal()">&times;</button>
        <h3 class="text-lg font-bold mb-4">Verify Checklist Item</h3>
        <form id="verifyForm">
            <input type="hidden" name="emp_id" id="verifyEmpId">
            <input type="hidden" name="doc_name" id="verifyDocName">
            <div class="mb-3">
                <label class="block font-semibold mb-1">On File</label>
                <input type="checkbox" name="on_file" id="verifyOnFile">
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Verification Date</label>
                <input type="date" name="verified_dt" id="verifyVerifiedDt" class="border rounded px-2 py-1 w-full"
                    readonly>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Expiration Date</label>
                <div class="flex items-center space-x-2">
                    <input type="date" name="exp_dt" id="verifyExpDt" class="border rounded px-2 py-1 w-full">
                    <label class="inline-flex items-center text-xs ml-2">
                        <input type="checkbox" id="expDtNotRequired" class="mr-1"> Not required
                    </label>
                </div>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Verified By</label>
                <input type="text" name="verified_by_name" id="verifyVerifiedBy" class="border rounded px-2 py-1 w-full"
                    readonly>
                <input type="hidden" name="verified_by" id="verifyVerifiedById">
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Comments</label>
                <textarea name="comments" id="verifyComments" class="border rounded px-2 py-1 w-full"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeVerifyModal()"
                    class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Set current user info for modal population (must be before any modal logic)
    @if(auth()->check())
        window.currentUserId = {{ auth()->user()->id }};
        window.currentUserName = @json(auth()->user()->name);
    @endif
    // Modal logic for Verify Checklist Item
    function openVerifyModal(itemName, empId) {
        console.log('openVerifyModal called', { itemName, empId });
        document.getElementById('verifyDocName').value = itemName;
        document.getElementById('verifyEmpId').value = empId;
        // Find doc_type_id for this item (from table row data attribute)
        var link = Array.from(document.querySelectorAll('.verify-link')).find(l => l.getAttribute('data-item-name') === itemName && l.getAttribute('data-emp-id') == empId);
        var row = link ? link.closest('tr') : null;
        console.log('Row found for modal:', row);
        var docTypeId = row ? row.getAttribute('data-doc-type-id') : '';
        document.getElementById('verifyForm').setAttribute('data-doc-type-id', docTypeId);
        // Auto-populate fields if data is present
        document.getElementById('verifyOnFile').checked = link && link.getAttribute('data-on-file') == '1';
        var verifiedDt = link && link.getAttribute('data-verified-dt') ? link.getAttribute('data-verified-dt') : '';
        var expDt = link && link.getAttribute('data-exp-dt') ? link.getAttribute('data-exp-dt') : '';
        var comments = link && link.getAttribute('data-comments') ? link.getAttribute('data-comments') : '';
        var verifiedBy = link && link.getAttribute('data-verified-by') ? link.getAttribute('data-verified-by') : '';
        var expDtNotRequired = link && link.getAttribute('data-exp-dt-not-required') == '1';
        document.getElementById('verifyVerifiedDt').value = verifiedDt || (function(){
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        })();
        document.getElementById('verifyExpDt').value = expDt;
        document.getElementById('verifyComments').value = comments;
        // Set Verified By to current user name (from window.currentUserName) and id (window.currentUserId) if not present
        if (window.currentUserName && !verifiedBy) {
            document.getElementById('verifyVerifiedBy').value = window.currentUserName;
        } else if (verifiedBy) {
            document.getElementById('verifyVerifiedBy').value = verifiedBy;
        }
        if (window.currentUserId && !verifiedBy) {
            document.getElementById('verifyVerifiedById').value = window.currentUserId;
        } else if (verifiedBy) {
            document.getElementById('verifyVerifiedById').value = verifiedBy;
        }
        // Expiration Date Not Required checkbox and input
        document.getElementById('expDtNotRequired').checked = expDtNotRequired;
        document.getElementById('verifyExpDt').disabled = expDtNotRequired;
        var modal = document.getElementById('verifyModal');
        console.log('Modal element:', modal);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            console.log('Modal classes after open:', modal.className);
        }, 100);
    }
    function closeVerifyModal() {
        var modal = document.getElementById('verifyModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Expiration Date Not Required checkbox logic
        var expDtNotRequired = document.getElementById('expDtNotRequired');
        var expDtInput = document.getElementById('verifyExpDt');
        if (expDtNotRequired && expDtInput) {
            expDtNotRequired.addEventListener('change', function() {
                if (this.checked) {
                    expDtInput.value = '';
                    expDtInput.disabled = true;
                } else {
                    expDtInput.disabled = false;
                }
            });
        }
        console.log('DOMContentLoaded: binding verify-link click events');
        document.querySelectorAll('.verify-link').forEach(function(link) {
            console.log('Binding click for', link);
            link.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Verify link clicked', this);
                openVerifyModal(this.getAttribute('data-item-name'), this.getAttribute('data-emp-id'));
            });
        });
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var empId = document.getElementById('verifyEmpId').value;
            var docName = document.getElementById('verifyDocName').value;
            var docTypeId = document.getElementById('verifyForm').getAttribute('data-doc-type-id') || 1;
            var onFile = document.getElementById('verifyOnFile').checked ? 1 : 0;
            var verifiedDt = document.getElementById('verifyVerifiedDt').value;
            var expDtNotRequiredChecked = document.getElementById('expDtNotRequired').checked;
            var expDt = expDtNotRequiredChecked ? null : document.getElementById('verifyExpDt').value;
            var comments = document.getElementById('verifyComments').value;
            var verifiedById = document.getElementById('verifyVerifiedById').value;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/admin/employees/${empId}/checklist/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    doc_name: docName,
                    doc_type_id: docTypeId,
                    on_file: onFile,
                    verified_dt: verifiedDt,
                    exp_dt: expDt,
                    comments: comments,
                    verified_by: verifiedById,
                    exp_dt_not_required: expDtNotRequiredChecked ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Optionally update the row UI with new data
                    // Find the row and update columns
                    var row = Array.from(document.querySelectorAll('.verify-link')).find(l => l.getAttribute('data-item-name') === docName && l.getAttribute('data-emp-id') == empId)?.closest('tr');
                    if (row) {
                        row.querySelector('input[type="checkbox"]').checked = !!data.data.on_file;
                        row.children[2].textContent = data.data.verified_dt || '';
                        if (data.data.exp_dt === null || data.data.exp_dt === '' || data.data.exp_dt === undefined) {
                            row.children[3].textContent = 'N/A';
                        } else {
                            row.children[3].textContent = data.data.exp_dt;
                        }
                        row.children[4].textContent = data.data.verified_by || '';
                    }
                } else {
                    let msg = 'Failed to save checklist verification.';
                    if (data.message) {
                        msg += '\n' + data.message;
                    }
                    alert(msg);
                }
                closeVerifyModal();
            })
            .catch(() => {
                alert('Error saving checklist verification.');
                closeVerifyModal();
            });
        });
    });
</script>

<script>
    document.querySelectorAll('.tab-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        const target = this.getAttribute('href');
        document.querySelector(target).classList.remove('hidden');
    });
});
</script>