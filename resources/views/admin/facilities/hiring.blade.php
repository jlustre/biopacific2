@push('scripts')
<script src="/js/job_opening_modal.js"></script>
<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('modal-description')) {
        tinymce.init({
            selector: '#modal-description',
            menubar: false,
            plugins: 'lists link table code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link table | code',
            min_height: 180,
            max_height: 320,
            branding: false,
            setup: function (editor) {
                // Ensure Alpine modal re-initializes TinyMCE if needed
                document.addEventListener('showNewOpeningModal', function () {
                    setTimeout(function () {
                        if (!tinymce.get('modal-description')) {
                            tinymce.init(editor.settings);
                        }
                    }, 200);
                });
            }
        });
    }

    // Handle Title/Position "Other" logic
    const form = document.querySelector('form[action*="job_openings.store"]');
    if (form) {
        form.addEventListener('submit', async function (e) {
            const titleSelect = document.getElementById('modal-title');
            const otherInput = document.getElementById('modal-title-other');
            if (titleSelect && titleSelect.value === 'Other' && otherInput && otherInput.value.trim()) {
                e.preventDefault();
                const newTitle = otherInput.value.trim();
                if (!newTitle) return;
                if (!confirm(`Add new position "${newTitle}" to the positions list?`)) return;
                // Save new position via AJAX
                try {
                    const resp = await fetch('/admin/positions/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                        },
                        body: JSON.stringify({ title: newTitle })
                    });
                    if (!resp.ok) throw new Error('Failed to save new position');
                    // Add new option to select and select it
                    const opt = document.createElement('option');
                    opt.value = newTitle;
                    opt.textContent = newTitle;
                    titleSelect.appendChild(opt);
                    titleSelect.value = newTitle;
                    // Remove the other input
                    otherInput.parentNode.removeChild(otherInput);
                    // Submit the form again
                    form.submit();
                } catch (err) {
                    alert('Could not save new position: ' + err.message);
                }
            }
        });
    }
});
</script>
@endpush
@extends('layouts.dashboard')

@section('content')

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    {{ session('error') }}
</div>
@endif
<div class="container mx-auto py-8 px-4">
    <div class="mb-4">
        <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">
            &larr; Back to Facility HR Dashboard
        </a>
    </div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $facility->name }} - Hiring Management</h1>
        <p class="text-gray-600 mt-2">Manage job openings, applicants, and new hire onboarding</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Active Positions</div>
            <div class="text-3xl font-bold text-teal-600 mt-2">{{ $stats['open_openings'] ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">of {{ $stats['total_openings'] ?? 0 }} openings</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Total Applicants</div>
            <div class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_applicants'] ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $stats['pending_applications'] ?? 0 }} pending</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Pre-Employment Submitted</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $stats['submitted_preemployment'] ?? 0 }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Onboarding Complete</div>
            <div class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['completed_preemployment'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6" x-data="{
        activeTab: localStorage.getItem('hiringActiveTab') || 'overview',
        showNewOpeningModal: false
    }" x-init="$watch('activeTab', value => localStorage.setItem('hiringActiveTab', value))">
        <div class="flex border-b border-gray-200 space-x-8">
            <button @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Overview
            </button>
            <button @click="activeTab = 'openings'"
                :class="activeTab === 'openings' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Job Openings
            </button>
            <button @click="activeTab = 'applicants'"
                :class="activeTab === 'applicants' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Applicants
            </button>
            <button @click="activeTab = 'preemployment'"
                :class="activeTab === 'preemployment' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Pre-Employment
            </button>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" class="mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Hiring Pipeline Overview</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Applicants -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Applicants</h3>
                        <div class="space-y-3">
                            @forelse($applications->take(5) as $app)
                            <div class="border border-gray-200 rounded p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $app->first_name }} {{ $app->last_name
                                            }}</p>
                                        <p class="text-sm text-gray-600">{{ $app->jobOpening?->title ?? 'Unknown
                                            Position' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $app->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($app->status === 'rejected') bg-red-100 text-red-800
                                        @elseif($app->status === 'shortlisted') bg-green-100 text-green-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $app->status ?? 'pending')) }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if($app->status === 'pre-employment')
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $app->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $app->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Change Status
                                    </a>
                                    @else
                                    <a href="{{ route('admin.job-applications.show', $app->id) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.job-applications.show', $app->id) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Change Status
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">No applicants yet</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Pre-Employment -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Pre-Employment Submissions</h3>
                        <div class="space-y-3">
                            @forelse($preEmploymentApplications->take(5) as $pre)
                            <div class="border border-gray-200 rounded p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $pre->first_name }} {{ $pre->last_name
                                            }}</p>
                                        <p class="text-sm text-gray-600">{{ $pre->position_applied_for }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $pre->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($pre->status === 'submitted') bg-blue-100 text-blue-800
                                        @elseif($pre->status === 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($pre->status ?? 'draft') }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Change Status
                                    </a>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">No pre-employment submissions yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Openings Tab -->
        <div x-show="activeTab === 'openings'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden" x-data="{ showAddPositionModal: false, newPosition: '', addPositionError: '', addPositionLoading: false }">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Job Openings</h2>
                    <button @click="showNewOpeningModal = true"
                        class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> New Opening
                    </button>
                    <!-- New Opening Modal (full-featured) -->
                    <div x-show="showNewOpeningModal" style="display: none;"
                        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl relative flex flex-col p-4"
                            style="max-height: 90vh;">
                            <button @click="showNewOpeningModal = false"
                                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                            <h3 class="text-xl font-bold mb-4">Create New Job Opening</h3>
                            <div class="overflow-y-auto" style="max-height: 70vh; padding-right: 2px;">
                                <form method="POST"
                                    action="{{ route('admin.facility.job_openings.store', $facility) }}">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold mb-1">Title/Position</label>
                                            <select id="modal-title" name="title" x-ref="titleSelect"
                                                class="w-full border rounded px-3 py-2" required
                                                @change="if ($event.target.value === 'Other') { 
                                                    $refs.titleSelect.removeAttribute('required');
                                                    showAddPositionModal = true; 
                                                }">
                                                <option value="">Select a title</option>
                                                @foreach($positions as $position)
                                                <option value="{{ $position }}">{{ $position }}</option>
                                                @endforeach
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                                <!-- Add Position Modal OUTSIDE the form -->
                                                <div x-show="showAddPositionModal" style="display: none;" class="fixed inset-0 flex items-center justify-center z-60 bg-black bg-opacity-50" x-init="$watch('showAddPositionModal', v => { if (!v) { $refs.titleSelect.setAttribute('required', 'required'); } })">
                                                    <div class="bg-white rounded-lg shadow-lg w-full max-w-md relative flex flex-col p-6">
                                                        <button @click="showAddPositionModal = false; newPosition = ''; addPositionError = '';" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                                                        <h3 class="text-lg font-bold mb-4">Add New Position</h3>
                                                        <form @submit.prevent="
                                                            if (!newPosition.trim()) { addPositionError = 'Title is required.'; return; }
                                                            addPositionLoading = true;
                                                            fetch('/admin/positions/add', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                                                                },
                                                                body: JSON.stringify({ title: newPosition.trim() })
                                                            })
                                                            .then(resp => resp.ok ? resp.json() : Promise.reject('Failed to save'))
                                                            .then(data => {
                                                                if (data && (data.title || data.id)) {
                                                                    // Add to select and select it
                                                                    const select = document.getElementById('modal-title');
                                                                    let exists = false;
                                                                    for (let i = 0; i < select.options.length; i++) {
                                                                        if (select.options[i].value === data.title) { exists = true; break; }
                                                                    }
                                                                    if (!exists) {
                                                                        const opt = document.createElement('option');
                                                                        opt.value = data.title;
                                                                        opt.textContent = data.title;
                                                                        select.appendChild(opt);
                                                                    }
                                                                    select.value = data.title;
                                                                    showAddPositionModal = false;
                                                                    newPosition = '';
                                                                    addPositionError = '';
                                                                } else {
                                                                    addPositionError = 'Could not add position.';
                                                                }
                                                            })
                                                            .catch(() => { addPositionError = 'Could not add position.'; })
                                                            .finally(() => { addPositionLoading = false; });
                                                        ">
                                                            <input type="text" x-model="newPosition" class="w-full border rounded px-3 py-2 mb-2" placeholder="Enter new position title" required autocomplete="off">
                                                            <div class="text-red-600 text-sm mb-2" x-text="addPositionError"></div>
                                                            <div class="flex justify-end">
                                                                <button type="button" @click="showAddPositionModal = false; newPosition = ''; addPositionError = '';" class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
                                                                <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded hover:bg-teal-700" :disabled="addPositionLoading">
                                                                    <span x-show="!addPositionLoading">Add Position</span>
                                                                    <span x-show="addPositionLoading">Adding...</span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Employment Type</label>
                                            <select name="employment_type" class="w-full border rounded px-3 py-2"
                                                required>
                                                <option value="">Select employment type</option>
                                                @foreach($employmentTypes as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Department</label>
                                            <select name="department" class="w-full border rounded px-3 py-2">
                                                <option value="">Select a department</option>
                                                @foreach($departments as $department)
                                                <option value="{{ $department }}">{{ $department }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Reporting To</label>
                                            <select name="reporting_to" class="w-full border rounded px-3 py-2"
                                                required>
                                                <option value="">Select reporting to</option>
                                                @foreach($reportingTo as $supervisor)
                                                <option value="{{ $supervisor }}">{{ $supervisor }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Posted At</label>
                                            <input type="date" name="posted_at" class="w-full border rounded px-3 py-2"
                                                required>
                                        </div>
                                        <div>
                                            <div class="flex items-end gap-4">
                                                <div class="flex-1">
                                                    <label class="block font-semibold mb-1">Status</label>
                                                    <select name="status" class="w-full border rounded px-3 py-2 h-10"
                                                        required style="min-width: 110px; max-width: 140px;">
                                                        <option value="open">Open</option>
                                                        <option value="closed">Closed</option>
                                                        <option value="filled">Filled</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center mb-1">
                                                    <input type="checkbox" name="active" value="1" id="modal-active"
                                                        class="h-4 w-4">
                                                    <label for="modal-active" class="ml-2">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block font-semibold mb-1">Description Template</label>
                                            <select id="modal-template" class="w-full border rounded px-3 py-2">
                                                <option value="">Select a template</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block font-semibold mb-1">Description</label>
                                            <textarea id="modal-description" name="description"
                                                class="w-full border rounded px-3 py-2" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <!-- Fixed action buttons -->
                                    <div class="flex justify-end gap-2 px-4 py-3 bg-white border-t border-gray-200 sticky bottom-0 left-0 right-0 z-10"
                                        style="position: sticky;">
                                        <button type="button" @click="showNewOpeningModal = false"
                                            class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
                                        <button type="submit"
                                            class="px-6 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Create
                                            Job Opening</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Department</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applications</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Posted Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jobOpenings as $opening)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $opening->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $opening->department ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-semibold @if($opening->active) bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                        @if($opening->active) Active @else Closed @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{
                                    $opening->applications->count() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $opening->posted_at?->format('M d, Y') ??
                                    'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.facility.job_openings.show', ['facility' => $facility->id, 'jobOpening' => $opening->id]) }}"
                                        class="text-teal-600 hover:text-teal-700 font-semibold">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No job openings found. <a href="#"
                                        class="text-teal-600 hover:text-teal-700 font-semibold">Create one</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Applicants Tab -->
        <div x-show="activeTab === 'applicants'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">All Applicants</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applied Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($applications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $app->first_name }} {{
                                    $app->last_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->jobOpening?->title ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($app->status === 'rejected') bg-red-100 text-red-800
                                        @elseif($app->status === 'shortlisted') bg-green-100 text-green-800
                                        @elseif($app->status === 'interview') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $app->status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="#" class="text-teal-600 hover:text-teal-700 font-semibold">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No applications found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pre-Employment Tab -->
        <div x-show="activeTab === 'preemployment'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Pre-Employment Applications</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Submitted</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($preEmploymentApplications as $pre)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $pre->first_name }} {{
                                    $pre->last_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->position_applied_for ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($pre->status === 'submitted') bg-blue-100 text-blue-800
                                        @elseif($pre->status === 'completed') bg-green-100 text-green-800
                                        @elseif($pre->status === 'returned') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($pre->status ?? 'draft') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="text-teal-600 hover:text-teal-700 font-semibold">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No pre-employment applications found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection