<div class="mb-4">
    <div class="flex flex-col md:flex-row mb-2 items-center justify-between">
        <div class="ml-2">
            <label class="font-semibold mr-2">Facility Name:</label>
            <input type="text" class="border rounded px-2 py-1 mr-4 width-auto"
                value="{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->name : '' }}"
                readonly style="flex-grow:1; min-width:100%; max-width:100%;">
        </div>
        <div class="ml-2">
            <label for="assessmentPeriodSelect" class="font-semibold mr-2">Assessment Period:</label>
            <select id="assessmentPeriodSelect" name="eff_date" class="border rounded px-2 py-1">
                @foreach($assessmentPeriods as $period)
                <option value="{{ $period->eff_date }}" @if($selectedEffDate==$period->eff_date) selected @endif>
                    {{ $period->eff_date }}
                </option>
                @endforeach
            </select>
            <button type="button" id="addNewPeriodBtn" class="ml-2 px-2 py-1 bg-green-600 text-white rounded text-xs"
                title="Create new assessment period">New Period</button>
        </div>

    </div>
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
        $reviewerName = '';
        if ($assignment && $assignment->reports_to_emp_id) {
        $reviewerEmp = \App\Models\Employee::where('id', $assignment->reports_to_emp_id)->first();
        if ($reviewerEmp && $reviewerEmp->user) {
        $reviewerName = $reviewerEmp->user->name;
        } elseif ($reviewerEmp) {
        $reviewerName = trim($reviewerEmp->last_name . ', ' . $reviewerEmp->first_name . ($reviewerEmp->middle_name ? '
        ' . $reviewerEmp->middle_name : ''));
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
            <td class="border px-2 py-1 font-semibold">Review</td>
            <td class="border px-2 py-1">{{ $reviewType ?? '' }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1 font-semibold">Reviewer's Name</td>
            <td class="border px-2 py-1">{{ $reviewerName }}</td>
            <td class="border px-2 py-1 font-semibold">Type of Review</td>
            <td class="border px-2 py-1">{{ $reviewType ?? '' }}</td>
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
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeNewPeriodModal()">&times;</button>
        <h3 class="text-lg font-bold mb-4">Create Assessment Period</h3>
        <form id="newPeriodForm">
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Period Date <span
                        class="text-red-600">*</span></label>
                <input type="date" name="new_eff_date" id="newEffDateInput" class="border rounded px-2 py-1 w-full"
                    required>
                <span id="newEffDateError" class="text-red-600 text-sm hidden">Date is required.</span>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeNewPeriodModal()"
                    class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Create</button>
            </div>
        </form>
    </div>
</div>