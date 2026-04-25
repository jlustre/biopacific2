@extends('layouts.dashboard')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">
            &larr; Back to Facility HR Dashboard
        </a>
    </div>
    <h1 class="text-xl font-bold mb-6"><strong>{{ $facility->name }}</strong></h1>

    <!-- Modal and Button in shared Alpine.js scope -->
    <div x-data="{ showNewOpeningModal: false }" @keydown.escape.window="showNewOpeningModal = false">
        <!-- Button to open modal -->
        <button @click="showNewOpeningModal = true"
            class="mb-4 px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">New Job Opening</button>

        <!-- Modal -->
        <div x-show="showNewOpeningModal" style="display: none;"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl relative">
                <button @click="showNewOpeningModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h3 class="text-xl font-bold mb-4">Create New Job Opening</h3>
                <form method="POST" action="{{ route('admin.facility.job_openings.store', $facility) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div x-data="{ showOther: false }">
                            <label class="block font-semibold mb-1">Title</label>
                            <select id="modal-title" name="title" class="w-full border rounded px-3 py-2" x-on:change="showOther = $event.target.value === 'Other'" required>
                                <option value="">Select a title</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->title }}">{{ $position->title }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                            <input x-show="showOther" x-transition type="text" name="title_other" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter other title">
                        </div>
                        <div x-data="{ showOtherEmployment: false }">
                            <label class="block font-semibold mb-1">Employment Type</label>
                            <select name="employment_type" class="w-full border rounded px-3 py-2" x-on:change="showOtherEmployment = $event.target.value === 'Other'" required>
                                <option value="">Select employment type</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Per Diem">Per Diem</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Contract">Contract</option>
                                <option value="Other">Other</option>
                            </select>
                            <input x-show="showOtherEmployment" x-transition type="text" name="employment_type_other" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter other employment type">
                        </div>
                        <div x-data="{ showOtherDepartment: false }">
                            <label class="block font-semibold mb-1">Department</label>
                            <select name="department" class="w-full border rounded px-3 py-2" x-on:change="showOtherDepartment = $event.target.value === 'Other'">
                                <option value="">Select department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                            <input x-show="showOtherDepartment" x-transition type="text" name="department_other" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter other department">
                        </div>
                        <div x-data="{ showOtherSupervisor: false }">
                            <label class="block font-semibold mb-1">Reporting To</label>
                            <select name="reporting_to" class="w-full border rounded px-3 py-2" x-on:change="showOtherSupervisor = $event.target.value === 'Other'" required>
                                <option value="">Select supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->title }}">{{ $supervisor->title }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                            <input x-show="showOtherSupervisor" x-transition type="text" name="reporting_to_other" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter other supervisor">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Posted At</label>
                            <input type="date" name="posted_at" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Status</label>
                            <select name="status" class="w-full border rounded px-3 py-2" required>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="filled">Filled</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Description Template</label>
                            <select id="modal-template" class="w-full border rounded px-3 py-2">
                                <option value="">Select a template</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Description</label>
                            <textarea id="modal-description" name="description" class="w-full border rounded px-3 py-2"
                                rows="3" required></textarea>
                        </div>
                        <div class="flex items-center mt-2 md:col-span-2">
                            <input type="checkbox" name="active" value="1" id="modal-active">
                            <label for="modal-active" class="ml-2">Active</label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="showNewOpeningModal = false"
                            class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Create
                            Job Opening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Job Openings Listing Table -->
    <div class="mt-8">
        <h2 class="text-lg font-bold mb-2">Job Openings</h2>
        <table class="min-w-full bg-white border border-gray-200 rounded">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">Title</th>
                    <th class="px-4 py-2 border-b">Employment Type</th>
                    <th class="px-4 py-2 border-b">Department</th>
                    <th class="px-4 py-2 border-b">Reporting To</th>
                    <th class="px-4 py-2 border-b">Posted At</th>
                    <th class="px-4 py-2 border-b">Status</th>
                    <th class="px-4 py-2 border-b">Active</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td class="px-4 py-2 border-b">{{ $job->title }}</td>
                    <td class="px-4 py-2 border-b">{{ $job->employment_type }}</td>
                    <td class="px-4 py-2 border-b">{{ $job->department }}</td>
                    <td class="px-4 py-2 border-b">{{ $job->reporting_to }}</td>
                    <td class="px-4 py-2 border-b">{{ $job->posted_at }}</td>
                    <td class="px-4 py-2 border-b">{{ ucfirst($job->status) }}</td>
                    <td class="px-4 py-2 border-b">{{ $job->active ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-2 border-b">
                        <a href="{{ route('admin.facility.job_openings.show', [$facility, $job]) }}" class="text-blue-600 hover:underline mr-2">View</a>
                        <a href="{{ route('admin.facility.job_openings.edit', [$facility, $job]) }}" class="text-yellow-600 hover:underline mr-2">Edit</a>
                        <form action="{{ route('admin.facility.job_openings.destroy', [$facility, $job]) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this job opening?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline bg-transparent border-none p-0 m-0 cursor-pointer">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-2 text-center text-gray-500">No job openings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="/js/job_opening_modal.js"></script>
@endpush