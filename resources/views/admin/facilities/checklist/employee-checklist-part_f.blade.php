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
                                @if(auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id)
                            readonly @endif>
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
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">Save</button>
                <button type="submit" name="action" value="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 cursor-pointer">Submit This
                    Assessment</button>
            </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('areasDevelopmentForm');
    var submitBtn = form.querySelector('button[type="submit"][name="action"][value="submit"]');
    if (!form || !submitBtn) return;

    // Disable submit by default
    submitBtn.disabled = true;

    // Track if form is dirty
    let isDirty = false;
    let lastSavedData = new FormData(form);

    // Helper to compare form data
    function isFormChanged() {
        let current = new FormData(form);
        for (let [key, value] of current.entries()) {
            if (lastSavedData.get(key) !== value) return true;
        }
        for (let [key, value] of lastSavedData.entries()) {
            if (current.get(key) !== value) return true;
        }
        return false;
    }

    // Listen for changes
    form.addEventListener('input', function() {
        isDirty = isFormChanged();
        submitBtn.disabled = isDirty;
    });
    form.addEventListener('change', function() {
        isDirty = isFormChanged();
        submitBtn.disabled = isDirty;
    });

    // On save, update lastSavedData and enable submit
    form.addEventListener('submit', function(e) {
        var clickedBtn = document.activeElement;
        if (clickedBtn && clickedBtn.name === 'action' && clickedBtn.value === 'save') {
            // Wait for backend to respond before enabling submit
            setTimeout(function() {
                lastSavedData = new FormData(form);
                isDirty = false;
                submitBtn.disabled = false;
            }, 500); // Adjust if needed for AJAX
        } else if (clickedBtn && clickedBtn.name === 'action' && clickedBtn.value === 'submit') {
            // Optionally, disable submit to prevent double submit
            submitBtn.disabled = true;
        }
    });
});
    </script>
</div>
</div>