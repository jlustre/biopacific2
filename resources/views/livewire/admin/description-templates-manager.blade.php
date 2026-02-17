<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-col gap-6">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif
        <div>
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <input id="template-title-filter" type="text" placeholder="Filter by title"
                    class="w-full sm:max-w-xs border border-gray-300 rounded px-3 py-2" />
                <button id="fetch-templates" type="button"
                    class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Refresh</button>
                <button id="new-template-btn" type="button" wire:click="openPositionBlock"
                    class="cursor-pointer px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 sm:ml-auto">New
                    Template</button>
            </div>
            <div class="overflow-x-auto" wire:ignore>
                <table class="min-w-full table-auto text-sm" id="templates-table">
                    <thead>
                        <tr class="text-left text-gray-600">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Title</th>
                            <div class="flex gap-2 mt-3">
                                @if($editingJobDescriptionId)
                                <button type="button" wire:click="saveJobDescription"
                                    class="px-4 py-2 text-white rounded hover:opacity-90"
                                    style="background-color: #059669;">
                                    Save
                                </button>
                                <button type="button" wire:click="cancelEditJobDescription"
                                    class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                                    Cancel
                                </button>
                                {{-- @else --}}
                                {{-- <button type="button" wire:click="startEditJobDescription"
                                    @if(!$selectedJobDescriptionId) disabled @endif
                                    class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-600 @if(!$selectedJobDescriptionId) opacity-50 cursor-not-allowed @endif">
                                    Edit2
                                </button>
                                <button type="button" wire:click="deleteJobDescription" @if(!$selectedJobDescriptionId)
                                    disabled @endif
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 @if(!$selectedJobDescriptionId) opacity-50 cursor-not-allowed @endif">
                                    Delete
                                </button> --}}
                                @endif
                            </div>
                            <div class="flex gap-2 mt-3">
                                <th class="px-2 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="px-2 py-3 text-gray-500">Loading templates...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="position-block" class="border-t pt-6 @if(!$showPositionBlock) hidden @endif">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">{{ $loadedTemplateId ? 'Edit Template' : 'New Template'
                    }}</h3>
                <button type="button" wire:click="resetTemplateForm"
                    class="cursor-pointer px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    &larr; Back to Templates
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <select wire:model="selectedPositionId" wire:change="syncDepartmentForPosition"
                        class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">Select a position...</option>
                        @foreach($positions as $position)
                        <option value="{{ $position['id'] }}">{{ $position['display_title'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Add Position</label>
                    <input id="add-position-input" type="text" wire:model.debounce.200ms="newPositionTitle"
                        placeholder="New position title" class="w-full border border-gray-300 rounded px-3 py-2">
                    @error('newPositionTitle')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                    <button id="position-button" type="button" wire:click="createPosition"
                        class="mt-3 w-full px-3 py-2 text-white rounded hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background-color:#059669; cursor: pointer;" disabled>
                        Save Position
                    </button>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select wire:model="selectedDepartmentId" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">Select a department...</option>
                        @foreach($departments as $department)
                        <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedDepartmentId')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Add Department</label>
                    <input id="add-department-input" type="text" wire:model.debounce.200ms="newDepartmentName"
                        placeholder="New department" class="w-full border border-gray-300 rounded px-3 py-2">
                    @error('newDepartmentName')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                    <button id="department-button" type="button" wire:click="createDepartment"
                        class="mt-3 w-full px-3 py-2 text-white rounded hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background-color:#0284c7; cursor: pointer;" disabled>
                        Save Department
                    </button>
                </div>
            </div>

            @if($selectedPositionId)
            <div class="border-t pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Job Description</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Job Description Title</label>
                        @if(count($jobDescriptions) > 0)
                        <select wire:model.live="selectedJobDescriptionId"
                            class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Select a job description...</option>
                            @foreach($jobDescriptions as $jobDesc)
                            <option value="{{ $jobDesc['id'] }}">{{ $jobDesc['title'] }}</option>
                            @endforeach
                        </select>
                        @else
                        <select class="w-full border border-gray-300 rounded px-3 py-2" disabled>
                            <option>No job descriptions available for this position</option>
                        </select>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <div id="job-description-editor"
                            class="border border-gray-300 rounded px-4 py-3 bg-white min-h-32 max-h-64 overflow-y-auto">
                            @if($editingJobDescriptionId)
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                                    <input type="text" wire:model="editJobDescriptionTitle"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                    @error('editJobDescriptionTitle')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                    <textarea id="job-description-hidden" wire:model="editJobDescriptionDescription"
                                        class="hidden"></textarea>
                                    <div wire:ignore>
                                        <textarea id="job-description-editor-input"
                                            class="w-full border border-gray-300 rounded px-3 py-2 h-32"></textarea>
                                    </div>
                                    @error('editJobDescriptionDescription')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            @elseif(!empty($selectedJobDescriptionDescription))
                            @php
                            $hasHtml = $selectedJobDescriptionDescription !==
                            strip_tags($selectedJobDescriptionDescription);
                            $safeHtml = strip_tags($selectedJobDescriptionDescription, '<p><strong><em><u>
                                            <ul>
                                                <ol>
                                                    <li><br><a>
                                                            <h4>
                                                                <h5>
                                                                    <h6>
                                                                        <blockquote>');
                                                                            @endphp
                                                                            @if($hasHtml)
                                                                            <div
                                                                                class="text-gray-700 whitespace-pre-wrap">
                                                                                {!! $safeHtml !!}</div>
                                                                            @elseif(strpos($selectedJobDescriptionDescription,
                                                                            '|') !== false)
                                                                            @php
                                                                            $listItems = array_map('trim', explode('|',
                                                                            $selectedJobDescriptionDescription));
                                                                            $listItems = array_filter($listItems, fn($i)
                                                                            => !empty($i));
                                                                            @endphp
                                                                            @if(!empty($listItems))
                                                                            <ul class="list-disc list-inside space-y-2">
                                                                                @foreach($listItems as $item)
                                                                                <li class="text-gray-700">{{ $item }}
                                                                                </li>
                                                                                @endforeach
                                                                            </ul>
                                                                            @else
                                                                            <p
                                                                                class="text-gray-700 whitespace-pre-wrap">
                                                                                {{ $selectedJobDescriptionDescription }}
                                                                            </p>
                                                                            @endif
                                                                            @else
                                                                            <p
                                                                                class="text-gray-700 whitespace-pre-wrap">
                                                                                {{ $selectedJobDescriptionDescription }}
                                                                            </p>
                                                                            @endif
                                                                            @else
                                                                            <p class="text-gray-400">No description</p>
                                                                            @endif
                        </div>
                        <div class="flex gap-2 mt-3">
                            @if($editingJobDescriptionId)
                            <button type="button" wire:click="saveJobDescription"
                                class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                                Save
                            </button>
                            <button type="button" wire:click="cancelEditJobDescription"
                                class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                                Cancel
                            </button>
                            @else
                            <button type="button" wire:click="startEditJobDescription" @if(!$selectedJobDescriptionId)
                                disabled @endif
                                class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-600 @if(!$selectedJobDescriptionId) opacity-50 cursor-not-allowed @endif">
                                Edit
                            </button>
                            <button type="button" wire:click="deleteJobDescription" @if(!$selectedJobDescriptionId)
                                disabled @endif
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 @if(!$selectedJobDescriptionId) opacity-50 cursor-not-allowed @endif">
                                Delete
                            </button>
                            @endif
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" wire:click="addJobDescriptionToFinal" @if(!$selectedJobDescriptionId)
                                disabled @endif
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 @if($selectedJobDescriptionId) cursor-pointer @else opacity-50 cursor-not-allowed @endif">
                                Add to Final Description
                            </button>
                            <button type="button" onclick="toggleCopyPanel()" @if(!$selectedJobDescriptionId) disabled
                                @endif
                                class="flex-1 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 @if(!$selectedJobDescriptionId) cursor-pointer @else opacity-50 cursor-not-allowed @endif">
                                Copy to Position
                            </button>
                        </div>

                        <!-- Copy Job Description Panel -->
                        <div id="copy-panel" style="display: none;"
                            class="mt-4 p-4 bg-purple-50 border border-purple-300 rounded">
                            <h4 class="font-semibold text-gray-800 mb-3">Copy Description to Another Position</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Position</label>
                                    <select wire:model="copyToPositionId"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                        <option value="">Select a position...</option>
                                        @foreach($positions as $position)
                                        <option value="{{ $position['id'] }}">{{ $position['display_title'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('copyToPositionId')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title for Copied
                                        Description</label>
                                    <input type="text" wire:model="copiedJobDescriptionTitle"
                                        placeholder="Enter a title for this description in the new position"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                    @error('copiedJobDescriptionTitle')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="copyJobDescriptionToPosition"
                                        class="cursor-pointer flex-1 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                                        Copy Description
                                    </button>
                                    <button type="button" onclick="toggleCopyPanel()"
                                        class="cursor-pointer flex-1 px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="toggleImportPanel"
                            class="cursor-pointer w-full mt-3 px-4 py-2 text-white rounded font-semibold hover:opacity-90 transition"
                            style="background-color: #059669;">
                            📥 Import Description from Another Position
                        </button>

                        <!-- Import Job Description Panel -->
                        @if($showImportPanel)
                        <div class="mt-4 p-4 bg-emerald-50 border border-emerald-300 rounded">
                            <h4 class="font-semibold text-gray-800 mb-3">Import Description from Another Position</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Position</label>
                                    <select wire:model.live="importFromPositionId"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                        <option value="">Select a position...</option>
                                        @foreach($positions as $position)
                                        @if($position['id'] != $selectedPositionId)
                                        <option value="{{ $position['id'] }}">{{ $position['display_title'] }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @error('importFromPositionId')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Title</label>
                                    <div class="flex gap-2">
                                        <select wire:model.live="importJobDescriptionId"
                                            class="flex-1 border border-gray-300 rounded px-3 py-2"
                                            @if(!$importFromPositionId) disabled @endif>
                                            <option value="">Select a description...</option>
                                            @foreach($importAvailableDescriptions as $desc)
                                            <option value="{{ $desc['id'] }}">{{ $desc['title'] }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="importAllJobDescriptions"
                                            @if(!$importFromPositionId) disabled @endif
                                            class="px-4 py-2 text-white rounded hover:opacity-90 transition whitespace-nowrap @if(!$importFromPositionId) opacity-50 cursor-not-allowed @endif"
                                            style="background-color: #0891b2;">
                                            📥 Import All Titles
                                        </button>
                                    </div>
                                    @error('importJobDescriptionId')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Position</label>
                                    <select wire:model="importTargetPositionId"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                        <option value="">Select a position...</option>
                                        @foreach($positions as $position)
                                        <option value="{{ $position['id'] }}">{{ $position['display_title'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('importTargetPositionId')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title for Target
                                        Position</label>
                                    <input type="text" wire:model="importedJobDescriptionTitle"
                                        placeholder="Auto-populates from source title, you can edit it"
                                        class="w-full border border-gray-300 rounded px-3 py-2">
                                    @error('importedJobDescriptionTitle')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="importJobDescription"
                                        class="flex-1 px-4 py-2 text-white rounded hover:opacity-90"
                                        style="background-color: #059669;">
                                        Import Description
                                    </button>
                                    <button type="button" wire:click="toggleImportPanel"
                                        class="flex-1 px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($selectedPositionId)
        <div class="border-t pt-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Final Description</h3>
            <div class="grid grid-cols-1 gap-4">
                @if(count($addedJobDescriptions) > 0)
                <div class="mb-4">
                    <div class="bg-gray-50 border border-gray-300 rounded px-4 py-3 max-h-80 overflow-y-auto">
                        @foreach($addedJobDescriptions as $index => $item)
                        <div class="mb-4 pb-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-gray-800">{{ $item['title'] }}</h4>
                                <button type="button" wire:click="removeFromFinal({{ $index }})"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    Remove
                                </button>
                            </div>
                            @if(strpos($item['description'], '|') !== false)
                            @php
                            $listItems = array_map('trim', explode('|', $item['description']));
                            $listItems = array_filter($listItems, fn($i) => !empty($i));
                            @endphp
                            @if(!empty($listItems))
                            <ul class="list-disc list-inside mt-2 text-gray-700 space-y-1">
                                @foreach($listItems as $listItem)
                                <li>{{ $listItem }}</li>
                                @endforeach
                            </ul>
                            @endif
                            @else
                            <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ $item['description'] }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-gray-400">Add job descriptions above to build your final description.</p>
                @endif

                <div class="flex justify-end items-center mt-6 mb-2">
                    <button type="button" wire:click="toggleFinalDescriptionEditor"
                        class="cursor-pointer px-4 py-2 text-sm text-white rounded transition"
                        style="background-color: #059669;" onmouseover="this.style.backgroundColor='#047857'"
                        onmouseout="this.style.backgroundColor='#059669'">
                        {{ $showFinalDescriptionEditor ? 'Hide Editor' : 'Show Editor' }}
                    </button>
                </div>

                @if($showFinalDescriptionEditor)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700">Final Description (Rich Text
                            Editor)</label>
                        <button type="button" id="html-mode-toggle" onclick="toggleMode()"
                            class="px-3 py-1 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                            &lt; &gt; HTML Mode
                        </button>
                    </div>
                    <textarea id="final-description-editor"
                        class="border border-gray-300 rounded px-3 py-2 w-full h-64"></textarea>

                    <!-- HTML Source View (Initially Hidden) -->
                    <div id="html-source-view" style="display: none;" class="mt-2">
                        <textarea id="html-textarea"
                            class="border border-gray-300 rounded px-3 py-2 w-full h-64 font-mono text-sm p-3"
                            placeholder="Raw HTML code - Edit and switch back to visual mode to apply changes"
                            style="color: #90EE90; background-color: #1e1e1e;"></textarea>
                        <p class="text-xs text-gray-500 mt-2">Tip: Edit the HTML code directly and click "Visual
                            Mode" to see the changes in the editor.</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="border-t pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $loadedTemplateId ? 'Update Template' :
                    'Save Template' }}</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                        <input type="text" wire:model="templateName" placeholder="Enter template name"
                            class="w-full border border-gray-300 rounded px-3 py-2">
                        @error('templateName')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <select wire:model="selectedPositionId" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Select a position...</option>
                            @foreach($positions as $position)
                            <option value="{{ $position['id'] }}">{{ $position['display_title'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedPositionId')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mt-4 flex gap-3 flex-wrap">
                    <button type="button" wire:click="saveTemplate"
                        class="cursor-pointer px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        {{ $loadedTemplateId ? 'Update Template' : 'Save Template' }}
                    </button>
                    @if($loadedTemplateId)
                    <button type="button" wire:click="saveTemplateAs"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Save as New Copy
                    </button>
                    @endif
                    <button type="button" wire:click="resetTemplateForm"
                        class="cursor-pointer px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Clear Form
                    </button>
                </div>
                @error('finalDescription')
                <span class="text-xs text-red-600 block mt-2">{{ $message }}</span>
                @enderror
            </div>
        </div>
        @endif
        @endif
    </div>

</div>
</div>

@push('scripts')
<script>
    function toggleCopyPanel() {
        const panel = document.getElementById('copy-panel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    function toggleImportPanel() {
        const panel = document.getElementById('import-panel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    function getCSRF() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getTitleFilter() {
        return document.getElementById('template-title-filter').value.trim();
    }

    function renderTemplatesTable(templates) {
        const tbody = document.querySelector('#templates-table tbody');
        tbody.innerHTML = '';
        if (!templates.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-2 py-3 text-gray-500">No templates found.</td></tr>';
            return;
        }
        templates.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-2 py-2">${t.name || ''}</td>
                <td class="px-2 py-2">${t.title || ''}</td>
                <td class="px-2 py-2 text-gray-500">${t.updated_at ? new Date(t.updated_at).toLocaleDateString() : ''}</td>
                <td class="px-2 py-2 space-x-2">
                    <button type="button" class="text-green-600 hover:underline" onclick='loadTemplate(${t.id})'>Load/Edit</button>
                    <button type="button" class="text-red-600 hover:underline" onclick='deleteTemplateConfirm(${t.id}, "${(t.name || '').replace(/'/g, "\\'")}")'>Delete</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function fetchTemplates() {
        const title = getTitleFilter();
        const url = title ? `/admin/job-description-templates?title=${encodeURIComponent(title)}` : '/admin/job-description-templates';
        fetch(url)
            .then(res => res.json())
            .then(data => renderTemplatesTable(data))
            .catch(() => renderTemplatesTable([]));
    }

    function editTemplate(template) {
        // console.log('Edit template', template);
        const block = document.getElementById('position-block');
        if (block) {
            block.classList.remove('hidden');
        }
    }

    function loadTemplate(templateId) {
        // Call Livewire method to load template
        @this.call('loadTemplate', templateId).then(() => {
            // Scroll to position block after loading
            const positionBlock = document.getElementById('position-block');
            if (positionBlock) {
                positionBlock.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    function deleteTemplateConfirm(templateId, templateName) {
        if (confirm(`Are you sure you want to delete template "${templateName}"? This action cannot be undone.`)) {
            @this.call('deleteTemplate', templateId);
        }
    }

    function validatePositionForm() {
        const positionInput = document.getElementById('add-position-input');
        const departmentSelect = document.querySelector('select[wire\\:model="selectedDepartmentId"]');
        const button = document.getElementById('position-button');
        
        // Clear error message when user types
        const errorSpan = positionInput.parentElement.querySelector('.text-red-600');
        if (errorSpan) {
            errorSpan.remove();
        }
        
        const isValid = positionInput && positionInput.value.trim() && departmentSelect && departmentSelect.value;
        button.disabled = !isValid;
    }

    function validateDepartmentForm() {
        const departmentInput = document.getElementById('add-department-input');
        const button = document.getElementById('department-button');
        
        // Clear error message when user types
        const errorSpan = departmentInput.parentElement.querySelector('.text-red-600');
        if (errorSpan) {
            errorSpan.remove();
        }
        
        const isValid = departmentInput && departmentInput.value.trim();
        button.disabled = !isValid;
    }

    // Set up listeners for position form validation
    const positionInput = document.getElementById('add-position-input');
    const departmentSelect = document.querySelector('select[wire\\:model="selectedDepartmentId"]');
    
    if (positionInput) {
        positionInput.addEventListener('input', validatePositionForm);
    }
    
    if (departmentSelect) {
        departmentSelect.addEventListener('change', validatePositionForm);
    }

    // Set up listeners for department form validation
    const departmentInput = document.getElementById('add-department-input');
    
    if (departmentInput) {
        departmentInput.addEventListener('input', validateDepartmentForm);
    }

    document.getElementById('fetch-templates').addEventListener('click', fetchTemplates);
    document.getElementById('template-title-filter').addEventListener('change', fetchTemplates);

    // Listen for template saved event from Livewire
    document.addEventListener('templateSaved', function() {
        fetchTemplates();
    });

    // Listen for template deleted event from Livewire
    document.addEventListener('templateDeleted', function() {
        fetchTemplates();
    });

    if (window.Livewire && window.Livewire.hook) {
        window.Livewire.hook('message.processed', () => {
            const tbody = document.querySelector('#templates-table tbody');
            if (!tbody || tbody.children.length <= 1) {
                fetchTemplates();
            }
        });
    }

    fetchTemplates();
</script>

<!-- TinyMCE Integration for Visual and Source Code Editing -->
<script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
<script>
    let editorInstance = null;
    let jobDescriptionEditorInstance = null;
    let isHtmlMode = false;

    function initEditor() {
        if (!window.tinymce) {
            console.log('Waiting for TinyMCE to load...');
            setTimeout(initEditor, 100);
            return;
        }

        const editorElement = document.querySelector('#final-description-editor');
        if (!editorElement) {
            console.log('Editor element not found');
            return;
        }

        // Check if TinyMCE is already initialized on this element
        const existingEditor = tinymce.get('final-description-editor');
        if (existingEditor) {
            editorInstance = existingEditor;
            console.log('Editor already initialized');
            return;
        }

        let updateTimeout;

        tinymce.init({
            selector: '#final-description-editor',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'lists', 'code', 'fullscreen', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | table | code | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            paste_as_text: false,
            paste_retain_style_properties: 'all',
            paste_word_valid_elements: 'b,strong,i,em,h1,h2,h3,h4,h5,h6,ul,ol,li,p,br,a[href],table,tr,td,th',
            paste_data_images: false,
            automatic_uploads: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            images_upload_handler: function (blobInfo, success, failure) {
                failure('Image upload is disabled');
            },
            init_instance_callback: function(editor) {
                editorInstance = editor;
                
                // Load initial data if exists
                if (@this.finalDescription) {
                    editor.setContent(@this.finalDescription);
                    document.getElementById('html-textarea').value = @this.finalDescription;
                }
                
                console.log('Editor initialized successfully');
            },
            setup: function(editor) {
                // Debounced update to Livewire
                const updateLivewire = function() {
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(function() {
                        const content = editor.getContent();
                        document.getElementById('html-textarea').value = content;
                        @this.set('finalDescription', content).catch(err => {
                            console.error('Livewire update error:', err);
                        });
                    }, 500);
                };

                // Update Livewire when editor content changes (debounced)
                editor.on('change', updateLivewire);
                editor.on('blur', function() {
                    clearTimeout(updateTimeout);
                    const content = editor.getContent();
                    document.getElementById('html-textarea').value = content;
                    @this.set('finalDescription', content).catch(err => {
                        console.error('Livewire update error:', err);
                    });
                });
            }
        });
    }

    function toggleMode() {
        const editorContainer = document.querySelector('.tox-tinymce');
        const htmlContainer = document.getElementById('html-source-view');
        const toggleBtn = document.getElementById('html-mode-toggle');
        
        if (!editorContainer || !htmlContainer || !toggleBtn) {
            return;
        }

        isHtmlMode = !isHtmlMode;

        if (isHtmlMode) {
            // Switch to HTML mode
            editorContainer.style.display = 'none';
            htmlContainer.style.display = 'block';
            toggleBtn.textContent = '👁️ Visual Mode';
            console.log('Switched to HTML mode');
        } else {
            // Switch to Visual mode
            editorContainer.style.display = 'block';
            htmlContainer.style.display = 'none';
            toggleBtn.textContent = '< > HTML Mode';
            
            // Sync HTML back to editor
            const htmlContent = document.getElementById('html-textarea').value;
            if (editorInstance && htmlContent) {
                editorInstance.setContent(htmlContent);
            }
            console.log('Switched to Visual mode');
        }
    }

    // Initialize when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        initEditor();
    }

    // Also initialize on Livewire updates
    document.addEventListener('livewire:load', initEditor);
    document.addEventListener('livewire:updated', function() {
        // Try to initialize if element appears (like when Show Editor is clicked)
        setTimeout(function() {
            initEditor();
            // Sync content if editor exists
            if (editorInstance && !isHtmlMode && @this.finalDescription) {
                editorInstance.setContent(@this.finalDescription);
                document.getElementById('html-textarea').value = @this.finalDescription;
            }
        }, 100);
    });

    function initJobDescriptionEditor() {
        if (jobDescriptionEditorInstance) {
            return;
        }
        if (!window.tinymce) {
            setTimeout(initJobDescriptionEditor, 100);
            return;
        }

        const editorElement = document.querySelector('#job-description-editor-input');
        const hiddenElement = document.querySelector('#job-description-hidden');

        if (!editorElement || !hiddenElement) {
            if (jobDescriptionEditorInstance) {
                tinymce.get('job-description-editor-input')?.remove();
                jobDescriptionEditorInstance = null;
            }
            return;
        }

        let updateTimeout;

        tinymce.init({
            selector: '#job-description-editor-input',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'lists', 'code', 'fullscreen', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            paste_as_text: false,
            paste_retain_style_properties: 'all',
            paste_word_valid_elements: 'b,strong,i,em,h1,h2,h3,h4,h5,h6,ul,ol,li,p,br,a[href],table,tr,td,th',
            paste_data_images: false,
            automatic_uploads: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            images_upload_handler: function (blobInfo, success, failure) {
                failure('Image upload is disabled');
            },
            init_instance_callback: function(editor) {
                jobDescriptionEditorInstance = editor;
                
                // Set initial content from hidden field
                const initialContent = hiddenElement.value || '';
                if (initialContent) {
                    editor.setContent(initialContent);
                }
            },
            setup: function(editor) {
                // Debounced update to Livewire
                const updateLivewire = function() {
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(function() {
                        const content = editor.getContent();
                        hiddenElement.value = content;
                        @this.set('editJobDescriptionDescription', content).catch(err => {
                            console.error('Livewire update error:', err);
                        });
                    }, 500);
                };

                // Update Livewire when editor content changes (debounced)
                editor.on('change', updateLivewire);
                editor.on('blur', function() {
                    clearTimeout(updateTimeout);
                    const content = editor.getContent();
                    hiddenElement.value = content;
                    @this.set('editJobDescriptionDescription', content).catch(err => {
                        console.error('Livewire update error:', err);
                    });
                });
            }
        });
    }

    function syncJobDescriptionEditor() {
        const hiddenElement = document.querySelector('#job-description-hidden');
        if (!hiddenElement || !jobDescriptionEditorInstance) {
            return;
        }

        const currentData = jobDescriptionEditorInstance.getContent();
        if (hiddenElement.value !== currentData) {
            jobDescriptionEditorInstance.setContent(hiddenElement.value || '');
        }
    }

    function ensureJobDescriptionEditor() {
        const editorElement = document.querySelector('#job-description-editor-input');
        const hiddenElement = document.querySelector('#job-description-hidden');

        if (!editorElement || !hiddenElement) {
            if (jobDescriptionEditorInstance) {
                tinymce.get('job-description-editor-input')?.remove();
                jobDescriptionEditorInstance = null;
            }
            return;
        }

        if (!jobDescriptionEditorInstance) {
            initJobDescriptionEditor();
            return;
        }

        syncJobDescriptionEditor();
    }

    document.addEventListener('livewire:load', ensureJobDescriptionEditor);
    document.addEventListener('livewire:updated', ensureJobDescriptionEditor);

    if (window.Livewire && window.Livewire.hook) {
        window.Livewire.hook('message.processed', ensureJobDescriptionEditor);
    }

    document.addEventListener('jobDescriptionEditOpened', function() {
        setTimeout(ensureJobDescriptionEditor, 0);
    });
</script>
@endpush