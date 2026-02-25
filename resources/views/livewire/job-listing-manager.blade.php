<div class="space-y-6"
    x-data="{ viewModalJobId: null, actionModalJobId: null, actionType: null, actionModalTitle: '', actionMessage: '', showActionModal: false, showSaveTemplateModal: false, templateName: '', selectedTemplateId: '', templates: [], editingTemplateId: null, editingTemplateName: null, editingJobId: null, showEditModal: false, editJobData: null, isCopyingJob: false, templateContentsOverride: null, templatePositionTitleOverride: null }"
    x-init="templates = window.templatesData; selectedTemplateId = {{ json_encode(old('selectedTemplateId', '')) }}; templateName = {{ json_encode(old('templateName', '')) }}">
    <script>
        window.templatesData = @json($templates ?? []);
    </script>
    <style>
        .job-description-content h1,
        .job-description-content h2,
        .job-description-content h3,
        .job-description-content h4,
        .job-description-content h5,
        .job-description-content h6 {
            font-weight: bold;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .job-description-content h1 {
            font-size: 1.875rem;
        }

        .job-description-content h2 {
            font-size: 1.5rem;
        }

        .job-description-content h3 {
            font-size: 1.25rem;
        }

        .job-description-content p {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .job-description-content ul,
        .job-description-content ol {
            margin-left: 2rem;
            margin-bottom: 0.5rem;
        }

        .job-description-content li {
            margin-bottom: 0.25rem;
        }

        .job-description-content strong {
            font-weight: bold;
        }

        .job-description-content em {
            font-style: italic;
        }

        .job-description-content u {
            text-decoration: underline;
        }

        .job-description-content a {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
    {{-- Success Message --}}
    @if($successMessage)
    <div class="p-4 bg-green-100 text-green-800 rounded-lg flex justify-between items-center">
        <span><i class="fas fa-check-circle mr-2"></i>{{ $successMessage }}</span>
        <button type="button" wire:click="closeSucess" class="text-green-800 hover:text-green-900 cursor-pointer">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Error Message --}}
    @if($errorMessage)
    <div class="p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
        <span><i class="fas fa-exclamation-circle mr-2"></i>{{ $errorMessage }}</span>
        <button type="button" wire:click="$set('errorMessage', '')"
            class="text-red-800 hover:text-red-900 cursor-pointer">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="p-4 bg-red-100 text-red-800 rounded-lg border-2 border-red-500 mb-6">
        <p class="font-bold text-lg mb-3"><i class="fas fa-exclamation-triangle mr-2"></i>Please fix these errors:</p>
        <ul class="list-disc list-inside space-y-2">
            @foreach($errors->all() as $error)
            <li class="font-semibold">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Add New Job Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Add New Job Listing</h2>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Load Template</label>
            <div class="flex gap-2">
                <select x-model="selectedTemplateId"
                    class="flex-1 border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                    @change="document.getElementById('selected-template-id-hidden').value = selectedTemplateId">
                    <option value="">-- Select Template --</option>
                    @foreach($templates as $template)
                    <option value="{{ $template['id'] }}" @selected(old('selectedTemplateId')==$template['id'])>{{
                        $template['name'] }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="selected-template-id-hidden" name="selectedTemplateId"
                    value="{{ old('selectedTemplateId', '') }}">
                <button type="button" x-bind:disabled="!selectedTemplateId" @click="loadTemplate()"
                    style="background-color: #2563eb;"
                    class="text-white font-bold px-6 py-3 rounded-lg transition whitespace-nowrap cursor-pointer hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-60">
                    <i class="fas fa-download mr-1"></i> Load
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

            <div>
                <label class="block font-semibold mb-2">Job Title *</label>
                <select wire:model="title"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    <option value="">Select a position...</option>
                    @foreach($positions as $id => $positionTitle)
                    <option value="{{ $positionTitle }}" @selected(old('title')==$positionTitle)>{{ $positionTitle }}
                    </option>
                    @endforeach
                </select>
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-2">Employment Type *</label>
                <select wire:model="employment_type"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 @error('employment_type') border-red-500 @enderror">
                    <option value="">Select...</option>
                    <option value="Full-time" @selected(old('employment_type')=='Full-time' )>Full-time</option>
                    <option value="Part-time" @selected(old('employment_type')=='Part-time' )>Part-time</option>
                    <option value="Contract" @selected(old('employment_type')=='Contract' )>Contract</option>
                    <option value="Temporary" @selected(old('employment_type')=='Temporary' )>Temporary</option>
                </select>
                @error('employment_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-2">Department</label>
                <select wire:model="department" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                    <option value="">Select...</option>
                    @foreach($departments as $id => $dept)
                    <option value="{{ $dept }}" @selected(old('department')==$dept)>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">Reporting To *</label>
                <select wire:model="reporting_to"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 @error('reporting_to') border-red-500 @enderror">
                    <option value="">Select a supervisor...</option>
                    @foreach($supervisors as $id => $supervisorTitle)
                    <option value="{{ $supervisorTitle }}" @selected(old('reporting_to')==$supervisorTitle)>{{
                        $supervisorTitle }}</option>
                    @endforeach
                </select>
                @error('reporting_to') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-2">Posted Date *</label>
                <input type="date" wire:model="posted_at" value="{{ old('posted_at') }}"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 @error('posted_at') border-red-500 @enderror" />
                @error('posted_at') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold mb-2">Status</label>
                <select wire:model="status" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                    <option value="open" @selected(old('status')=='open' )>Open</option>
                    <option value="closed" @selected(old('status')=='closed' )>Closed</option>
                    <option value="filled" @selected(old('status')=='filled' )>Filled</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Description *</label>
            <textarea id="description-editor" wire:model="description"
                placeholder="Job responsibilities, requirements, etc." rows="6"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <script>
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('description-editor', {
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                        { name: 'paragraph', items: ['BulletedList', 'NumberedList', '-', 'Outdent', 'Indent'] },
                        { name: 'links', items: ['Link'] },
                        { name: 'styles', items: ['Format'] }
                    ],
                    height: 250,
                    removePlugins: 'elementspath'
                }, function(editor) {
                    // Set old description if there was a validation error
                    const oldDescription = {!! json_encode(old('description') ?? '') !!};
                    if (oldDescription) {
                        editor.setData(oldDescription);
                    }
                    
                    // Update Livewire model when CKEditor changes
                    editor.on('change', function() {
                        @this.set('description', editor.getData());
                    });
                });
            }

            // Define loadTemplate function for Alpine.js
            window.loadTemplate = function() {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                
                const data = mainDiv._x_dataStack[0];
                if (!data.selectedTemplateId || !data.templates || data.templates.length === 0) {
                    return;
                }
                
                const selectedTemplate = data.templates.find(t => t.id == data.selectedTemplateId);
                if (!selectedTemplate) {
                    alert('Template not found');
                    return;
                }
                
                if (CKEDITOR.instances['description-editor']) {
                    CKEDITOR.instances['description-editor'].setData(selectedTemplate.contents);
                    data.selectedTemplateId = '';
                }
            };

            // Submit form for creating job listing
            window.submitJobForm = function() {
                try {
                    const form = document.getElementById('add-job-form');
                    if (!form) {
                        console.error('Form not found');
                        return;
                    }
                    
                    const titleSelect = document.querySelector('select[x-model="title"]');
                    const employmentTypeSelect = document.querySelector('select[x-model="employment_type"]');
                    const departmentSelect = document.querySelector('select[x-model="department"]');
                    const reportingToSelect = document.querySelector('select[x-model="reporting_to"]');
                    const postedAtInput = document.querySelector('input[x-model="posted_at"]');
                    const statusSelect = document.querySelector('select[x-model="status"]');
                    
                    if (titleSelect) form.querySelector('input[name="title"]').value = titleSelect.value;
                    if (employmentTypeSelect) form.querySelector('input[name="employment_type"]').value = employmentTypeSelect.value;
                    if (departmentSelect) form.querySelector('input[name="department"]').value = departmentSelect.value;
                    if (reportingToSelect) form.querySelector('input[name="reporting_to"]').value = reportingToSelect.value;
                    if (postedAtInput) form.querySelector('input[name="posted_at"]').value = postedAtInput.value;
                    if (statusSelect) form.querySelector('input[name="status"]').value = statusSelect.value;
                    
                    // Get CKEditor content
                    if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['description-editor']) {
                        document.getElementById('hidden-description').value = CKEDITOR.instances['description-editor'].getData();
                    }
                    
                    // Get active checkbox value
                    const activeCheckbox = document.querySelector('input[type="checkbox"][x-model="active"]');
                    form.querySelector('input[name="active"]').value = activeCheckbox ? (activeCheckbox.checked ? '1' : '0') : '0';
                    
                    console.log('Form data ready, submitting...');
                    form.submit();
                } catch (error) {
                    console.error('Error in submitJobForm:', error);
                }
            };

            // Save or update template
            window.saveTemplate = function() {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                if (data.templateName.trim() === '') {
                    alert('Please enter a template name');
                    return;
                }
                if (data.templateContentsOverride !== null && data.templateContentsOverride !== undefined) {
                    document.getElementById('template-contents').value = data.templateContentsOverride;
                } else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['description-editor']) {
                    document.getElementById('template-contents').value = CKEDITOR.instances['description-editor'].getData();
                }

                const positionSelect = document.getElementById('template-position-select');
                if (!data.templatePositionTitleOverride) {
                    const titleSelect = document.querySelector('select[wire\\:model="title"]');
                    const fallbackTitle = titleSelect ? titleSelect.value : '';
                    data.templatePositionTitleOverride = fallbackTitle;
                    if (positionSelect) {
                        positionSelect.value = fallbackTitle;
                    }
                }
                
                // Determine if we're creating a new template or updating an existing one
                if (data.editingTemplateId) {
                    // Update existing template
                    document.getElementById('save-template-form').action = '/admin/facility/{{ $facility->id }}/job-openings/template/update';
                    document.getElementById('template-id').value = data.editingTemplateId;
                } else {
                    // Create new template
                    document.getElementById('save-template-form').action = '/admin/facility/{{ $facility->id }}/job-openings/template/save';
                }
                
                document.getElementById('save-template-form').submit();
            };

            // Save template from view modal
            window.saveTemplateFromJob = function(contents, defaultName) {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];

                data.templateContentsOverride = contents || '';
                data.templatePositionTitleOverride = defaultName || '';
                data.templateName = defaultName ? (defaultName + ' Template') : '';
                data.editingTemplateId = null;
                data.editingTemplateName = null;
                data.viewModalJobId = null;
                data.showSaveTemplateModal = true;
            };

            // Save template from edit modal
            window.saveTemplateFromEdit = function() {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];

                const editForm = document.getElementById('edit-job-form');
                if (!editForm) return;

                const titleValue = editForm.querySelector('[x-model="title"]')?.value || '';
                let contents = '';
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['edit-description-editor']) {
                    contents = CKEDITOR.instances['edit-description-editor'].getData();
                }

                data.templateContentsOverride = contents;
                data.templatePositionTitleOverride = titleValue;
                data.templateName = titleValue ? (titleValue + ' Template') : '';
                data.editingTemplateId = null;
                data.editingTemplateName = null;
                data.showEditModal = false;
                data.showSaveTemplateModal = true;
            };

            // Use template from saved templates
            window.useTemplate = function(templateId) {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                const template = data.templates.find(t => t.id == templateId);
                if (template && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['description-editor']) {
                    CKEDITOR.instances['description-editor'].setData(template.contents);
                }
            };

            // Delete template
            window.deleteTemplate = function(templateId) {
                if(!confirm('Delete this template?')) return;
                
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/facility/{{ $facility->id }}/job-openings/template/delete';
                form.innerHTML = '@csrf<input type="hidden" name="template_id" value="' + templateId + '">';
                document.body.appendChild(form);
                form.submit();
            };

            // Update template
            window.updateTemplate = function(templateId) {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                const template = data.templates.find(t => t.id == templateId);
                if (!template) return;
                
                // Update the modal for save template
                data.templateName = template.name;
                data.showSaveTemplateModal = true;
                data.editingTemplateId = templateId; // Add flag for updating existing template
                data.editingTemplateName = template.name;
            };

            // Edit job
            window.editJob = function(jobId) {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                // Fetch job data
                fetch('/admin/facility/{{ $facility->id }}/job-openings/' + jobId + '/data')
                    .then(response => response.json())
                    .then(job => {
                        data.editJobData = job;
                        data.editingJobId = jobId;
                        data.isCopyingJob = false;
                        data.showEditModal = true;
                        data.viewModalJobId = null; // Close view modal if open
                        
                        // Populate form fields after modal renders
                        setTimeout(() => {
                            const editForm = document.getElementById('edit-job-form');
                            if (editForm) {
                                editForm.querySelector('[x-model="title"]').value = job.title || '';
                                editForm.querySelector('[x-model="employment_type"]').value = job.employment_type || '';
                                editForm.querySelector('[x-model="department"]').value = job.department || '';
                                editForm.querySelector('[x-model="reporting_to"]').value = job.reporting_to || '';
                                editForm.querySelector('[x-model="posted_at"]').value = job.posted_at || '';
                                editForm.querySelector('[x-model="status"]').value = job.status || 'open';
                                editForm.querySelector('input[type="checkbox"][x-model="active"]').checked = job.active == 1;
                                
                                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['edit-description-editor']) {
                                    CKEDITOR.instances['edit-description-editor'].setData(job.description || '');
                                }
                            }
                        }, 100);
                    })
                    .catch(error => {
                        console.error('Error loading job:', error);
                        alert('Error loading job details');
                    });
            };

            // Copy job
            window.copyJob = function(jobId) {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                // Fetch job data
                fetch('/admin/facility/{{ $facility->id }}/job-openings/' + jobId + '/data')
                    .then(response => response.json())
                    .then(job => {
                        data.editJobData = job;
                        data.editingJobId = null; // Important: null means it's a copy, not an edit
                        data.isCopyingJob = true;
                        data.showEditModal = true;
                        data.viewModalJobId = null; // Close view modal if open
                        
                        // Populate form fields after modal renders
                        setTimeout(() => {
                            const editForm = document.getElementById('edit-job-form');
                            if (editForm) {
                                editForm.querySelector('[x-model="title"]').value = job.title || '';
                                editForm.querySelector('[x-model="employment_type"]').value = job.employment_type || '';
                                editForm.querySelector('[x-model="department"]').value = job.department || '';
                                editForm.querySelector('[x-model="reporting_to"]').value = job.reporting_to || '';
                                editForm.querySelector('[x-model="posted_at"]').value = new Date().toISOString().split('T')[0]; // Reset date to today
                                editForm.querySelector('[x-model="status"]').value = 'open'; // Reset status to open
                                editForm.querySelector('input[type="checkbox"][x-model="active"]').checked = true; // Default to active
                                
                                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['edit-description-editor']) {
                                    CKEDITOR.instances['edit-description-editor'].setData(job.description || '');
                                }
                            }
                        }, 100);
                    })
                    .catch(error => {
                        console.error('Error loading job:', error);
                        alert('Error loading job details');
                    });
            };

            // Submit edit form
            window.submitEditForm = function() {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                const form = document.getElementById('edit-job-form');
                if (!form) {
                    console.error('Edit form not found');
                    return;
                }
                
                // Set form action based on edit or copy
                if (data.editingJobId) {
                    // Updating existing job
                    form.action = '/admin/facility/{{ $facility->id }}/job-openings/' + data.editingJobId + '/update';
                    form.method = 'POST';
                } else {
                    // Creating new job from copy
                    form.action = '/admin/facility/{{ $facility->id }}/job-openings';
                    form.method = 'POST';
                }
                
                // Get form values
                const titleSelect = form.querySelector('select[x-model="title"]');
                const employmentTypeSelect = form.querySelector('select[x-model="employment_type"]');
                const departmentSelect = form.querySelector('select[x-model="department"]');
                const reportingToSelect = form.querySelector('select[x-model="reporting_to"]');
                const postedAtInput = form.querySelector('input[x-model="posted_at"]');
                const statusSelect = form.querySelector('select[x-model="status"]');
                const activeCheckbox = form.querySelector('input[type="checkbox"][x-model="active"]');
                
                if (titleSelect) form.querySelector('input[name="title"]').value = titleSelect.value;
                if (employmentTypeSelect) form.querySelector('input[name="employment_type"]').value = employmentTypeSelect.value;
                if (departmentSelect) form.querySelector('input[name="department"]').value = departmentSelect.value;
                if (reportingToSelect) form.querySelector('input[name="reporting_to"]').value = reportingToSelect.value;
                if (postedAtInput) form.querySelector('input[name="posted_at"]').value = postedAtInput.value;
                if (statusSelect) form.querySelector('input[name="status"]').value = statusSelect.value;
                
                // Get CKEditor content
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['edit-description-editor']) {
                    form.querySelector('input[name="description"]').value = CKEDITOR.instances['edit-description-editor'].getData();
                }
                
                // Get active checkbox value
                form.querySelector('input[name="active"]').value = activeCheckbox ? (activeCheckbox.checked ? '1' : '0') : '0';
                
                form.submit();
            };

            // Confirm action from modal
            window.confirmAction = function() {
                const mainDiv = document.querySelector('[x-data*="viewModalJobId"]');
                if (!mainDiv || !mainDiv._x_dataStack) return;
                const data = mainDiv._x_dataStack[0];
                
                let form = document.getElementById('action-form');
                form.action = data.actionType === 'toggle' ? '/admin/facility/{{ $facility->id }}/job-openings/toggle' : 
                             data.actionType === 'status' ? '/admin/facility/{{ $facility->id }}/job-openings/status' :
                             '/admin/facility/{{ $facility->id }}/job-openings/delete';
                form.submit();
                data.showActionModal = false;
            };
        </script>
        <div class="flex items-center gap-4 mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="active" class="w-4 h-4 rounded">
                <span>Active (visible to applicants)</span>
            </label>
        </div>

        <form id="add-job-form" method="POST" action="/admin/facility/{{ $facility->id }}/job-openings">
            @csrf
            <input type="hidden" name="title" x-bind:value="@this.get('title')">
            <input type="hidden" name="employment_type" x-bind:value="@this.get('employment_type')">
            <input type="hidden" name="department" x-bind:value="@this.get('department')">
            <input type="hidden" name="reporting_to" x-bind:value="@this.get('reporting_to')">
            <input type="hidden" name="posted_at" x-bind:value="@this.get('posted_at')">
            <input type="hidden" name="status" x-bind:value="@this.get('status')">
            <input type="hidden" name="description" id="hidden-description" x-bind:value="@this.get('description')">
            <input type="hidden" name="active" x-bind:value="@this.get('active') ? '1' : '0'">
        </form>

        <div class="flex gap-3 mb-6">
            <button type="button" @click="submitJobForm()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition cursor-pointer">
                <i class="fas fa-plus mr-2"></i>Create Job Listing
            </button>
            <button type="button"
                @click="showSaveTemplateModal = true; templateContentsOverride = null; templatePositionTitleOverride = null; editingTemplateId = null; editingTemplateName = null"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition cursor-pointer">
                <i class="fas fa-save mr-2"></i>Save as Template
            </button>
        </div>

        {{-- Save Template Modal --}}
        <div x-show="showSaveTemplateModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[70]" style="display: none;">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h3 class="text-xl font-bold mb-4"
                    x-text="editingTemplateId ? 'Update Template' : 'Save Description as Template'"></h3>

                <p x-show="editingTemplateId" class="text-sm text-gray-600 mb-4">
                    You are updating a template you created.
                </p>

                <form id="save-template-form" @submit.prevent="submitTemplateForm()" x-ref="templateForm">
                    <input type="hidden" name="_token" :value="'{{ csrf_token() }}'">
                    <div class="mb-4">
                        <label class="block font-semibold mb-2">Template Name *</label>
                        <input type="text" x-model="templateName" name="template_name"
                            placeholder="e.g., Standard Nurse Job"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required />
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-2">Assign to Position *</label>
                        <select id="template-position-select" name="template_position_title"
                            x-model="templatePositionTitleOverride"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select a position...</option>
                            @foreach($positions as $id => $positionTitle)
                            <option value="{{ $positionTitle }}">{{ $positionTitle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="template_contents" id="template-contents">
                    <input type="hidden" name="template_id" id="template-id" x-bind:value="editingTemplateId">
                </form>

                <div class="flex gap-3">
                    <button type="submit" form="save-template-form"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg transition cursor-pointer"
                        x-text="editingTemplateId ? 'Update Template' : 'Save Template'">
                    </button>
                    </script>
                    <script>
                        window.submitTemplateForm = function() {
                        const form = document.getElementById('save-template-form');
                        const formData = new FormData(form);
                        let url = form.querySelector('#template-id').value
                            ? `/admin/facility/{{ $facility->id }}/job-openings/template/update`
                            : `/admin/facility/{{ $facility->id }}/job-openings/template/save`;
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) throw response;
                            return response.json ? response.json() : response.text();
                        })
                        .then(data => {
                            // handle success (close modal, show message, etc.)
                            window.location.reload();
                        })
                        .catch(async error => {
                            let msg = 'Error updating template.';
                            if (error.json) {
                                const errData = await error.json();
                                msg = errData.message || msg;
                            }
                            alert(msg);
                        });
                    }
                    </script>
                    <button type="button"
                        @click="showSaveTemplateModal = false; templateName = ''; editingTemplateId = null; editingTemplateName = null; templateContentsOverride = null; templatePositionTitleOverride = null"
                        class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        {{-- Saved Templates Section --}}
        @if(count($templates) > 0)
        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="font-bold text-blue-900 mb-3">Saved Templates ({{ count($templates) }})</h3>
            <div class="space-y-2">
                @foreach($templates as $template)
                <div class="flex items-center justify-between bg-white p-3 rounded border">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">{{ $template['name'] }}</p>
                        <p class="text-xs text-gray-500 mb-1">Created by: {{ $template['creator_name'] }}</p>
                        <p class="text-sm text-gray-600">{{ substr(strip_tags($template['contents']), 0, 100) }}...</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="useTemplate({{ $template['id'] }})"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition cursor-pointer"
                            title="Load this template into the description field">
                            <i class="fas fa-copy mr-1"></i>Use
                        </button>
                        @if(auth()->id() == $template['created_by'])
                        <button type="button" @click="updateTemplate({{ $template['id'] }})"
                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-sm transition cursor-pointer"
                            title="Update this template (you are the creator)">
                            <i class="fas fa-edit mr-1"></i>Update
                        </button>
                        <button type="button" @click="deleteTemplate({{ $template['id'] }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition cursor-pointer"
                            title="Delete this template (you are the creator)">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Job Listings Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold">Job Listings ({{ count($jobs) }})</h2>
        </div>

        @if($jobs->isEmpty())
        <div class="p-6 text-center text-gray-500">
            No job listings yet. Create one above!
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Title</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Department</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Active</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($jobs as $job)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold">{{ $job->title }}</td>
                        <td class="px-6 py-4">{{ $job->department ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $job->employment_type }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $job->status === 'open' ? 'bg-green-100 text-green-800' : 
                                   ($job->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $job->active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $job->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <button type="button" @click="viewModalJobId = {{ $job->id }}" title="View job details"
                                class="text-blue-600 hover:text-blue-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" @click="editJob({{ $job->id }})" title="Edit this job listing"
                                class="text-green-600 hover:text-green-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" @click="copyJob({{ $job->id }})"
                                title="Copy and create new job listing"
                                class="text-indigo-600 hover:text-indigo-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <button type="button"
                                @click="actionType = 'toggle'; actionModalJobId = {{ $job->id }}; actionModalTitle = '{{ $job->active ? 'Deactivate' : 'Activate' }} Job Posting'; actionMessage = 'Are you sure you want to {{ $job->active ? 'deactivate' : 'activate' }} this job posting?'; showActionModal = true"
                                title="{{ $job->active ? 'Deactivate job posting' : 'Activate job posting' }}"
                                class="text-orange-600 hover:text-orange-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-{{ $job->active ? 'ban' : 'check' }}"></i>
                            </button>
                            <button type="button"
                                @click="actionType = 'status'; actionModalJobId = {{ $job->id }}; actionModalTitle = 'Toggle Job Status'; actionMessage = 'Change status from {{ ucfirst($job->status) }} to {{ $job->status === 'open' ? 'Closed' : 'Open' }}?'; showActionModal = true"
                                title="Toggle status ({{ $job->status === 'open' ? 'Open' : 'Closed' }} to {{ $job->status === 'open' ? 'Closed' : 'Open' }})"
                                class="text-purple-600 hover:text-purple-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                            <button type="button"
                                @click="actionType = 'delete'; actionModalJobId = {{ $job->id }}; actionModalTitle = 'Delete Job'; actionMessage = 'Are you sure you want to permanently delete this job posting?'; showActionModal = true"
                                title="Delete this job listing permanently"
                                class="text-red-600 hover:text-red-800 font-semibold transition cursor-pointer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Edit Job Modal --}}
    <div x-show="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-blue-600 text-white p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold" x-text="isCopyingJob ? 'Copy Job Listing' : 'Edit Job Listing'"></h2>
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
                            <select x-model="title"
                                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a position...</option>
                                @foreach($positions as $id => $positionTitle)
                                <option value="{{ $positionTitle }}">{{ $positionTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold mb-2">Employment Type *</label>
                            <select x-model="employment_type"
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
                            <select x-model="department"
                                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                                <option value="">Select...</option>
                                @foreach($departments as $id => $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold mb-2">Reporting To *</label>
                            <select x-model="reporting_to"
                                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a supervisor...</option>
                                @foreach($supervisors as $id => $supervisorTitle)
                                <option value="{{ $supervisorTitle }}">{{ $supervisorTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold mb-2">Posted Date *</label>
                            <input type="date" x-model="posted_at"
                                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block font-semibold mb-2">Status</label>
                            <select x-model="status"
                                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="filled">Filled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Description *</label>
                        <textarea id="edit-description-editor" x-model="description"
                            placeholder="Job responsibilities, requirements, etc." rows="8"
                            class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="active" class="w-4 h-4 rounded">
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
                    <i class="fas fa-save mr-2"></i>Save as Template
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

    <script>
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('edit-description-editor', {
                toolbar: [
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                    { name: 'paragraph', items: ['BulletedList', 'NumberedList', '-', 'Outdent', 'Indent'] },
                    { name: 'links', items: ['Link'] },
                    { name: 'styles', items: ['Format'] }
                ],
                height: 250,
                removePlugins: 'elementspath'
            });
        }
    </script>

    {{-- View Job Details Modal --}}
    @forelse($jobs as $job)
    <div x-show="viewModalJobId === {{ $job->id }}"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-blue-600 text-white p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold">{{ $job->title }}</h2>
                <button type="button" @click="viewModalJobId = null"
                    class="text-white hover:text-gray-200 text-2xl cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">EMPLOYMENT TYPE</p>
                        <p class="text-lg text-gray-800">{{ $job->employment_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">DEPARTMENT</p>
                        <p class="text-lg text-gray-800">{{ $job->department ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">REPORTING TO</p>
                        <p class="text-lg text-gray-800">{{ $job->reporting_to }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">POSTED DATE</p>
                        <p class="text-lg text-gray-800">{{ $job->posted_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500 font-semibold mb-2">STATUS</p>
                    <div class="flex gap-2">
                        <span
                            class="px-3 py-1 rounded-full text-sm font-semibold 
                            {{ $job->status === 'open' ? 'bg-green-100 text-green-800' : 
                               ($job->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($job->status) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            {{ $job->active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $job->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500 font-semibold mb-2">JOB DESCRIPTION</p>
                    <div class="bg-gray-50 p-4 rounded border text-gray-700 job-description-content">
                        {!! $job->description !!}
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 p-6 sticky bottom-0 flex gap-3">
                <form id="action-form-toggle" method="POST"
                    action="/admin/facility/{{ $facility->id }}/job-openings/toggle" style="display: none;">
                    @csrf
                    <input type="hidden" name="job_id" id="form-job-id">
                </form>
                <form id="action-form-status" method="POST"
                    action="/admin/facility/{{ $facility->id }}/job-openings/status" style="display: none;">
                    @csrf
                    <input type="hidden" name="job_id" id="form-job-id-status">
                </form>
                <form id="action-form-delete" method="POST"
                    action="/admin/facility/{{ $facility->id }}/job-openings/delete" style="display: none;">
                    @csrf
                    <input type="hidden" name="job_id" id="form-job-id-delete">
                </form>
                <button type="button" @click="saveTemplateFromJob(@json($job->description), @json($job->title))"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    <i class="fas fa-save mr-2"></i>Save as Template
                </button>
                <button type="button"
                    @click="let jobId = viewModalJobId; actionType = 'toggle'; actionModalJobId = jobId; showActionModal = true; actionModalTitle = 'Toggle Active'; actionMessage = 'Are you sure?'"
                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    <i class="fas fa-toggle-on mr-2"></i>Toggle Active
                </button>
                <button type="button"
                    @click="let jobId = viewModalJobId; actionType = 'status'; actionModalJobId = jobId; showActionModal = true; actionModalTitle = 'Change Status'; actionMessage = 'Toggle the job status?'"
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    <i class="fas fa-exchange-alt mr-2"></i>Change Status
                </button>
                <button type="button"
                    @click="let jobId = viewModalJobId; actionType = 'delete'; actionModalJobId = jobId; showActionModal = true; actionModalTitle = 'Delete Job'; actionMessage = 'This action cannot be undone. Are you sure?'"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
                <button type="button" @click="viewModalJobId = null"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
    @empty
    @endforelse

    {{-- Action Confirmation Modal --}}
    <div x-show="showActionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4" x-text="actionModalTitle"></h3>
            <p class="text-gray-600 mb-6" x-text="actionMessage"></p>

            <div class="flex gap-3">
                <form id="action-form" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="job_id" x-bind:value="actionModalJobId">
                </form>

                <button type="button" @click="confirmAction()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    Confirm
                </button>
                <button type="button" @click="showActionModal = false"
                    class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>