@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Careers Management</h1>
                        <p class="text-gray-600">Manage career opportunities for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection Dropdown -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.facilities.webcontents.careers') }}">
                <div class="mb-6">
                    <label for="facilitySelect" class="block text-sm font-semibold text-gray-700 mb-3">Select
                        Facility:</label>
                    <div class="relative w-full max-w-md">
                        <select id="facilitySelect" name="facility_id"
                            class="w-full pl-12 pr-12 py-4 border-2 border-gray-200 rounded-xl bg-white text-gray-700 font-medium focus:ring-3 focus:ring-teal-200 focus:border-teal-500 hover:border-gray-300 transition-all duration-200 appearance-none cursor-pointer shadow-sm text-sm sm:text-base"
                            onchange="this.form.submit()">
                            <option value="" class="text-gray-500">Choose a facility...</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}" @if(($facilityId ?? null)==$facility->id) selected
                                @endif>
                                {{ $facility->name }} - {{ $facility->city ?? 'N/A' }}, {{ $facility->state ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400 transition-colors duration-200" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-building text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 max-w-md">Select a facility to view and manage its career
                        opportunities</p>


                </div>
            </form>
        </div>

        <!-- Placeholder Content -->
        @if(($facilityId ?? null))
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Openings</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobOpenings as $job)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->department }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->employment_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->posted_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->active ? 'Yes' : 'No' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                            <form method="POST"
                                action="{{ route('admin.facilities.webcontents.careers.destroy', $job) }}"
                                onsubmit="return confirm('Delete this job opening?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                            <button type="button" class="text-blue-600 hover:underline"
                                onclick="editJob({{ $job->id }})">Edit</button>
                            <a href="{{ route('admin.facilities.webcontents.careers.applications', $job) }}"
                                class="text-green-600 hover:underline">Applications</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No job openings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Job Opening Form -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="jobFormTitle">Add Job Opening</h3>

            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Please fix the following errors:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="jobForm" method="POST" action="{{ route('admin.facilities.webcontents.careers.store') }}">
                @csrf
                <input type="hidden" name="facility_id" value="{{ $facilityId }}">
                <input type="hidden" name="job_id" id="job_id" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-data="positionFilter()" x-init="init()">
                        <label class="block text-sm font-medium text-gray-700">Department *</label>
                        <select name="department" id="job_department" x-model="selectedDepartment"
                            @change="filterPositions"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary"
                            required>
                            <option value="">Select Department...</option>
                            <template x-for="dept in departments" :key="dept">
                                <option :value="dept" x-text="dept"></option>
                            </template>
                        </select>
                        @error('department')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <label class="block text-sm font-medium text-gray-700 mt-4">Position Title *</label>
                        <select name="position_id" id="position_id" x-model="selectedPositionId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            required @change="$dispatch('position-changed', selectedPositionId)">
                            <option value="">Select Position...</option>
                            <template x-for="pos in filteredPositions" :key="pos.id">
                                <option :value="pos.id" x-text="pos.title + ' (' + pos.department + ')' "></option>
                            </template>
                        </select>
                        <small class="text-gray-500">Select the position for this job opening.</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Posted At *</label>
                        <input type="date" name="posted_at" id="job_posted_at"
                            value="{{ old('posted_at', date('Y-m-d')) }}"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary"
                            required>
                        @error('posted_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expires At</label>
                        <input type="date" name="expires_at" id="job_expires_at" value="{{ old('expires_at') }}"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary">
                        @error('expires_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center mt-6">
                        <input type="checkbox" name="active" id="job_active" value="1" checked class="mr-2">
                        <label class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="md:col-span-2 mt-4">
                    <!-- Position Title select is now handled above with AlpineJS -->
                </div>

                <div class="md:col-span-2 mt-4" x-data="jobDescManager()">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Description Items</label>
                    <!-- Job Description Items logic remains here -->
                </div>
                <div class="mt-4">
                    <button type="submit" id="jobFormSubmit"
                        class="bg-teal-500 hover:bg-teal-600 cursor-pointer text-white px-4 py-2 rounded">Add
                        Job</button>
                    <button type="button" id="jobFormCancel"
                        class="ml-2 px-4 py-2 rounded bg-gray-300 text-gray-700 hidden"
                        onclick="resetJobForm()">Cancel</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    // AlpineJS for Department/Position dynamic select
function positionFilter() {
    return {
        positions: [],
        departments: [],
        selectedDepartment: '',
        selectedPositionId: '',
        filteredPositions: [],
        init() {
            fetch('/admin/positions/all')
                .then(res => res.json())
                .then(data => {
                    this.positions = data;
                    this.departments = [...new Set(data.map(p => p.department).filter(Boolean))].sort();
                });
            this.$watch('selectedDepartment', value => this.filterPositions());
        },
        filterPositions() {
            if (!this.selectedDepartment) {
                this.filteredPositions = [];
                this.selectedPositionId = '';
                return;
            }
            this.filteredPositions = this.positions.filter(p => p.department === this.selectedDepartment);
            if (!this.filteredPositions.some(p => p.id === this.selectedPositionId)) {
                this.selectedPositionId = '';
            }
        }
    }
}

function jobDescManager() {
    return {
        jobDescriptions: [],
        selectedJobDescId: '',
        selectedItems: [],
        selectedPositionId: '',
        fetchJobDescriptions() {
            const posId = document.getElementById('position_id').value;
            if (!posId) return;
            fetch(`/admin/job-descriptions/by-position/${posId}`)
                .then(res => res.json())
                .then(data => { this.jobDescriptions = data; });
        },
        addJobDesc() {
            if (!this.selectedJobDescId) return;
            const desc = this.jobDescriptions.find(d => d.id == this.selectedJobDescId);
            if (desc && !this.selectedItems.some(i => i.id == desc.id)) {
                this.selectedItems.push({...desc});
            }
            this.selectedJobDescId = '';
        },
        removeJobDesc(idx) {
            this.selectedItems.splice(idx, 1);
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('positionFilter', positionFilter);
    Alpine.data('jobDescManager', jobDescManager);
});

document.addEventListener('DOMContentLoaded', function() {
    if (window.ClassicEditor) {
        ClassicEditor.create(document.querySelector('#detailed_description'), {
            toolbar: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
            ],
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
            }
        }).then(editor => {
            window.editorInstance = editor;
        }).catch(error => { 
            console.error('CKEditor error:', error); 
        });
    }
});
</script>
@endpush