<div id="verifyModalF" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeVerifyModalF()">&times;</button>
        <h3 class="text-lg font-bold mb-4">Performance Assessment</h3>
        <form id="verifyFormF">
            <input type="hidden" name="emp_id" id="verifyEmpIdF">
            <input type="hidden" name="item_key" id="verifyItemKeyF">
            <input type="hidden" name="eff_date" id="verifyEffDateF">
            <div class="mb-3">
                <label class="block font-semibold mb-1">Rating <span class="text-red-600">*</span></label>
                <select name="rating" id="verifyRatingF" class="border rounded px-2 py-1 w-full" required>
                    <option value="">Select rating</option>
                    <option value="1">Below</option>
                    <option value="2">Meets</option>
                    <option value="3">Exceeds</option>
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
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeVerifyModalF()"
                    class="mr-2 px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>