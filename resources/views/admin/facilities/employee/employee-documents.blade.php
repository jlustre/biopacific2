@php
    $uploads = \App\Models\Upload::with(['user','uploadType'])
        ->where('employee_num', $employee->employee_num)
        ->orderByDesc('uploaded_at')
        ->get();
    $uploadTypes = \App\Models\UploadType::all();
@endphp
 <div x-show="tab === 'documents'">
    @if(empty($employee->employee_num))
        <div x-show="tab === 'documents'">
            <div class="p-6 mb-6 bg-white rounded shadow text-gray-600">
                <div class="mb-2 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
                    <strong>Notice:</strong> Please complete and save the Personal tab form before continuing with the checklist.
                </div>
                <em>Save the employee record before uploading documents.</em>
            </div>
        </div>
    @elseif(isset($employee->employee_num) && $employee->employee_num)
    <div x-show="tab === 'documents'">
        <h2 class="text-xl font-bold mb-4">Employee Documents</h2>
        @if(session('success'))
            <div class="p-3 mb-4 text-green-800 bg-green-100 border border-green-400 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
                <ul class="pl-5 list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div x-data="employeeDocumentInlineEdit()" wire:ignore>
            <div class="p-6 mb-6 bg-white rounded shadow">
                <!-- Inline Edit/Upload Form -->
                <form id="employee-upload-form" method="POST" :action="formAction" enctype="multipart/form-data" @reset.prevent="resetForm()">
                    <input type="hidden" name="_token" :value="csrf">
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
                            <select name="upload_type_id" class="form-select w-full px-2 py-1 bg-teal-50 border-teal-700 rounded border-1 focus:border-teal-800" required x-model="form.upload_type_id">
                                <option value="">Select Type</option>
                                <template x-for="type in uploadTypes" :key="type.id">
                                    <option :value="type.id" x-text="type.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-semibold">File <span class="text-red-600" x-show="!editMode">*</span></label>
                            <input type="file" name="document" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-700 rounded border-1 focus:border-teal-800" :required="!editMode">
                            <div class="text-xs text-gray-500 mt-1" x-show="editMode">Leave blank to keep the current file.</div>
                        </div>
                        <template x-if="form.upload_type_id && uploadTypesById[form.upload_type_id] && uploadTypesById[form.upload_type_id].requires_expiry">
                            <div class="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                                    <input type="date" name="effective_start_date" class="px-2 py-1 border-teal-700 rounded border-1 focus:border-teal-800 form-input w-full" x-model="form.effective_start_date">
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-semibold">Expires At <span class="text-red-600">*</span></label>
                                    <input type="date" name="expires_at" class="px-2 py-1 border-teal-700 rounded border-1 focus:border-teal-800 form-input w-full" x-model="form.expires_at" :required="form.upload_type_id && uploadTypesById[form.upload_type_id] && uploadTypesById[form.upload_type_id].requires_expiry">
                                </div>
                            </div>
                        </template>
                        <div class="col-span-2 flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-1">
                                <label class="block mb-1 text-xs font-semibold">Comments</label>
                                <textarea name="comments" rows="2" class="px-2 py-1 bg-teal-50 border-teal-700 rounded border-1 focus:border-teal-800 form-input w-full min-w-[220px] resize-y" x-model="form.comments"></textarea>
                            </div>
                            <div class="flex-shrink-0 flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700" x-text="editMode ? 'Save Changes' : 'Upload'"></button>
                                <button type="reset" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" x-show="editMode">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-6 bg-white rounded shadow">
                <table class="min-w-full border border-gray-200 table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2 border text-sm">Type</th>
                            <th class="px-3 py-2 border text-sm">Uploaded By</th>
                            <th class="px-3 py-2 border text-sm">Expires</th>
                            <th class="px-3 py-2 border text-sm">Uploaded</th>
                            <th class="px-3 py-2 border text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uploads as $upload)
                        <tr>
                            <td class="px-3 py-2 border text-xs">{{ $upload->uploadType->name ?? '-' }}</td>
                            <td class="px-3 py-2 border text-xs">{{ $upload->user->name ?? '-' }}</td>
                            <td class="px-3 py-2 border text-xs">{{ $upload->expires_at ?? '-' }}</td>
                            <td class="px-3 py-2 border text-xs">{{ $upload->uploaded_at ? \Carbon\Carbon::parse($upload->uploaded_at)->format('M d, Y g:i A') : '-' }}</td>
                            <td class="px-3 py-2 border text-sm flex items-center space-x-2">
                                <a href="{{ route('admin.employees.documents.download', [$employee->id, $upload->id]) }}" title="Download" class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                                </a>
                                <a href="{{ route('admin.employees.documents.view', [$employee->id, $upload->id]) }}" title="View" class="text-green-600 hover:text-green-800" target="_blank" rel="noopener noreferrer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <button type="button" @click="startEdit({
                                    id: {{ $upload->id }},
                                    upload_type_id: '{{ $upload->upload_type_id }}',
                                    effective_start_date: '{{ $upload->effective_start_date ?? '' }}',
                                    expires_at: '{{ $upload->expires_at ?? '' }}',
                                    comments: @js($upload->comments),
                                })" title="Edit" class="text-yellow-600 hover:text-yellow-800">
                                    <!-- Pencil Icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.768-6.768a2.5 2.5 0 113.536 3.536L12.5 17H9v-3.5z" /></svg>
                                </button>
                                <form action="{{ route('admin.employees.documents.delete', [$employee->id, $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete" class="text-red-600 hover:text-red-800 bg-transparent border-none p-0 m-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">No uploads found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    <script>
        function employeeDocumentInlineEdit() {
            return {
                editMode: false,
                csrf: '{{ csrf_token() }}',
                uploadTypes: @json($uploadTypes->values()),
                uploadTypesById: @json($uploadTypes->keyBy('id')),
                form: {
                    id: null,
                    upload_type_id: '',
                    effective_start_date: '',
                    expires_at: '',
                    comments: '',
                },
                get formAction() {
                    if (this.editMode && this.form.id) {
                        return `/admin/employees/{{ $employee->id }}/documents/${this.form.id}`;
                    }
                    return `{{ (isset($employee) && $employee->id) ? route('admin.employees.documents.upload', $employee->id) : '#' }}`;
                },
                startEdit(upload) {
                    this.editMode = true;
                    this.form.id = upload.id;
                    this.form.upload_type_id = upload.upload_type_id;
                    this.form.effective_start_date = upload.effective_start_date || '';
                    this.form.expires_at = upload.expires_at || '';
                    this.form.comments = upload.comments || '';
                    // Focus the upload_type_id select in the form
                    this.$nextTick(() => {
                        const select = document.querySelector('#employee-upload-form select[name="upload_type_id"]');
                        if (select) select.focus();
                    });
                },
                resetForm() {
                    this.editMode = false;
                    this.form = {
                        id: null,
                        upload_type_id: '',
                        effective_start_date: '',
                        expires_at: '',
                        comments: '',
                    };
                },
            }
        }
    </script>
</div>