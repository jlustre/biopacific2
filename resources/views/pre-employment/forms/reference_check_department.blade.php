<div x-data="{ openDept: false }" class="border rounded-lg">
    <div class="flex items-center justify-between px-4 py-3 cursor-pointer bg-gray-50" @click="openDept = !openDept">
        <h2 class="text-xl font-bold mb-0">To be completed by reference or head of department</h2>
        <button type="button"
            class="ml-2 px-2 py-1 rounded bg-teal-100 text-teal-800 font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-teal-400 cursor-pointer">
            <span x-show="openDept">Hide</span>
            <span x-show="!openDept">Show</span>
        </button>
    </div>
    <div x-show="openDept" x-transition class="p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Name of Person Contacted</label>
                <input type="text" name="contact_name"
                    value="{{ old('contact_name', $referenceCheck->contact_name ?? '') }}"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    id="contact_name_field" @if($readonly ?? false) readonly @endif>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Relationship To Applicant</label>
                <input type="text" name="contact_relationship"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
        </div>
        <h2 class="block font-bold my-1">Employment Details</h2>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">From</label>
                <input type="text" name="employment_from"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">To</label>
                <input type="text" name="employment_to"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Salary</label>
                <input type="text" name="salary"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Per</label>
                <input type="text" name="salary_per"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
        </div>
        <div>
            <label class="block font-semibold mt-2">Position and description of duties</label>
            <input type="text" name="position_description"
                class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                @if($readonly ?? false) readonly @endif>
        </div>
        <div>
            <label class="block font-semibold mt-2">Describe applicant's performance
                <span class="text-sm text-gray-500">(what are the applicant's strong/weak points in
                    comparison
                    with
                    the other people who are doing or have done the same job?)</span></label>
            <textarea name="performance" rows="3"
                class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                @if($readonly ?? false) readonly @endif></textarea>
        </div>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Signature</label>
                <input type="text" name="signature"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" name="title"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    @if($readonly ?? false) readonly @endif>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 md:justify-between md:items-end">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Date Contacted</label>
                <input type="date" name="reference_contacted_date"
                    class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                    x-ref="referenceContactedDate" @if($readonly ?? false) readonly @endif>
            </div>
        </div>
    </div>
</div>