@extends('layouts.dashboard')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">
            &larr; Back to Facility HR Dashboard
        </a>
    </div>
    <h1 class="text-xl font-bold mb-6"><strong>{{ $facility->name }}</strong></h1>

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">{{ session('error') }}</div>
    @endif

    <!-- Modal and Button in shared Alpine.js scope -->
    <div x-data="{ showNewOpeningModal: false, showSaveTemplateModal: false }"
        @keydown.escape.window="showNewOpeningModal = false; showSaveTemplateModal = false">
        <!-- Button to open modal -->
        <button type="button"
            @click="showNewOpeningModal = true; $nextTick(() => window.initJobOpeningTinyMCE && window.initJobOpeningTinyMCE())"
            class="mb-4 px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">New Job Opening</button>

        <!-- Modal -->
        <div x-show="showNewOpeningModal" style="display: none;"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl relative flex flex-col p-6" style="max-height: 90vh;">
                <button type="button" @click="showNewOpeningModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h3 class="text-xl font-bold mb-4 pr-8">Create New Job Opening</h3>
                <div class="overflow-y-auto flex-1" style="max-height: calc(90vh - 8rem);">
                <form id="job-opening-create-form" method="POST" action="{{ route('admin.facility.job_openings.store', $facility) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div x-data="{ showOther: false }">
                            <label class="block font-semibold mb-1">Title</label>
                            <select id="modal-title" name="title" class="w-full border rounded px-3 py-2"
                                x-on:change="showOther = $event.target.value === 'Other'"
                                required>
                                <option value="">Select a title</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->title }}"
                                        data-department="{{ $position->department?->name ?? '' }}"
                                        data-reporting-to="{{ $position->reportsToPosition?->title ?? '' }}">{{ $position->title }}</option>
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
                            <select id="modal-department" name="department" class="w-full border rounded px-3 py-2" x-on:change="showOtherDepartment = $event.target.value === 'Other'">
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
                            <select id="modal-reporting-to" name="reporting_to" class="w-full border rounded px-3 py-2" x-on:change="showOtherSupervisor = $event.target.value === 'Other'" required>
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
                            <div class="flex flex-wrap items-end justify-between gap-2 mb-1">
                                <label class="block font-semibold">Description Template</label>
                                <button type="button" onclick="window.openJobOpeningSaveTemplateModal && window.openJobOpeningSaveTemplateModal()"
                                    class="text-sm font-semibold text-teal-700 hover:text-teal-900">Save description as template</button>
                            </div>
                            <select id="modal-template" class="w-full border rounded px-3 py-2">
                                <option value="">Select a template to load</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Templates are saved per position title. Select a title first to see matching templates.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Description</label>
                            <textarea id="modal-description" name="description" class="w-full border rounded px-3 py-2"
                                rows="8"></textarea>
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

        <!-- Save description as template -->
        <div x-show="showSaveTemplateModal" x-cloak style="display: none;"
            class="fixed inset-0 flex items-center justify-center z-[60] bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative" @click.outside="showSaveTemplateModal = false">
                <button type="button" @click="showSaveTemplateModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h3 class="text-lg font-bold mb-1 pr-8">Save Description as Template</h3>
                <p class="text-sm text-slate-500 mb-4">Reuse this description when creating future job openings for the same position.</p>

                <div id="job-opening-template-save-error" class="hidden mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800"></div>
                <div id="job-opening-template-save-success" class="hidden mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"></div>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold mb-1 text-sm" for="job-opening-template-name">Template name *</label>
                        <input type="text" id="job-opening-template-name" class="w-full border rounded px-3 py-2 text-sm"
                            placeholder="e.g. Registered Nurse - Standard" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-sm" for="job-opening-template-position">Assign to position *</label>
                        <select id="job-opening-template-position" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Select a position</option>
                            @foreach($positions as $position)
                            <option value="{{ $position->title }}">{{ $position->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="showSaveTemplateModal = false"
                        class="px-4 py-2 text-sm rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
                    <button type="button" id="job-opening-template-save-btn"
                        onclick="window.submitJobOpeningSaveTemplate && window.submitJobOpeningSaveTemplate()"
                        class="px-4 py-2 text-sm rounded bg-teal-600 text-white hover:bg-teal-700">Save Template</button>
                </div>
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
<script>
    window.jobOpeningPositionDefaults = @json($positionDefaults ?? []);
    window.jobOpeningConfig = {
        templateSaveUrl: @json(route('admin.facility.job_openings.template.save', $facility)),
        csrfToken: @json(csrf_token()),
    };
</script>
<script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/job_opening_modal.js?v=4"></script>
@endpush