@php
    $uploads = \App\Models\Upload::with(['user','uploadType'])
        ->where('employee_num', $employee->employee_num)
        ->current()
        ->orderByDesc('uploaded_at')
        ->get();
    $uploadTypes = isset($uploadTypes) && $uploadTypes
        ? $uploadTypes
        : \App\Models\UploadType::catalogForEmployee($employee);
    $existingUploads = $uploads->map(function ($upload) {
        return [
            'id' => $upload->id,
            'upload_type_id' => (string) $upload->upload_type_id,
            'original_filename' => $upload->original_filename,
            'expires_at' => $upload->expires_at,
        ];
    })->values();
    $requiredDocumentChecklist = $requiredDocumentChecklist ?? [
        'position_id' => null,
        'position_title' => null,
        'department_id' => null,
        'items' => collect(),
        'summary' => [
            'total' => 0,
            'complete' => 0,
            'expired' => 0,
            'missing' => 0,
        ],
    ];
    $autoOpenUploadModal = (bool) request()->boolean('open_upload_modal');
    $autoUploadTypeId = request('open_upload_type_id');
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
        <div x-data="employeeDocumentInlineEdit({ autoOpenModal: @json($autoOpenUploadModal), autoUploadTypeId: @json($autoUploadTypeId) })" x-init="init()" wire:ignore>
        @if(($requiredDocumentChecklist['summary']['total'] ?? 0) > 0)
            <div class="p-4 mb-6 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Required Documents</h3>
                        <p class="text-xs text-slate-600 mt-1">
                            Position: <span class="font-semibold text-slate-800">{{ $requiredDocumentChecklist['position_title'] ?? 'N/A' }}</span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2 py-1 rounded bg-slate-200 text-slate-800 font-semibold">Total: {{ $requiredDocumentChecklist['summary']['total'] ?? 0 }}</span>
                        <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-semibold">Complete: {{ $requiredDocumentChecklist['summary']['complete'] ?? 0 }}</span>
                        <span class="px-2 py-1 rounded bg-amber-100 text-amber-800 font-semibold">Expired: {{ $requiredDocumentChecklist['summary']['expired'] ?? 0 }}</span>
                        <span class="px-2 py-1 rounded bg-rose-100 text-rose-800 font-semibold">Missing: {{ $requiredDocumentChecklist['summary']['missing'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Document Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Latest document</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Latest Expiry</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($requiredDocumentChecklist['items'] as $requiredDoc)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-slate-900">{{ $requiredDoc['name'] }}</div>
                                        @if(!empty($requiredDoc['description']))
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $requiredDoc['description'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(($requiredDoc['status'] ?? '') === 'complete')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Complete</span>
                                        @elseif(($requiredDoc['status'] ?? '') === 'expired')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Expired</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800">Missing</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-slate-700">{{ $requiredDoc['latest_uploaded_at'] ?? '—' }}</td>
                                    <td class="px-3 py-2 text-slate-700">
                                        {{ $requiredDoc['latest_expires_at'] ?? '—' }}
                                        @if(isset($requiredDoc['days_to_expiry']) && $requiredDoc['days_to_expiry'] !== null)
                                            <span class="text-xs text-slate-500 ml-1">({{ $requiredDoc['days_to_expiry'] }}d)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $viewUploadId = $requiredDoc['valid_upload_id']
                                                ?? $requiredDoc['latest_upload_id']
                                                ?? null;
                                            $documentsViewTemplate = $employeeFormRoutes['documents_view']
                                                ?? route('admin.employees.documents.view', [$employee->id, '__ID__']);
                                            $requiredDocViewUrl = $viewUploadId
                                                ? str_replace('__ID__', (string) $viewUploadId, $documentsViewTemplate)
                                                : null;
                                        @endphp
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($requiredDocViewUrl)
                                                <a
                                                    href="{{ $requiredDocViewUrl }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700 hover:text-rose-900 underline"
                                                    title="View PDF"
                                                >
                                                    <i class="fas fa-file-pdf" aria-hidden="true"></i>
                                                    View PDF
                                                </a>
                                            @endif
                                            @if(($requiredDoc['status'] ?? '') !== 'complete')
                                                <button
                                                    type="button"
                                                    class="text-xs font-semibold text-teal-700 hover:text-teal-900 underline"
                                                    @click="openUploadModal('{{ $requiredDoc['upload_type_id'] }}')"
                                                >
                                                    Upload now
                                                </button>
                                            @elseif(! $requiredDocViewUrl)
                                                <span class="text-xs text-slate-400">Up to date</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(!empty($requiredDocumentChecklist['position_title']))
            <div class="p-4 mb-6 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700">
                No required document types are configured yet for position
                <span class="font-semibold text-slate-900">{{ $requiredDocumentChecklist['position_title'] }}</span>.
            </div>
        @endif
            <div class="mb-4">
                <button type="button" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700"
                    @click="openUploadModal()">
                    <i class="fas fa-upload mr-2"></i>{{ config('documents.labels.upload_modal_title') }}
                </button>
            </div>

            <template x-teleport="body">
                <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
                    <div class="absolute inset-0 bg-black/50" @click="resetForm()"></div>
                    <div id="employee-upload-section" class="relative z-10 w-full max-w-3xl p-6 bg-white rounded shadow max-h-[90vh] overflow-y-auto" tabindex="-1">
                    <!-- Inline Edit/Upload Form -->
                    <form id="employee-upload-form" method="POST" :action="formAction" enctype="multipart/form-data" @submit.prevent="submitForm($event)" @reset.prevent="resetForm()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit document' : @js(config('documents.labels.upload_modal_title'))"></h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" @click="resetForm()" aria-label="Close">&times;</button>
                    </div>
                    <input type="hidden" name="_token" :value="csrf">
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block mb-1 text-xs font-semibold">{{ config('documents.labels.type') }} <span class="text-red-600">*</span></label>
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
                            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <div class="col-span-1 md:col-span-2 flex flex-col gap-4">
                            <div class="flex-1">
                                <label class="block mb-1 text-xs font-semibold">Comments</label>
                                <textarea name="comments" rows="2" class="px-2 py-1 bg-teal-50 border-teal-700 rounded border-1 focus:border-teal-800 form-input w-full resize-y" x-model="form.comments"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700" x-text="editMode ? 'Save Changes' : 'Upload'"></button>
                                <button type="reset" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            </template>
            @include('admin.facilities.partials.upload-table', [
                'tableScope' => 'employee',
                'employee' => $employee,
                'isSelfService' => $isSelfService ?? false,
            ])
        </div>
    </div>
    @endif
    <script>
        function employeeDocumentInlineEdit(config = {}) {
            return {
                editMode: false,
                showUploadModal: false,
            autoOpenModal: !!config.autoOpenModal,
            autoUploadTypeId: config.autoUploadTypeId,
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
                init() {
                    if (!this.autoOpenModal) {
                        return;
                    }

                    this.$nextTick(() => {
                        this.openUploadModal(this.autoUploadTypeId || null);
                        this.autoOpenModal = false;
                    });
                },
                get formAction() {
                    if (this.editMode && this.form.id) {
                        const updateBase = @json($employeeFormRoutes['documents_update_base'] ?? ('/admin/employees/' . $employee->id . '/documents'));
                        return `${updateBase}/${this.form.id}`;
                    }
                    return @json($employeeFormRoutes['documents_upload'] ?? ((isset($employee) && $employee->id) ? route('admin.employees.documents.upload', $employee->id) : '#'));
                },
                openUploadModal(uploadTypeId = null) {
                    this.editMode = false;
                    this.form.id = null;
                    this.form.upload_type_id = '';
                    this.form.effective_start_date = '';
                    this.form.expires_at = '';
                    this.form.comments = '';
                    if (uploadTypeId) {
                        this.form.upload_type_id = String(uploadTypeId);
                    }
                    this.showUploadModal = true;
                    this.$nextTick(() => {
                        const select = document.querySelector('#employee-upload-form select[name="upload_type_id"]');
                        if (select) select.focus();
                    });
                },
                startEdit(upload) {
                    this.editMode = true;
                    this.showUploadModal = true;
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
                    this.showUploadModal = false;
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