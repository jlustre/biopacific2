<div class="grid grid-cols-1 gap-4 mb-3">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $this->storedReviewerName }}" readonly />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerTitle }}" readonly />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW SIGN DATE</label>
            <input
                type="text"
                readonly
                class="w-full rounded border border-gray-300 bg-slate-100 p-2"
                value="{{ $this->displayReviewSignDate }}"
                placeholder="Recorded automatically when the reviewer completes this section"
            />
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $this->storedEmployeeName }}" readonly />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeTitle }}" readonly />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE SIGN DATE</label>
            <input
                type="text"
                readonly
                class="w-full rounded border border-gray-300 bg-slate-100 p-2"
                value="{{ $this->displayEmployeeSignDate }}"
                placeholder="Recorded automatically when the employee signs"
            />
        </div>
    </div>
</div>
