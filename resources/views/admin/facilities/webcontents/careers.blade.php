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
            <form id="jobForm" method="POST" action="{{ route('admin.facilities.webcontents.careers.store') }}">
                @csrf
                <input type="hidden" name="facility_id" value="{{ $facilityId }}">
                <input type="hidden" name="job_id" id="job_id" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="job_title"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department" id="job_department"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary">
                            <option value="">Select Department...</option>
                            <option value="Administration">Administration</option>
                            <option value="Nursing">Nursing</option>
                            <option value="Dietary/Food Services">Dietary / Food Services</option>
                            <option value="Housekeeping">Housekeeping</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Social Services">Social Services</option>
                            <option value="Activities/Recreation">Activities / Recreation</option>
                            <option value="Rehabilitation/Therapy">Rehabilitation / Therapy</option>
                            <option value="Medical Records">Medical Records</option>
                            <option value="Admissions">Admissions</option>
                            <option value="Business Office">Business Office</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Infection Control">Infection Control</option>
                            <option value="Quality Assurance">Quality Assurance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employment Type</label>
                        <select name="employment_type" id="job_employment_type"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary">
                            <option value="">Select Employment Type...</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="On-Call">OnCall</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                            <option value="Seasonal">Seasonal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Posted At</label>
                        <input type="date" name="posted_at" id="job_posted_at"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expires At</label>
                        <input type="date" name="expires_at" id="job_expires_at"
                            class="mt-1 block w-full border-2 border-gray-400 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="flex items-center mt-6">
                        <input type="checkbox" name="active" id="job_active" value="1" checked class="mr-2">
                        <label class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="md:col-span-2 mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                    <textarea name="description" id="job_description"
                        class="mt-1 block w-full border-2 border-gray-600 px-2 py-1 rounded-md shadow-sm focus:border-primary focus:ring-2 focus:ring-primary rtf-editor"
                        rows="6"></textarea>
                    <small class="text-gray-500">You can use rich text formatting by clicking the icons above.</small>
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

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Store jobs in JS for quick lookup
    const jobs = @json($jobOpenings);
    let editorInstance = null;

    function editJob(id) {
        const job = jobs.find(j => j.id === id);
        if (!job) return;
        document.getElementById('jobFormTitle').innerText = 'Edit Job Opening';
        document.getElementById('jobForm').action = `/admin/facilities/webcontents/careers/${id}`;
        document.getElementById('jobFormSubmit').innerText = 'Update Job';
        document.getElementById('jobFormCancel').classList.remove('hidden');
        document.getElementById('job_id').value = job.id;
        document.getElementById('job_title').value = job.title;
        document.getElementById('job_department').value = job.department;
        document.getElementById('job_employment_type').value = job.employment_type;
        document.getElementById('job_posted_at').value = job.posted_at ?? '';
        document.getElementById('job_expires_at').value = job.expires_at ?? '';
        document.getElementById('job_active').checked = job.active ? true : false;
        if (editorInstance) {
            editorInstance.setData(job.description ?? '');
        } else {
            document.getElementById('job_description').value = job.description ?? '';
        }
        // Change method to PUT
        if (!document.getElementById('jobForm').querySelector('input[name="_method"]')) {
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            document.getElementById('jobForm').appendChild(methodInput);
        } else {
            document.getElementById('jobForm').querySelector('input[name="_method"]').value = 'PUT';
        }
    }

    function resetJobForm() {
        document.getElementById('jobFormTitle').innerText = 'Add Job Opening';
        document.getElementById('jobForm').action = `{{ route('admin.facilities.webcontents.careers.store') }}`;
        document.getElementById('jobFormSubmit').innerText = 'Add Job';
        document.getElementById('jobFormCancel').classList.add('hidden');
        document.getElementById('job_id').value = '';
        document.getElementById('job_title').value = '';
        document.getElementById('job_department').value = '';
        document.getElementById('job_employment_type').value = '';
        document.getElementById('job_posted_at').value = '';
        document.getElementById('job_expires_at').value = '';
        document.getElementById('job_active').checked = true;
        if (editorInstance) {
            editorInstance.setData('');
        } else {
            document.getElementById('job_description').value = '';
        }
        // Remove PUT method
        const methodInput = document.getElementById('jobForm').querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
    }

    // CKEditor initialization
    document.addEventListener('DOMContentLoaded', function() {
        ClassicEditor.create(document.querySelector('textarea.rtf-editor'), {
            toolbar: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
            ],
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
            }
        }).then(editor => {
            editorInstance = editor;
        }).catch(error => { console.error(error); });
    });
</script>
@endsection