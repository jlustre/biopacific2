<div class="space-y-6">
    @if($successMessage)
    <div class="p-4 bg-green-100 text-green-800 rounded-lg font-semibold flex justify-between items-center">
        <span><i class="fas fa-check-circle mr-2"></i>{{ $successMessage }}</span>
        <button wire:click="$set('successMessage', '')" class="text-green-800 hover:text-green-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
        <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-800 hover:text-red-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-100 text-red-800 rounded-lg">
        <p class="font-semibold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Please fix the following errors:
        </p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Add Job Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Add New Job Listing</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Job Title *</label>
                    <select wire:model="title" id="jobTitleSelect"
                        class="w-full border-2 @error('title') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select a position</option>
                        @foreach($positions as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold mb-2">Reporting To *</label>
                    <select wire:model="reporting_to"
                        class="w-full border-2 @error('reporting_to') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select supervisor</option>
                        @foreach($supervisorPositions as $supervisor)
                        <option value="{{ $supervisor }}">{{ $supervisor }}</option>
                        @endforeach
                    </select>
                    @error('reporting_to')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Department</label>
                    <select wire:model="department" id="departmentSelect"
                        class="w-full border-2 @error('department') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    @error('department')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Created By</label>
                    <input type="text" wire:model="created_by" readonly
                        class="w-full border-2 border-gray-300 rounded-lg p-3 bg-gray-100 cursor-not-allowed">
                </div>

            </div>

            <div>
                <label class="block font-semibold mb-2">Job Description Template</label>
                <div class="flex gap-2">
                    <select wire:model="selected_template_id" id="templateSelect"
                        class="flex-1 border-2 @error('selected_template_id') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">-- Select a template (optional) --</option>
                        @foreach($templates as $template)
                        <option value="{{ $template['id'] }}" data-position-id="{{ $template['position_id'] ?? '' }}"
                            data-template-id="{{ $template['id'] }}">{{ $template['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="viewTemplateBtn"
                        class="cursor-pointer bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition whitespace-nowrap flex items-center">
                        <i class="fas fa-eye mr-2"></i> View/Load
                    </button>
                    <button type="button" wire:click="openSaveTemplateModal"
                        class="cursor-pointer bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition whitespace-nowrap flex items-center">
                        <i class="fas fa-save mr-2"></i> Save As
                    </button>

                </div>
                <p class="text-sm text-gray-600 mt-1">Select a pre-defined template, or enter custom description below
                </p>
                @error('selected_template_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div wire:ignore>
                <div class="flex justify-between items-center mb-2">
                    <label class="block font-semibold">Description <span class="text-gray-500 text-sm">(or use
                            template)</span></label>
                </div>
                <textarea id="jobDescriptionInput"
                    class="w-full border-2 @error('description') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    rows="8" placeholder="Job description... (optional if template selected)"></textarea>
            </div>
            @error('description')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror

            <!-- Template Save/Update Section - Always Visible -->
            <div
                class="border-2 @if($editingTemplateId) border-blue-200 bg-blue-50 @else border-gray-200 bg-gray-50 @endif rounded-lg p-4 space-y-3">
                @if($editingTemplateId && $canUpdateOriginal)
                <!-- Owner can update -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-900">
                                <i class="fas fa-info-circle mr-1"></i> Editing Template: <span class="font-bold">{{
                                    $templateName }}</span>
                            </p>
                            <p class="text-xs text-blue-700 mt-1">You can update this template</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="clearTemplateEditing"
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition cursor-pointer">
                                <i class="fas fa-times mr-2"></i> Clear
                            </button>
                            <button type="button" id="updateTemplateBtn"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition cursor-pointer">
                                <i class="fas fa-sync mr-2"></i> Update Template
                            </button>
                        </div>
                    </div>
                </div>
                @elseif($editingTemplateId && !$canUpdateOriginal)
                <!-- Non-owner must save as new -->
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i> You are editing a template created by another
                        user
                    </p>
                    <p class="text-xs text-yellow-700">You can save this as a new template with your own name</p>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Template Name *</label>
                        <input wire:model="templateName" type="text"
                            class="w-full border-2 @error('templateName') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Enter a unique template name">
                        @error('templateName')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(empty($title))
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800 font-semibold mb-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Job Title not selected
                        </p>
                        <p class="text-xs text-yellow-700">Please select a job title to link this template to a position
                        </p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Link to Position *
                            <span class="text-gray-500 text-xs">({{ empty($title) ? 'required' : 'auto-filled from job
                                title' }})</span>
                        </label>
                        <select wire:model="templatePositionId"
                            class="w-full border-2 @error('templatePositionId') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="">-- Select a position --</option>
                            @foreach($positions as $pos)
                            <option value="{{ $positionIdMap[$pos] ?? '' }}">{{ $pos }}</option>
                            @endforeach
                        </select>
                        @error('templatePositionId')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="clearTemplateEditing"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition cursor-pointer">
                            <i class="fas fa-times mr-2"></i> Clear
                        </button>
                        <button type="button" id="saveNewTemplateBtn"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition cursor-pointer">
                            <i class="fas fa-save mr-2"></i> Save as New Template
                        </button>
                    </div>
                </div>
                @else
                <!-- Not editing any template - Save as new -->
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-save mr-1"></i> Save Description as Template
                    </p>
                    <p class="text-xs text-gray-600">Save the current job description as a reusable template</p>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name *</label>
                        <input wire:model="templateName" type="text"
                            class="w-full border-2 @error('templateName') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="e.g. RN Job Description">
                        @error('templateName')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(empty($title))
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800 font-semibold mb-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Job Title not selected
                        </p>
                        <p class="text-xs text-yellow-700">Please select a job title to link this template to a position
                        </p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Link to Position *
                            <span class="text-gray-500 text-xs">({{ empty($title) ? 'required' : 'auto-filled from job
                                title' }})</span>
                        </label>
                        <select wire:model="templatePositionId"
                            class="w-full border-2 @error('templatePositionId') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="">-- Select a position --</option>
                            @foreach($positions as $pos)
                            <option value="{{ $positionIdMap[$pos] ?? '' }}">{{ $pos }}</option>
                            @endforeach
                        </select>
                        @error('templatePositionId')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="saveNewTemplateBtn"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition cursor-pointer">
                            <i class="fas fa-save mr-2"></i> Save as Template
                        </button>
                    </div>
                </div>
                @endif

                @if(session()->has('template_error'))
                <div class="p-3 bg-red-100 text-red-800 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('template_error') }}
                </div>
                @endif
                @if(session()->has('template_success'))
                <div class="p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('template_success') }}
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Posted At *</label>
                    <input wire:model="posted_at" type="date"
                        class="w-full border-2 @error('posted_at') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('posted_at')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Expires At</label>
                    <input wire:model="expires_at" type="date"
                        class="w-full border-2 @error('expires_at') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('expires_at')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Status *</label>
                    <select wire:model="status"
                        class="w-full border-2 @error('status') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select status</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                    @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Active</label>
                    <input wire:model="active" type="checkbox"
                        class="w-6 h-6 border-2 @error('active') border-red-500 @else border-blue-300 @enderror rounded focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('active')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1">
                    <label class="block font-semibold mb-2">Salary Range</label>
                    <input wire:model="salary_range" type="text"
                        class="w-full border-2 @error('salary_range') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. 40,000 - 60,000">
                    @error('salary_range')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label class="block font-semibold mb-2">Salary Unit <span class="text-gray-500 text-sm">(required if
                            salary range provided)</span></label>
                    <select wire:model="salary_unit"
                        class="w-full border-2 @error('salary_unit') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select Salary Unit</option>
                        <option value="hourly">Hourly</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    @error('salary_unit')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label class="block font-semibold mb-2">Employment Type *</label>
                    <select wire:model="employment_type"
                        class="w-full border-2 @error('employment_type') border-red-500 @else border-blue-300 @enderror rounded-lg p-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Select type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                    </select>
                    @error('employment_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button wire:click="addJobOpening" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="cursor-pointer w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center">
                <span wire:loading.remove wire:target="addJobOpening">
                    <i class="fas fa-plus mr-2"></i> Add Job Listing
                </span>
                <span wire:loading wire:target="addJobOpening">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                </span>
            </button>
        </div>
    </div>

    <!-- Job Listings Table -->
    <div class="bg-white rounded-lg shadow">
        <h2 class="text-2xl font-bold p-6 border-b">Job Listings ({{ count($jobs) }})</h2>

        @if($jobs->isEmpty())
        <div class="p-6 text-center text-gray-500">
            No job listings yet
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Job Title</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Department</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Active</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Posted At</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($jobs as $job)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $job->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $job->department ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold {{ $job->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold {{ $job->active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $job->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($job->posted_at)->format('M
                            d, Y') }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button wire:click="toggleExpand({{ $job->id }})"
                                class="cursor-pointer text-blue-600 hover:text-blue-800 font-semibold transition">
                                <i class="fas fa-eye mr-1"></i>View/Edit
                            </button>
                            <button
                                onclick="if(confirm('Edit functionality coming soon - Use View to see details')) { } "
                                class="cursor-pointer text-blue-600 hover:text-blue-800 font-semibold transition">
                                <i class="fas fa-edit mr-1"></i>Edit (Dev)
                            </button>
                            <button wire:click="toggleActive({{ $job->id }})"
                                class="cursor-pointer text-orange-600 hover:text-orange-800 font-semibold transition">
                                <i class="fas fa-{{ $job->active ? 'ban' : 'check' }} mr-1"></i>{{ $job->active ?
                                'Inactivate' : 'Activate' }}
                            </button>
                            <button
                                wire:click="changeStatus({{ $job->id }}, '{{ $job->status === 'open' ? 'closed' : 'open' }}')"
                                class="cursor-pointer text-purple-600 hover:text-purple-800 font-semibold transition">
                                <i class="fas fa-exchange-alt mr-1"></i>{{ $job->status === 'open' ? 'Close' : 'Reopen'
                                }}
                            </button>
                            <button wire:click="deleteJob({{ $job->id }})"
                                onclick="return confirm('Delete this job listing?')"
                                class="cursor-pointer text-red-600 hover:text-red-800 font-semibold transition">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </td>
                    </tr>
                    @if(in_array($job->id, $expandedJobs))
                    <tr class="bg-gray-50 border-b">
                        <td colspan="6" class="px-6 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                    <div class="text-gray-700 bg-white p-3 rounded border border-gray-200 prose prose-sm max-w-none"
                                        style="font-size: inherit;">
                                        <style>
                                            .prose-content ul {
                                                list-style-type: disc;
                                                margin-left: 1.5rem;
                                                padding-left: 1.5rem;
                                            }

                                            .prose-content ol {
                                                list-style-type: decimal;
                                                margin-left: 1.5rem;
                                                padding-left: 1.5rem;
                                            }

                                            .prose-content li {
                                                margin-bottom: 0.5rem;
                                            }
                                        </style>
                                        <div class="prose-content">{!! $job->description !!}</div>
                                    </div>
                                </div>
                                @if($job->salary_range)
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Salary
                                            Range</label>
                                        <p class="text-gray-700">{{ $job->salary_range }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Salary
                                            Unit</label>
                                        <p class="text-gray-700 capitalize">{{ $job->salary_unit ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Employment
                                            Type</label>
                                        <p class="text-gray-700">{{ $job->employment_type ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Reporting
                                            To</label>
                                        <p class="text-gray-700">{{ $job->reporting_to ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Expires At</label>
                                        <p class="text-gray-700">{{ $job->expires_at ?
                                            \Carbon\Carbon::parse($job->expires_at)->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>



    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const positionDepartmentMap = @json($positionDepartmentMap);
    const positionIdMap = @json($positionIdMap);
        const templateMap = @json(collect($templates)->keyBy('id'));
    const jobTitleSelect = document.getElementById('jobTitleSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const templateSelect = document.getElementById('templateSelect');
    const viewTemplateBtn = document.getElementById('viewTemplateBtn');
    
    // Store all template options
    const allTemplateOptions = templateSelect ? Array.from(templateSelect.querySelectorAll('option')).slice(1) : [];
    
    const renderPreview = (content) => {
        if (!modalPreview) {
            return;
        }

        modalPreview.innerHTML = '';

        if (!content) {
            modalPreview.innerHTML = '<p class="text-gray-400">No description</p>';
            return;
        }

        if (content.indexOf('|') !== -1) {
            const items = content.split('|').map(item => item.trim()).filter(item => item.length);
            if (items.length) {
                const ul = document.createElement('ul');
                ul.className = 'list-disc list-inside space-y-2';
                items.forEach((item) => {
                    const li = document.createElement('li');
                    li.className = 'text-gray-700';
                    li.textContent = item;
                    li.appendChild(document.createTextNode(''));
                    ul.appendChild(li);
                });
                modalPreview.appendChild(ul);
                return;
            }
        }

        modalPreview.innerHTML = content;
        modalPreview.querySelectorAll('ul').forEach((ul) => {
            ul.classList.add('list-disc', 'list-inside', 'space-y-2');
        });
        modalPreview.querySelectorAll('ol').forEach((ol) => {
            ol.classList.add('list-decimal', 'list-inside', 'space-y-2');
        });
    };

    // Template view button functionality - directly populate editor
    if (viewTemplateBtn && templateSelect) {
        viewTemplateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedId = templateSelect.value;

            if (!selectedId) {
                alert('Please select a template first');
                return;
            }

            const selectedTemplate = templateMap[selectedId];
            const templateContent = selectedTemplate ? selectedTemplate.contents : '';

            // Populate the TinyMCE editor with template content
            if (window.tinymce && window.tinymce.get('jobDescriptionInput')) {
                window.tinymce.get('jobDescriptionInput').setContent(templateContent || '');
            }
            @this.set('description', templateContent || '');
            
            // Scroll to description field
            setTimeout(() => {
                document.getElementById('jobDescriptionInput').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        });
    }
    
    if (jobTitleSelect && departmentSelect) {
        jobTitleSelect.addEventListener('change', function() {
            const selectedTitle = this.value;
            
            // Look up the department for this position
            if (selectedTitle && positionDepartmentMap[selectedTitle]) {
                const departmentName = positionDepartmentMap[selectedTitle];
                departmentSelect.value = departmentName;
                
                // Dispatch a change event for Livewire to detect the update
                departmentSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            
            // Filter templates based on selected job title
            if (templateSelect) {
                // Keep the default option
                const defaultOption = templateSelect.querySelector('option[value=""]');
                
                // Remove all non-default options
                templateSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
                
                // Get the position ID for the selected title
                const selectedPositionId = selectedTitle && positionIdMap[selectedTitle] ? positionIdMap[selectedTitle].toString() : null;
                
                // Re-add only matching templates
                allTemplateOptions.forEach(option => {
                    const templatePositionId = option.getAttribute('data-position-id');
                    
                    // Show template if it matches selected position ID or has no specific position
                    if (!selectedTitle || !templatePositionId || templatePositionId === selectedPositionId) {
                        templateSelect.appendChild(option.cloneNode(true));
                    }
                });
                
                // Reset template selection
                templateSelect.value = '';
            }
        });
    }
});
    </script>

    <!-- TinyMCE Integration for Visual and Source Code Editing -->
    <script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        let descriptionEditor;
        let updateTimeout;
        
        tinymce.init({
            selector: '#jobDescriptionInput',
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
                descriptionEditor = editor;
                
                // Set initial content from Livewire
                const initialContent = @this.get('description') || '';
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
                        @this.set('description', content).catch(err => {
                            console.error('Livewire update error:', err);
                        });
                    }, 500);
                };

                // Update Livewire when editor content changes (debounced)
                editor.on('change', updateLivewire);
                editor.on('blur', function() {
                    clearTimeout(updateTimeout);
                    const content = editor.getContent();
                    @this.set('description', content).catch(err => {
                        console.error('Livewire update error:', err);
                    });
                });

                // Listen for Livewire updates to sync editor content
                window.addEventListener('description-updated', event => {
                    if (event.detail && editor) {
                        editor.setContent(event.detail.description || '');
                    }
                });
            }
        });
        
        // Template save/update button handlers - sync TinyMCE before saving
        document.addEventListener('click', function(e) {
            if (e.target.id === 'viewTemplateBtn' || e.target.closest('#viewTemplateBtn')) {
                e.preventDefault();
                @this.call('viewAndLoadTemplate');
            }
            
            if (e.target.id === 'updateTemplateBtn' || e.target.closest('#updateTemplateBtn')) {
                e.preventDefault();
                // Force sync TinyMCE content to Livewire immediately
                if (window.tinymce && window.tinymce.get('jobDescriptionInput')) {
                    const content = window.tinymce.get('jobDescriptionInput').getContent();
                    @this.set('description', content).then(() => {
                        @this.call('saveAsTemplate');
                    }).catch(err => {
                        console.error('Failed to sync content:', err);
                    });
                } else {
                    @this.call('saveAsTemplate');
                }
            }
            
            if (e.target.id === 'saveNewTemplateBtn' || e.target.closest('#saveNewTemplateBtn')) {
                e.preventDefault();
                // Force sync TinyMCE content to Livewire immediately
                if (window.tinymce && window.tinymce.get('jobDescriptionInput')) {
                    const content = window.tinymce.get('jobDescriptionInput').getContent();
                    @this.set('description', content).then(() => {
                        @this.call('saveAsTemplate');
                    }).catch(err => {
                        console.error('Failed to sync content:', err);
                    });
                } else {
                    @this.call('saveAsTemplate');
                }
            }
        });
    });
    </script>
    @endpush