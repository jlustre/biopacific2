<script>
    window.assessmentPeriods = @json($assessmentPeriods);
    window.performanceAssessmentStatuses = @json($performanceAssessmentStatuses ?? []);
    window.competencyAssessmentStatuses = @json($competencyAssessmentStatuses ?? []);
    window.currentFacilityId = '{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->id : '' }}';
</script>

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

<div id="reviewedEmployeesModal"
    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeReviewedEmployeesModal()">&times;</button>
        <h3 class="text-lg font-bold mb-2">Employees Reviewed</h3>
        <div class="mb-2 text-sm text-gray-700">
            <span id="reviewedEmployeesFacility"></span> | <span id="reviewedEmployeesPeriod"></span>
        </div>
        <div id="reviewedEmployeesList" class="mb-4 text-sm max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end">
            <button class="px-4 py-2 bg-gray-300 rounded"
                onclick="closeReviewedEmployeesModal()">Close</button>
        </div>
    </div>
</div>

<div id="allAssessmentPeriodsModal"
    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeAllAssessmentPeriodsModal()">&times;</button>
        <h3 class="text-lg font-bold mb-2">Assessment Periods</h3>
        <p id="allAssessmentPeriodsDescription" class="mb-4 text-sm text-gray-600">Select a period to load this employee assessment for that assessment window.</p>
        <div id="allAssessmentPeriodsList" class="mb-4 text-sm max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end">
            <button class="px-4 py-2 bg-gray-300 rounded"
                onclick="closeAllAssessmentPeriodsModal()">Close</button>
        </div>
    </div>
</div>

<div id="newPeriodModal" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeNewPeriodModal()">&times;</button>
        <h3 id="periodModalTitle" class="text-lg font-bold mb-4">Create Assessment Period</h3>
        <form id="newPeriodForm">
            <input type="hidden" id="newPeriodIdInput" name="id" value="">
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Period From <span class="text-red-600">*</span></label>
                <input type="date" name="date_from" id="newPeriodDateFromInput" class="border rounded px-2 py-1 w-full" required>
                <span id="newPeriodDateFromError" class="text-red-600 text-sm hidden">Start date is required.</span>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Period To <span class="text-red-600">*</span></label>
                <input type="date" name="date_to" id="newPeriodDateToInput" class="border rounded px-2 py-1 w-full" required>
                <span id="newPeriodDateToError" class="text-red-600 text-sm hidden">End date is required.</span>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Type of Review <span class="text-red-600">*</span></label>
                <select name="review_type" id="newPeriodReviewTypeInput" class="border rounded px-2 py-1 w-full" required>
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