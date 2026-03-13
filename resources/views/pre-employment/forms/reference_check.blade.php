<div class="flex-1">
    <label class="block font-semibold mb-1">Applicant's Name</label>
    <input type="text"
        value="{{ $preEmployment?->first_name ?? $jobApplication?->first_name ?? '' }} {{ $preEmployment?->last_name ?? $jobApplication?->last_name ?? '' }}"
        class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
        readonly>
</div>
<div class="container mx-auto py-8 max-w-2xl" x-data="{ open: true }">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">
            Confidential Reference Check #{{ $referenceCheckNumber ?? 1 }}
        </h1>
        <div class="flex items-center gap-2">
            <button type="button" @click="open = !open"
                class="px-3 py-1 rounded bg-teal-100 text-teal-800 font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 cursor-pointer">
                <span x-show="open">Hide</span>
                <span x-show="!open">Show</span>
            </button>
            @if(($referenceCheckTotal ?? 2) > 1)
            <form method="POST" action="{{ route('pre-employment.reference-checks.delete', $referenceCheck->id ?? 0) }}"
                onsubmit="return confirm('Are you sure you want to delete this reference check?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-3 py-1 rounded bg-red-100 text-red-800 font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-red-400 cursor-pointer">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            @endif
        </div>
    </div>
    <div x-show="open" x-transition>
        <p class="mb-6">
            The person named below has applied for employment. He/she has authorized the collection of any information
            concerning past employment with your organization. The Company deals in long-term health care and it is of
            the
            utmost importance to us that we hire the right person for the job. Therefore we would appreciate your reply
            to
            the questions below. Thank you.
        </p>
        <form method="POST" action="{{ route('pre-employment.reference-checks.save', $referenceCheck->id ?? 0) }}">
            @csrf
            <input type="hidden" name="reference_check_number" value="{{ $referenceCheckNumber ?? 1 }}">
            <input type="hidden" name="reference_check_id" value="{{ $referenceCheck->id ?? 0 }}">
            <input type="hidden" name="reference_index"
                value="{{ $referenceCheck->reference_index ?? $referenceCheckNumber ?? 1 }}">
            <div class="gap-y-6 flex flex-col">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Applicant's Name</label>
                        <input type="text" name="applicant_name"
                            value="{{ old('applicant_name', $referenceCheck->applicant_name ?? ($preEmployment?->first_name ?? $jobApplication?->first_name ?? '') . ' ' . ($referenceCheck->applicant_last_name ?? ($preEmployment?->last_name ?? $jobApplication?->last_name ?? '')) ) }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            required>
                    </div>
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Position Applied for</label>
                        <input type="text" name="position_applied_for"
                            value="{{ old('position_applied_for', $referenceCheck->position_applied_for ?? ($positions?->firstWhere('id', $preEmployment?->position_id ?? $jobApplication?->position_id ?? null)?->title ?? '')) }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            required>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-4 my-1">
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Company</label>
                        <input type="text" name="company" value="{{ old('company', $referenceCheck->company ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
                    </div>
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Company Address</label>
                        <input type="text" name="company_address"
                            value="{{ old('company_address', $referenceCheck->company_address ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            id="supervisor_field"
                            onchange="document.getElementById('contact_name_field').value = this.value;">
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-4 my-1">
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Reference Name</label>
                        <input type="text" name="reference_name"
                            value="{{ old('reference_name', $referenceCheck->reference_name ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            id="supervisor_field"
                            onchange="document.getElementById('contact_name_field').value = this.value;">
                    </div>
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Reference Title</label>
                        <input type="text" name="reference_title"
                            value="{{ old('reference_title', $referenceCheck->reference_title ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            id="supervisor_field"
                            onchange="document.getElementById('contact_name_field').value = this.value;">
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-4 my-1">
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Reference Phone</label>
                        <input type="text" name="reference_phone"
                            value="{{ old('reference_phone', $referenceCheck->reference_phone ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
                    </div>
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Reference Email</label>
                        <input type="email" name="reference_email"
                            value="{{ old('reference_email', $referenceCheck->reference_email ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2">
                    </div>
                </div>

                <div class="italic text-gray-700 my-1">
                    I hereby release from all liability the Company or people named above, and authorize him or her to
                    release all information regarding my employment with them.
                </div>
                <div class="text-gray-700 text-sm mb-4">
                    By entering your name and date below, you give permission for the hiring manager to contact your
                    references.
                </div>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Applicant's Name & Signature</label>
                        <input type="text" name="applicant_signature"
                            value="{{ old('applicant_signature', $referenceCheck->applicant_signature ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            required>
                    </div>
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Date</label>
                        <input type="date" name="signature_date"
                            value="{{ old('signature_date', $referenceCheck->signature_date ?? '') }}"
                            class="form-input w-full border-2 border-teal-500 focus:border-teal-700 focus:ring-teal-200 rounded-lg px-4 py-2"
                            required>
                    </div>
                </div>
                <button type="submit"
                    class="bg-gray-100 hover:bg-teal-100 text-teal-700 font-semibold py-2 px-6 rounded-lg shadow transition-colors duration-150 mt-4 cursor-pointer">
                    <i class="fas fa-save"></i>
                    Save Reference Check
                </button>
        </form>
        {{--
        <hr class="my-6">
        @include('pre-employment.forms.reference_check_department', [
        'referenceCheck' => $referenceCheck,
        'readonly' => $readonly ?? false
        ]) --}}

        <script>
            document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            if (!form) return;
            form.addEventListener('submit', function (e) {
                // Find the section containing the reference_contacted_date field
                const section = document.querySelector('input[name="reference_contacted_date"]').closest('.flex-1');
                const field = document.querySelector('input[name="reference_contacted_date"]');
                // Only require if visible
                if (field && section && section.offsetParent !== null) {
                    field.required = true;
                } else if (field) {
                    field.required = false;
                }
            });
        });
        </script>
    </div>
    <!-- Add Another Reference Check button is now handled in portal.blade.php only -->
</div>