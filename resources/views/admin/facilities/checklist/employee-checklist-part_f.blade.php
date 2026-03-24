<div id="partF" class="tab-content hidden">
    <h2 class="text-xl font-bold mb-4">PART F - EMPLOYEE PERFORMANCE APPRAISAL</h2>
    @include('admin.facilities.checklist.employee-appraisal-form')

    <!-- PERFORMANCE AREAS (Dynamic from DB) -->
    @include('admin.facilities.checklist.employee-performance-areas')

    <form id="areasDevelopmentForm" method="POST"
        action="{{ route('admin.employees.areas_development.save', ['emp_id' => $employee->emp_id]) }}">
        <input type="hidden" name="assessment_period_id" value="{{ $selectedAssessmentPeriodId }}">
        @csrf
        @include('admin.facilities.checklist.employee-areas-development')

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
                        <td class="border px-2 py-1">
                            Supervisor: <input type="text" name="supervisor_name"
                                class="border rounded px-2 py-1 w-full mb-1" value="{{ $supervisorName ?? '' }}">
                        </td>
                        <td class="border px-2 py-1">
                            <label class="text-xs text-gray-600">Review Date <span
                                    class="text-red-500">*</span>:</label>
                            <input type="date" name="review_dt" class="border rounded px-2 py-1 w-auto"
                                value="{{ old('review_dt', isset($reviewDt) ? $reviewDt : '') }}" required
                                @if(!(auth()->check() && isset($employee->user_id) && auth()->id() !=
                            $employee->user_id)) @else readonly @endif>
                        </td>
                    </tr>
                    <tr>
                        <td class="border px-2 py-1">Employee: <input type="text" name="employee_name"
                                class="border rounded px-2 py-1 w-full"
                                value="{{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{ $employee->middle_name }}@endif"
                                readonly></td>
                        <td class="border px-2 py-1">
                            <label class="text-xs text-gray-600">Acknowledge Date:</label>
                            <input type="date" name="employee_acknowledge_dt" class="border rounded px-2 py-1 w-auto"
                                value="{{ old('employee_acknowledge_dt', isset($employeeAcknowledgeDt) ? $employeeAcknowledgeDt : '') }}"
                                @if(!(auth()->check() && isset($employee->user_id) && auth()->id() ==
                            $employee->user_id)) readonly @endif>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="flex justify-end space-x-2">
                <button type="submit" name="action" value="save"
                    class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500 cursor-pointer">Save</button>
                <button type="submit" name="action" value="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Submit This
                    Assessment</button>
            </div>
    </form>
</div>
</div>