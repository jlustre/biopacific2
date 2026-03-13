@php
use Illuminate\Support\Str;
$defaultFileName = 'application_form_' . $application->id . '_' . Str::slug(trim(($application->last_name ??
'applicant') . '-' . ($application->first_name ?? ''))) . '.pdf';
@endphp
@props([
'facility',
'application',
])

@php
$latestReturnedActivity = \App\Models\HiringActivityLog::where('pre_employment_application_id', $application->id)
->where('activity_type', 'returned')
->orderByDesc('created_at')
->first();
$latestReturnedNotes = $latestReturnedActivity?->notes ?? '';
$latestReturnedForm = $latestReturnedActivity?->form_type ?? '';
$existingDocuments = \App\Models\EmployeeDocument::where('pre_employment_application_id', $application->id)
->where('document_type', 'application_form')
->latest('created_at')
->get();
@endphp

<div x-data="statusModal()" @keydown.escape="showModal = false">
    <a href="{{ route('admin.facility.hiring', $facility) }}"
        class="text-teal-600 hover:text-teal-700 font-semibold flex items-center gap-2 pt-4">
        <i class="fas fa-arrow-left"></i> Back to Hiring
    </a>

    <div class="flex gap-3 mt-4 flex-wrap">
        @if($application->status !== 'completed')
        <button type="button" @click="openModal('returned')"
            class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold flex items-center gap-2">
            <i class="fas fa-undo"></i> Mark as Returned
        </button>
        @endif

        @if($application->status !== 'completed' && $latestReturnedActivity)
        <button type="button" id="edit-recent-notes-btn" @click="openModal('returned')"
            class="px-6 py-3 bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 font-semibold flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit Recent Notes
        </button>
        @endif

        @if($application->status !== 'completed')
        <button type="button" @click="openModal('completed')"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle"></i> Mark as Completed
        </button>
        @endif

        @if($application->status === 'completed')
        <button type="button" @click="openModal('returned')"
            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold flex items-center gap-2">
            <i class="fas fa-arrow-rotate-left"></i> Return to Applicant
        </button>
        @endif

        @if($application->status === 'completed')
        <button type="button" @click="openModal('pdf')"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> Generate A PDF
        </button>

        @if($existingDocuments->count() > 0)
        <button type="button" @click="openModal('pdf-list')"
            class="px-6 py-3 bg-slate-600 text-white rounded-lg hover:bg-slate-700 font-semibold flex items-center gap-2">
            <i class="fas fa-list"></i> View PDF Files ({{ $existingDocuments->count() }})
        </button>
        @endif
        @else
        <a href="{{ route('admin.facility.pre-employment.pdf', ['facility' => $facility->id, 'application' => $application->id]) }}"
            target="_blank" rel="noopener noreferrer"
            class="px-6 py-3 bg-slate-600 text-white rounded-lg hover:bg-slate-700 font-semibold flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> Create PDF Document
        </a>
        @endif
    </div>

    @if($application->status !== 'completed' && $latestReturnedActivity)
    <p class="text-sm text-amber-700 mt-2">
        <i class="fas fa-info-circle mr-1"></i>
        You can still edit the most recent notes by clicking the Edit Recent Notes button.
    </p>
    @endif

    <!-- Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        @click.self="showModal = false">
        <!-- Form Modal (Returned/Completed) -->
        <div @click.stop class="bg-white rounded-lg shadow-xl max-w-lg w-full flex flex-col max-h-[calc(100vh-2rem)]"
            x-show="currentStatus !== 'pdf' && currentStatus !== 'pdf-list'">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 px-6 pt-6 flex-shrink-0">
                <span
                    x-text="currentStatus === 'returned' ? (previousStatus === 'completed' ? 'Return Application to Applicant' : 'Mark Application as Returned') : 'Mark Application as Completed'"></span>
            </h2>

            <form :action="formAction" method="POST" class="flex flex-col flex-1 overflow-hidden">
                <div class="overflow-y-auto px-6 flex-1">
                    @csrf
                    <input type="hidden" name="status" :value="currentStatus">

                    <!-- Context Message for Returning Completed -->
                    <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded"
                        x-show="currentStatus === 'returned' && previousStatus === 'completed'">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            This application was already marked as completed. You are returning it to the applicant for
                            additional information or corrections.
                        </p>
                    </div>

                    <!-- Form Selection (for Returned status) -->
                    <div class="mb-6" x-show="currentStatus === 'returned'">
                        <label for="form_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-alt text-orange-600 mr-1"></i> Which Form/Section Needs Changes?
                            <span class="text-red-600">*</span>
                        </label>
                        <select name="form_type" id="form_type" x-ref="formType"
                            :required="currentStatus === 'returned'"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            @change="updateFormDescription">
                            <option value="">-- Select a Form Section --</option>
                            <option value="application_form">Application Form</option>
                            <option value="personal">Personal Information</option>
                            <option value="position">Position Desired</option>
                            <option value="drivers_license">Driver's License</option>
                            <option value="work_authorization">Work Authorization</option>
                            <option value="work_experience">Work Experience</option>
                            <option value="education">Education</option>
                            <option value="previous_addresses">Previous Addresses</option>
                            <option value="other">Other/Multiple Sections</option>
                        </select>
                        <p class="text-sm text-gray-600 mt-1">Select which form section needs to be corrected.</p>
                        @error('form_type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes/Comments Section -->
                    <div class="mb-6" x-show="currentStatus === 'returned'">
                        <label for="notes-returned" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-orange-600 mr-1"></i> Comments/Notes (Required for
                            Returned)
                            <span class="text-red-600">*</span>
                        </label>
                        <textarea name="notes" id="notes-returned" x-ref="returnedNotes" rows="6"
                            :required="currentStatus === 'returned'" :disabled="currentStatus !== 'returned'"
                            :placeholder="previousStatus === 'completed' ? 'Explain what additional information or corrections are needed...' : 'Specify what needs to be changed or is missing in the application...'"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-vertical"
                            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">{{ old('notes') }}</textarea>
                        <p class="text-sm text-gray-600 mt-1" x-show="previousStatus === 'completed'">
                            Explain what additional information the applicant needs to provide for the selected section.
                        </p>
                        <p class="text-sm text-gray-600 mt-1" x-show="previousStatus !== 'completed'">
                            Explain what the applicant needs to correct or provide.
                        </p>
                        @error('notes')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Optional Notes Section for Completed -->
                    <div class="mb-6" x-show="currentStatus === 'completed'">
                        <label for="notes-completed" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-green-600 mr-1"></i> Additional Notes (Optional)
                        </label>
                        <textarea name="notes" id="notes-completed" rows="4" :disabled="currentStatus !== 'completed'"
                            placeholder="Add any final comments or observations..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-vertical"
                            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Confirmation Message -->
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded">
                        <p class="text-sm text-amber-800">
                            <span x-show="currentStatus === 'returned' && previousStatus === 'completed'">
                                <strong>Note:</strong> This application will be returned to the applicant for additional
                                information or corrections. The status will change from Completed to Returned.
                            </span>
                            <span x-show="currentStatus === 'returned' && previousStatus !== 'completed'">
                                <strong>Note:</strong> Marking as returned will notify the applicant to review and
                                resubmit
                                their application.
                            </span>
                            <span x-show="currentStatus === 'completed'">
                                <strong>Note:</strong> Marking as completed indicates all pre-employment requirements
                                have
                                been fulfilled.
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 justify-end px-6 py-6 border-t border-gray-200 flex-shrink-0">
                    <button type="button" @click="showModal = false"
                        class="px-6 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">
                        Cancel
                    </button>
                    <button type="submit"
                        :class="currentStatus === 'returned' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700'"
                        class="px-6 py-2 text-white rounded-lg font-semibold flex items-center gap-2">
                        <i :class="currentStatus === 'returned' ? 'fas fa-undo' : 'fas fa-check-circle'"></i>
                        <span
                            x-text="currentStatus === 'returned' ? (previousStatus === 'completed' ? 'Return to Applicant' : 'Mark as Returned') : 'Mark as Completed'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- PDF Generation Modal -->
        <div @click.stop class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6"
            x-show="currentStatus === 'pdf'">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-file-pdf text-blue-600 mr-2"></i>Generate PDF Document
            </h2>
            <div class="mb-6">
                <p class="text-gray-700 mb-4">Select a template to generate the PDF document for this application.</p>
                <div class="space-y-2 mb-6">
                    <div class="block p-4 border-2 border-gray-300 rounded-lg">
                        <div class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-file-pdf text-blue-600"></i> Standard Application Form
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Complete application form with all sections</p>
                        <div class="flex gap-3 mt-4">
                            <form method="POST"
                                action="{{ route('admin.facility.pre-employment.pdf', ['facility' => $facility->id, 'application' => $application->id, 'template' => 'standard']) }}">
                                @csrf
                                <input type="hidden" name="mode" value="save">
                                <div class="mb-2">
                                    <label class="block text-xs font-semibold mb-1">File Name</label>
                                    <input type="text" name="file_name" value="{{ old('file_name', $defaultFileName) }}"
                                        class="form-input rounded border-gray-300 w-full" required>
                                    @error('file_name')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center gap-2">
                                    <i class="fas fa-save"></i> Generate and Save
                                </button>
                            </form>
                            <a href="{{ route('admin.facility.pre-employment.pdf', ['facility' => $facility->id, 'application' => $application->id, 'template' => 'standard', 'mode' => 'view']) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 font-semibold flex items-center gap-2">
                                <i class="fas fa-eye"></i> View PDF Only
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-6">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Note:</strong> The PDF will be generated and stored automatically.
                </p>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="showModal = false"
                    class="px-6 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">
                    Close
                </button>
            </div>
        </div>

        <!-- PDF List Modal -->
        <div @click.stop class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6 max-h-96 overflow-y-auto"
            x-show="currentStatus === 'pdf-list'">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-list text-slate-600 mr-2"></i>Generated PDF Files
            </h2>
            <div class="space-y-3">
                @forelse($existingDocuments as $document)
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <i class="fas fa-file-pdf text-red-600 text-lg flex-shrink-0"></i>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 truncate text-sm">{{ $document->file_name }}</p>
                            <p class="text-xs text-gray-600">
                                {{ number_format($document->file_size / 1024, 2) }} KB •
                                {{ $document->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 ml-2">
                        <a href="{{ route('admin.facility.document.download', ['facility' => $facility->id, 'document' => $document->id]) }}"
                            class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm flex items-center gap-1">
                            <i class="fas fa-download text-xs"></i> Download
                        </a>
                        <form
                            action="{{ route('admin.facility.document.delete', ['facility' => $facility->id, 'document' => $document->id]) }}"
                            method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this PDF?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm flex items-center gap-1">
                                <i class="fas fa-trash text-xs"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-gray-600 text-center py-4">No PDF files generated yet.</p>
                @endforelse
            </div>
            <div class="flex gap-3 justify-end mt-6 pt-4 border-t">
                <button type="button" @click="showModal = false"
                    class="px-6 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function statusModal() {
    return {
        showModal: false,
        currentStatus: null,
        previousStatus: '{{ $application->status }}',
        formAction: '',
        lastReturnedNotes: @json($latestReturnedNotes),
        lastReturnedForm: @json($latestReturnedForm),

        openModal(status) {
            this.currentStatus = status;
            if (status === 'returned' || status === 'completed') {
                this.formAction = "{{ route('admin.facility.pre-employment.status', ['facility' => $facility->id, 'application' => $application->id]) }}";
            }
            this.showModal = true;
            
            this.$nextTick(() => {
                const notesField = this.$refs.returnedNotes;
                const formTypeField = this.$refs.formType;
                if (status === 'returned') {
                    if (formTypeField) formTypeField.value = '';
                    if (notesField) {
                        // Create a new note when returning from completed status
                        // Don't prefill with old notes to ensure fresh documentation
                        notesField.value = '';
                        notesField.focus();
                    }
                } else {
                    if (formTypeField) formTypeField.value = '';
                    if (notesField) notesField.value = '';
                }
            });
        },

        updateFormDescription() {
            const formTypeSelect = this.$refs.formType;
            const notesField = this.$refs.returnedNotes;
            
            if (formTypeSelect && notesField && formTypeSelect.value) {
                const formLabels = {
                    'application_form': 'Application Form section',
                    'personal': 'Personal Information section',
                    'position': 'Position Desired section',
                    'drivers_license': "Driver's License section",
                    'work_authorization': 'Work Authorization section',
                    'work_experience': 'Work Experience section',
                    'education': 'Education section',
                    'previous_addresses': 'Previous Addresses section',
                    'other': 'Multiple sections or other areas'
                };
                
                const selectedForm = formLabels[formTypeSelect.value] || '';
                if (selectedForm && !notesField.value.includes(selectedForm)) {
                    const prefix = `[${selectedForm}] `;
                    if (!notesField.value.startsWith('[')) {
                        notesField.value = prefix + notesField.value;
                    }
                }
            }
        }
    }
}
</script>