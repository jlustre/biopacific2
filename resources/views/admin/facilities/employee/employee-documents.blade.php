@php
    $uploads = \App\Models\Upload::with(['user','uploadType'])
        ->where('employee_num', $employee->employee_num)
        ->orderByDesc('uploaded_at')
        ->get();
    $uploadTypes = \App\Models\UploadType::all();
    $existingUploads = $uploads->map(function ($upload) {
        return [
            'id' => $upload->id,
            'upload_type_id' => (string) $upload->upload_type_id,
            'original_filename' => $upload->original_filename,
            'expires_at' => $upload->expires_at,
        ];
    })->values();
@endphp
 <div x-show="tab === 'documents'" x-cloak data-employee-tab-panel="documents">
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
            <div id="employee-upload-section" class="p-6 mb-6 bg-white rounded shadow" tabindex="-1">
                <!-- Inline Edit/Upload Form -->
                <form id="employee-upload-form" method="POST" :action="formAction" enctype="multipart/form-data" @submit.prevent="submitForm($event)" @reset.prevent="resetForm()">
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
                            <input type="file" name="document" x-ref="documentInput" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-700 rounded border-1 focus:border-teal-800" :required="!editMode">
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
                                    <input type="date" name="expires_at" class="px-2 py-1 border-teal-700 rounded border-1 focus:border-teal-800 form-input w-full" x-model="form.expires_at" :min="form.effective_start_date || null" :required="form.upload_type_id && uploadTypesById[form.upload_type_id] && uploadTypesById[form.upload_type_id].requires_expiry">
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
            @include('admin.facilities.partials.upload-table', [
                'tableScope' => 'employee',
                'employee' => $employee,
                'isSelfService' => $isSelfService ?? false,
            ])
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
                existingUploads: @json($existingUploads),
                form: {
                    id: null,
                    upload_type_id: '',
                    effective_start_date: '',
                    expires_at: '',
                    comments: '',
                },
                duplicateWarnings() {
                    const warnings = [];
                    const selectedTypeId = String(this.form.upload_type_id || '');
                    if (!selectedTypeId) {
                        return warnings;
                    }

                    const selectedFile = this.$refs.documentInput && this.$refs.documentInput.files.length
                        ? this.$refs.documentInput.files[0].name
                        : '';
                    const selectedExpiry = this.form.expires_at || '';

                    this.existingUploads.forEach((upload) => {
                        if (upload.id === this.form.id || upload.upload_type_id !== selectedTypeId) {
                            return;
                        }

                        if (selectedFile && upload.original_filename === selectedFile) {
                            warnings.push(`A ${this.uploadTypeName(selectedTypeId)} document named "${selectedFile}" already exists.`);
                        }

                        if (selectedExpiry && upload.expires_at === selectedExpiry) {
                            warnings.push(`A ${this.uploadTypeName(selectedTypeId)} document already uses expiration date ${selectedExpiry}.`);
                        }
                    });

                    return [...new Set(warnings)];
                },
                uploadTypeName(typeId) {
                    return this.uploadTypesById[typeId]?.name || 'document';
                },
                submitForm(event) {
                    const warnings = this.duplicateWarnings();
                    if (warnings.length > 0) {
                        const confirmed = window.confirm(`${warnings.join('\n')}\n\nDo you still want to continue?`);
                        if (!confirmed) {
                            return;
                        }
                    }

                    event.target.submit();
                },
                get formAction() {
                    if (this.editMode && this.form.id) {
                        return `/admin/employees/{{ $employee->id }}/documents/${this.form.id}`;
                    }
                    return @json($employeeFormRoutes['documents_upload'] ?? ((isset($employee) && $employee->id) ? route('admin.employees.documents.upload', $employee->id) : '#'));
                },
                startEdit(upload) {
                    this.editMode = true;
                    this.form.id = upload.id;
                    this.form.upload_type_id = upload.upload_type_id;
                    this.form.effective_start_date = upload.effective_start_date || '';
                    this.form.expires_at = upload.expires_at || '';
                    this.form.comments = upload.comments || '';
                    this.$nextTick(() => {
                        const uploadSection = document.getElementById('employee-upload-section');
                        if (uploadSection) {
                            uploadSection.setAttribute('tabindex', '-1');
                            uploadSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            uploadSection.focus({ preventScroll: true });
                        }
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
                    if (this.$refs.documentInput) {
                        this.$refs.documentInput.value = '';
                    }
                },
            }
        }
    </script>
</div>