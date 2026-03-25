<div class="mb-4">
    <div class="flex flex-col md:flex-row mb-2 items-center justify-between">
        <div class="ml-2">
            <label class="font-semibold mr-2">Facility Name:</label>
            <input type="text" class="border rounded px-2 py-1 mr-4 width-auto"
                value="{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->name : '' }}"
                readonly style="flex-grow:1; min-width:100%; max-width:100%;">
            <input type="hidden" name="facility_id" id="facilityIdHidden"
                value="{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->id : '' }}">
            <script>
                window.currentFacilityId = '{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->id : '' }}';
            </script>
        </div>
        <div class="ml-2">
            <label for="assessmentPeriodSelect" class="font-semibold mr-2">Assessment Period:</label>
            <div class="flex flex-row items-center justify-between">
                <select id="assessmentYearSelect" class="border border-teal-600 border-2 rounded px-2 py-1 mr-2"
                    style="font-weight: bold;">
                    @php
                    $years = collect($assessmentPeriods)->pluck('period_year')->unique()->sort()->values();
                    $currentYear = date('Y');
                    $selectedYear = request('assessment_year') ?? ($years->contains($currentYear) ? $currentYear :
                    $years->last());
                    @endphp
                    @foreach($years as $year)
                    <option value="{{ $year }}" @if($selectedYear==$year) selected @endif>{{ $year }}</option>
                    @endforeach
                </select>
                <form id="assessmentPeriodForm" method="GET" action="">
                    <select id="assessmentPeriodSelect" name="assessment_period_id"
                        class="border rounded px-2 py-1 mr-2"
                        style="background-color: #fffbe6; border: 2px solid #f59e42; font-weight: bold;"
                        onchange="this.form.submit()">
                        @foreach($assessmentPeriods as $period)
                        <option value="{{ $period->id }}" data-year="{{ $period->period_year }}"
                            @if($selectedAssessmentPeriodId==$period->id) selected @endif>
                            {{ $period->date_from }} to {{ $period->date_to }}
                        </option>
                        @endforeach
                    </select>
                    <input type="hidden" id="assessmentYearHidden" name="assessment_year" value="{{ $selectedYear }}">
                    @foreach(request()->except(['assessment_period_id','assessment_year']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var yearSelect = document.getElementById('assessmentYearSelect');
                    var periodSelect = document.getElementById('assessmentPeriodSelect');
                    var yearHidden = document.getElementById('assessmentYearHidden');
                    function filterPeriodsByYear(selectedYear) {
                        Array.from(periodSelect.options).forEach(function(opt) {
                            opt.style.display = (opt.getAttribute('data-year') == selectedYear) ? '' : 'none';
                        });
                        // Select first visible option if current is hidden
                        if (periodSelect.selectedOptions.length && periodSelect.selectedOptions[0].style.display === 'none') {
                            var firstVisible = Array.from(periodSelect.options).find(o => o.style.display !== 'none');
                            if (firstVisible) periodSelect.value = firstVisible.value;
                        }
                    }
                    yearSelect.addEventListener('change', function() {
                        yearHidden.value = this.value;
                        filterPeriodsByYear(this.value);
                        // Optionally auto-submit the form when year changes
                        periodSelect.form.submit();
                    });
                    // Initial filter
                    filterPeriodsByYear(yearSelect.value);
                });
                // Reviewed Employees Modal logic
                function openReviewedEmployeesModal() {
                                        var facilityNameSpan = document.getElementById('reviewedEmployeesFacility');
                                        var periodSpan = document.getElementById('reviewedEmployeesPeriod');
                    var modal = document.getElementById('reviewedEmployeesModal');
                    var listDiv = document.getElementById('reviewedEmployeesList');
                    if (!modal || !listDiv) return;
                    listDiv.innerHTML = '<div class="text-gray-500">Loading...</div>';
                    // Get selected assessment period and facility
                    var periodSelect = document.getElementById('assessmentPeriodSelect');
                    var facilityInput = document.querySelector('input[name="facility_id"], input[name="facility"]');
                    var assessmentPeriodId = periodSelect ? periodSelect.value : null;
                    var facilityId = facilityInput ? facilityInput.value : (window.currentFacilityId || null);
                    // Set facility name and assessment period text
                    if (facilityNameSpan) {
                        var facilityNameInput = document.querySelector('input[readonly][type="text"]');
                        facilityNameSpan.textContent = 'Facility: ' + (facilityNameInput ? facilityNameInput.value : '');
                    }
                    if (periodSpan) {
                        var selectedPeriodOption = periodSelect ? periodSelect.selectedOptions[0] : null;
                        periodSpan.textContent = 'Assessment Period: ' + (selectedPeriodOption ? selectedPeriodOption.textContent : '');
                    }
                    // console.log('DEBUG facilityInput:', facilityInput);
                    // console.log('DEBUG facilityInput.value:', facilityInput ? facilityInput.value : null);
                    // console.log('DEBUG window.currentFacilityId:', window.currentFacilityId);
                    // console.log('DEBUG assessmentPeriodId:', assessmentPeriodId);
                    // console.log('DEBUG facilityId (used):', facilityId);
                    if (!assessmentPeriodId || !facilityId) {
                        listDiv.innerHTML = '<div class="text-red-600">Missing assessment period or facility.</div>';
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        return;
                    }
                    fetch('/admin/employees/performance-assessment/reviewed-employees?assessment_period_id=' + encodeURIComponent(assessmentPeriodId) + '&facility_id=' + encodeURIComponent(facilityId), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(resp => resp.json())
                    .then(data => {
                        if (!data.success) {
                            listDiv.innerHTML = '<div class="text-red-600">' + (data.message || 'Failed to load employees.') + '</div>';
                            return;
                        }
                        if (!data.employees.length) {
                            listDiv.innerHTML = '<div class="text-gray-500">No employees reviewed for this period and facility.</div>';
                            return;
                        }
                        var html = '<table class="min-w-full border text-xs md:text-sm"><thead><tr class="bg-gray-100">' +
                            '<th class="border px-2 py-1">Employee Name</th>' +
                            '<th class="border px-2 py-1">Position</th>' +
                            '<th class="border px-2 py-1">Department</th>' +
                            '<th class="border px-2 py-1">Assessment Date</th>' +
                            '<th class="border px-2 py-1">Reviewed By</th>' +
                            '<th class="border px-2 py-1">Action</th>' +
                            '</tr></thead><tbody>';
                        data.employees.forEach(function(emp) {
                            html += '<tr>' +
                                '<td class="border px-2 py-1">' + emp.name + '</td>' +
                                '<td class="border px-2 py-1">' + (emp.position || '') + '</td>' +
                                '<td class="border px-2 py-1">' + (emp.department || '') + '</td>' +
                                '<td class="border px-2 py-1">' + (emp.assessment_date || '') + '</td>' +
                                '<td class="border px-2 py-1">' + (emp.reviewed_by || '') + '</td>' +
                                '<td class="border px-2 py-1 text-center">' +
                                    '<button class="px-2 py-1 bg-blue-600 text-white rounded text-xs load-employee-btn" data-emp-id="' + emp.emp_id + '">Load</button>' +
                                '</td>' +
                                '</tr>';
                        });
                        html += '</tbody></table>';
                        listDiv.innerHTML = html;
                        // Add click handler for Load buttons
                        listDiv.querySelectorAll('.load-employee-btn').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var empId = this.getAttribute('data-emp-id');
                                if (!empId) return;
                                // Build new URL with emp_id replaced or added
                                var url = new URL(window.location.href);
                                url.searchParams.set('emp_id', empId);
                                window.location.href = url.toString();
                            });
                        });
                    })
                    .catch(() => {
                        listDiv.innerHTML = '<div class="text-red-600">Failed to load employees.</div>';
                    });
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
                function closeReviewedEmployeesModal() {
                    var modal = document.getElementById('reviewedEmployeesModal');
                    if (modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                }
                document.addEventListener('DOMContentLoaded', function() {
                    var btn = document.getElementById('listReviewedEmployeesBtn');
                    if (btn) {
                        btn.addEventListener('click', openReviewedEmployeesModal);
                    }
                });
            </script>
            <div class="flex justify-end space-x-2">
                <button type="button" id="addNewPeriodBtn"
                    class="px-2 my-1 py-1 bg-green-600 text-white rounded text-xs cursor-pointer"
                    title="Create new assessment period">New Period</button>
                <button type="button" id="editPeriodBtn"
                    class="px-2 my-1 py-1 bg-blue-600 text-white rounded text-xs cursor-pointer"
                    title="Edit selected assessment period">Edit Period</button>
                <button type="button" id="deletePeriodBtn"
                    class="px-2 my-1 py-1 bg-red-600 text-white rounded text-xs cursor-pointer"
                    title="Delete selected assessment period">Delete</button>
                <button type="button" id="listReviewedEmployeesBtn"
                    class="px-2 my-1 py-1 bg-purple-600 text-white rounded text-xs cursor-pointer"
                    title="List all employees reviewed for this period and facility">Reviewed Employees</button>
                <!-- Modal for affected assessments -->
                <div id="deleteAffectedModal"
                    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg relative">
                        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
                            onclick="closeDeleteAffectedModal()">&times;</button>
                        <h3 class="text-lg font-bold mb-4">Assessments Affected by Deletion</h3>
                        <div id="deleteAffectedList" class="mb-4 text-sm max-h-64 overflow-y-auto"></div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="px-4 py-2 bg-gray-300 rounded"
                                onclick="closeDeleteAffectedModal()">Cancel</button>
                            <button type="button" id="confirmDeletePeriodBtn"
                                class="px-4 py-2 bg-red-600 text-white rounded">Delete Anyway</button>
                        </div>
                    </div>
                </div>

                <!-- Modal for listing reviewed employees -->
                <div id="reviewedEmployeesModal"
                    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl relative">
                        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
                            onclick="closeReviewedEmployeesModal()">&times;</button>
                        <h3 class="text-lg font-bold mb-2">Employees Reviewed</h3>
                        <div class="mb-2 text-sm text-gray-700">
                            <span id="reviewedEmployeesFacility"></span> | <span id="reviewedEmployeesPeriod"></span>
                        </div>
                        <div id="reviewedEmployeesList" class="mb-4 text-sm max-h-96 overflow-y-auto">
                            <!-- List will be loaded here by JS -->
                        </div>
                        <div class="flex justify-end">
                            <button class="px-4 py-2 bg-gray-300 rounded"
                                onclick="closeReviewedEmployeesModal()">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // ...existing code...
                    var deleteBtn = document.getElementById('deletePeriodBtn');
                    if (deleteBtn) {
                        deleteBtn.onclick = function() {
                            var periodSelect = document.getElementById('assessmentPeriodSelect');
                            var selectedId = periodSelect.value;
                            if (!selectedId) {
                                alert('Please select an assessment period to delete.');
                                return;
                            }
                            if (!confirm('Are you sure you want to delete this assessment period? This action cannot be undone.')) return;
                            // CSRF token
                            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                            if (!tokenMeta) {
                                alert('CSRF token missing.');
                                return;
                            }
                            var token = tokenMeta.getAttribute('content');
                            fetch('/admin/employees/performance-assessment/period/' + selectedId, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async response => {
                                let data;
                                let rawText = await response.text();
                                try {
                                    data = JSON.parse(rawText);
                                } catch (err) {
                                    alert('Delete failed.');
                                    return;
                                }
                                if (data.success) {
                                    alert('Assessment period deleted.');
                                    window.location.reload();
                                } else if (data.affected && Array.isArray(data.affected) && data.affected.length > 0) {
                                    // Show modal with affected assessments
                                    showDeleteAffectedModal(data.affected, selectedId, token);
                                } else if (data.warning && data.message) {
                                    alert(data.message);
                                } else {
                                    alert(data.message || 'Delete failed.');
                                }
                            })
                            .catch(err => {
                                alert('Delete failed: ' + (err && err.message ? err.message : err));
                            });
                        };
                    }
                    // Modal logic for affected assessments
                    window.showDeleteAffectedModal = function(affected, periodId, token) {
                        var modal = document.getElementById('deleteAffectedModal');
                        var list = document.getElementById('deleteAffectedList');
                        var confirmBtn = document.getElementById('confirmDeletePeriodBtn');
                        if (!modal || !list || !confirmBtn) return;
                        list.innerHTML = '<ul class="list-disc pl-5">' + affected.map(function(a) {
                            return '<li><strong>' + (a.employee_name || 'Employee ID: ' + a.emp_id) + '</strong> (' + (a.assessment_date || 'No date') + ')</li>';
                        }).join('') + '</ul>';
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        confirmBtn.onclick = function() {
                            // Actually delete with force flag
                            fetch('/admin/employees/performance-assessment/period/' + periodId + '?force=1', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async response => {
                                let data;
                                let rawText = await response.text();
                                try {
                                    data = JSON.parse(rawText);
                                } catch (err) {
                                    alert('Delete failed.');
                                    return;
                                }
                                if (data.success) {
                                    alert('Assessment period deleted.');
                                    window.location.reload();
                                } else {
                                    alert(data.message || 'Delete failed.');
                                }
                            })
                            .catch(err => {
                                alert('Delete failed: ' + (err && err.message ? err.message : err));
                            });
                        };
                    };
                    window.closeDeleteAffectedModal = function() {
                        var modal = document.getElementById('deleteAffectedModal');
                        if (modal) {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }
                    };
                });
            </script>
        </div>

    </div>
    @if (!empty($reviewDate))
    <div id="review-date-warning"
        class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 flex items-center"
        style="display:none;">
        <svg class="h-6 w-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
        <span><strong>Warning:</strong> This assessment has already been submitted (Review Date: <strong>{{ $reviewDate
                }}</strong>). Further changes may not be allowed or may require special permission.</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                if (!sessionStorage.getItem('reviewDateWarned')) {
                    var warnDiv = document.getElementById('review-date-warning');
                    if (warnDiv) warnDiv.style.display = 'flex';
                    sessionStorage.setItem('reviewDateWarned', '1');
                }
            });
    </script>
    @endif
    <table class="min-w-full border text-xs md:text-sm mb-4">
        <tr>
            <td class="border px-2 py-1 font-semibold" width="20%">Name</td>
            <td class="border px-2 py-1">{{ $employee->last_name }}, {{ $employee->first_name
                }}@if($employee->middle_name), {{ $employee->middle_name }}@endif</td>
            <td class="border px-2 py-1 font-semibold" width="20%">Review Date</td>
            <td class="border px-2 py-1">{{ $reviewDate ?? '' }}</td>
        </tr>
        @php
        $assignment = $employee->currentAssignment;
        $position = $assignment && $assignment->position ? $assignment->position->position_title : '';
        $department = $assignment && $assignment->department ? $assignment->department->dept_name : '';
        // Use reviewerName from controller if available, otherwise fallback to assignment logic
        if (!empty($reviewerName)) {
        $displayReviewerName = $reviewerName;
        } else {
        $displayReviewerName = '';
        if ($assignment && $assignment->reports_to_emp_id) {
        $reviewerEmp = \App\Models\Employee::where('id', $assignment->reports_to_emp_id)->first();
        if ($reviewerEmp && $reviewerEmp->user) {
        $displayReviewerName = $reviewerEmp->user->name;
        } elseif ($reviewerEmp) {
        $displayReviewerName = trim($reviewerEmp->last_name . ', ' . $reviewerEmp->first_name .
        ($reviewerEmp->middle_name ? ' ' . $reviewerEmp->middle_name : ''));
        }
        } else {
        // Default to logged-in user if no Report To
        $displayReviewerName = auth()->user()->name ?? '';
        }
        }
        @endphp
        <tr>
            <td class="border px-2 py-1 font-semibold">Position</td>
            <td class="border px-2 py-1">{{ $position }}</td>
            <td class="border px-2 py-1 font-semibold">Date Employed</td>
            <td class="border px-2 py-1">{{ $employee->original_hire_dt ?? '' }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1 font-semibold">Department</td>
            <td class="border px-2 py-1">{{ $department }}</td>
            <td class="border px-2 py-1 font-semibold">Review Type</td>
            <td class="border px-2 py-1">{{ $reviewType ?? '' }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1 font-semibold">Reviewer's Name</td>
            <td class="border px-2 py-1">{{ $displayReviewerName }}</td>
            <td class="border px-2 py-1 font-semibold">Reviewer's Position</td>
            <td class="border px-2 py-1">
                @php
                $reviewerPosition = '';
                if (!empty($assignment) && $assignment->reports_to_emp_id) {
                $reviewerEmp = \App\Models\Employee::where('id', $assignment->reports_to_emp_id)->first();
                if ($reviewerEmp && $reviewerEmp->currentAssignment && $reviewerEmp->currentAssignment->position) {
                $reviewerPosition = $reviewerEmp->currentAssignment->position->position_title;
                }
                }
                @endphp
                {{ $reviewerPosition }}
            </td>
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
                <td class="border px-2 py-1">The employee exceeds the majority of performance expectations.</td>
                <td class="border px-2 py-1">E = Exceeds</td>
                <td class="border px-2 py-1">3</td>
            </tr>
            <tr>
                <td class="border px-2 py-1">The employee meets performance expectations with occasional deviations
                    above and below expectations.</td>
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
<!-- Modal for creating new assessment period -->
<div id="newPeriodModal" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    @php
    // Group assessment periods by year for JS
    $periodsByYear = [];
    if (!empty($assessmentPeriods)) {
    foreach ($assessmentPeriods as $p) {
    $y = isset($p->period_year) ? $p->period_year : (\Carbon\Carbon::parse($p->eff_date)->year ?? null);
    if ($y !== null) {
    $periodsByYear[$y][] = [
    'period_sequence' => $p->period_sequence ?? 0,
    'eff_date' => $p->eff_date,
    ];
    }
    }
    }
    @endphp
    <script>
        window.assessmentPeriodsByYear = @json($periodsByYear);
        window.assessmentPeriods = @json($assessmentPeriods);
    </script>
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeNewPeriodModal()">&times;</button>
        <h3 id="periodModalTitle" class="text-lg font-bold mb-4">Create Assessment Period</h3>
        <form id="newPeriodForm">
            <!-- PART F Modal hidden field for assessment_period_id -->
            <input type="hidden" id="verifyAssessmentPeriodIdF" name="assessment_period_id" value="">
            <input type="hidden" id="newPeriodIdInput" name="id" value="">
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Period From <span
                        class="text-red-600">*</span></label>
                <input type="date" name="date_from" id="newPeriodDateFromInput" class="border rounded px-2 py-1 w-full"
                    required>
                <span id="newPeriodDateFromError" class="text-red-600 text-sm hidden">Start date is required.</span>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Period To <span class="text-red-600">*</span></label>
                <input type="date" name="date_to" id="newPeriodDateToInput" class="border rounded px-2 py-1 w-full"
                    required>
                <span id="newPeriodDateToError" class="text-red-600 text-sm hidden">End date is required.</span>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Type of Review <span class="text-red-600">*</span></label>
                <select name="review_type" id="newPeriodReviewTypeInput" class="border rounded px-2 py-1 w-full"
                    required>
                    <option value="A" selected>Annual</option>
                    <option value="Q">Quarterly</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeNewPeriodModal()"
                    class="mr-2 px-4 py-2 bg-gray-300 rounded cursor-pointer">Cancel</button>
                <button id="periodModalSubmitBtn" type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded cursor-pointer">Create</button>
            </div>
        </form>
    </div>
</div>