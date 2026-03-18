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