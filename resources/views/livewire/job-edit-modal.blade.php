<div x-data="{ showEditModal: false, editingJobId: null, editJobData: null, isCopyingJob: false, editTitle: '', editEmploymentType: '', editDepartment: '', editReportingTo: '', editPostedAt: '', editStatus: '', editDescription: '', editActive: false, viewModalJobId: null, showActionModal: false, actionModalTitle: '', actionMessage: '', actionModalJobId: null }"
    x-on:open-edit-modal.window="showEditModal = true" x-on:close-edit-modal.window="showEditModal = false"
    x-on:open-view-modal.window="viewModalJobId = $event.detail.jobId"
    x-on:close-view-modal.window="viewModalJobId = null"
    x-on:open-action-modal.window="showActionModal = true; actionModalTitle = $event.detail.title; actionMessage = $event.detail.message; actionModalJobId = $event.detail.jobId"
    x-on:close-action-modal.window="showActionModal = false; actionModalTitle = ''; actionMessage = ''; actionModalJobId = null"
    x-show="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    style="display: none;">
    <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-blue-600 text-white p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold" x-text="isCopyingJob ? 'Copy Job Listing' : 'Edit Job Listing'">
            </h2>
            <button type="button"
                @click="showEditModal = false; editingJobId = null; editJobData = null; isCopyingJob = false"
                class="text-white hover:text-gray-200 text-2xl cursor-pointer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <form id="edit-job-form" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-2">Job Title *</label>
                        <select x-model="editTitle"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a position...</option>
                            @foreach($positions as $id => $positionTitle)
                            <option value="{{ $positionTitle }}">{{ $positionTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Employment Type *</label>
                        <select x-model="editEmploymentType"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select...</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Temporary">Temporary</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Department</label>
                        <select x-model="editDepartment"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select...</option>
                            @foreach($departments as $id => $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Reporting To *</label>
                        <select x-model="editReportingTo"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a supervisor...</option>
                            @foreach($supervisors as $id => $supervisorTitle)
                            <option value="{{ $supervisorTitle }}">{{ $supervisorTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Posted Date *</label>
                        <input type="date" x-model="editPostedAt"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Status</label>
                        <select x-model="editStatus"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="filled">Filled</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-2">Description *</label>
                    <textarea id="edit-description-editor" x-model="editDescription"
                        placeholder="Job responsibilities, requirements, etc." rows="8"
                        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="editActive" class="w-4 h-4 rounded">
                    <label class="font-semibold">Active (visible to applicants)</label>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="title" value="">
                <input type="hidden" name="employment_type" value="">
                <input type="hidden" name="department" value="">
                <input type="hidden" name="reporting_to" value="">
                <input type="hidden" name="posted_at" value="">
                <input type="hidden" name="status" value="">
                <input type="hidden" name="description" value="">
                <input type="hidden" name="active" value="">
            </form>
        </div>

        <div class="bg-gray-100 p-6 sticky bottom-0 flex gap-3">
            <button type="button" @click="saveTemplateFromEdit()"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                <i class="fas fa-save mr-2"></i>Save This Template
            </button>
            <button type="button" @click="submitEditForm()"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition cursor-pointer"
                x-text="isCopyingJob ? 'Create Copy' : 'Save Changes'">
            </button>
            <button type="button"
                @click="showEditModal = false; editingJobId = null; editJobData = null; isCopyingJob = false"
                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
        </div>
    </div>
</div>