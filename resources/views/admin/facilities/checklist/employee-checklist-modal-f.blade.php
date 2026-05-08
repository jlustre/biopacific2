<div id="verifyModalF" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeVerifyModalF()">&times;</button>
        <h3 class="text-lg font-bold mb-4">Employee Assessment</h3>
        <form id="verifyFormF">
            <input type="hidden" name="employee_num" id="verifyEmpIdF">
            <input type="hidden" name="item_key" id="verifyItemKeyF">
            <input type="hidden" name="assessment_period_id" id="verifyAssessmentPeriodIdF">
            <input type="hidden" name="eff_date" id="verifyEffDateF">
            <input type="hidden" name="item_label" id="verifyItemLabelF">
            <input type="hidden" name="source_item_id" id="verifySourceItemIdF">
            <div class="mb-3">
                <label class="block font-semibold mb-1">Rating <span class="text-red-600">*</span></label>
                <select name="rating" id="verifyRatingF" class="border rounded px-2 py-1 w-full" required>
                    <option value="">Select rating</option>
                    <option value="E">Excellent (3)</option>
                    <option value="S">Satisfactory (2)</option>
                    <option value="U">Unsatisfactory (1)</option>
                    <option value="N">Not Applicable</option>
                </select>
                <span id="ratingFError" class="text-red-600 text-sm hidden">Rating is required.</span>
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessment Date</label>
                <input type="date" name="assessment_date" id="verifyAssessmentDateF"
                    class="border rounded px-2 py-1 w-full">
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Assessed By</label>
                <input type="text" name="assessed_by_name" id="verifyAssessedByF"
                    class="border rounded px-2 py-1 w-full" readonly>
                <input type="hidden" name="assessed_by" id="verifyAssessedByIdF">
            </div>
            <div class="mb-3">
                <label class="block font-semibold mb-1">Comments</label>
                <textarea name="comments" id="verifyCommentsF" class="border rounded px-2 py-1 w-full"></textarea>
                <span id="commentsFError" class="text-red-600 text-sm hidden">Comments are required for an unsatisfactory rating.</span>
            </div>
            <div class="mb-3 border-t pt-3">
                <div class="block font-semibold mb-1">Assessment History</div>
                <div id="assessmentHistoryEmptyF" class="text-sm text-gray-500">No prior assessments for this item.</div>
                <ul id="assessmentHistoryListF" class="space-y-2 text-sm"></ul>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeVerifyModalF()"
                    class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button id="verifySaveBtnF" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
    window.assessmentItemHistories = @json($assessmentItemHistories ?? []);
    window.users = @json(isset($users) ? $users->pluck('name', 'id') : []);
</script>