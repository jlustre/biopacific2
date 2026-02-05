@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">Job Openings for <strong>{{ $facility->name }}</strong></h1>
    <form method="POST" action="{{ route('admin.facility.job_openings.store', $facility) }}"
        class="mb-6 bg-white p-4 rounded shadow">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Facility Name</label>
                <input type="text" class="form-input w-full" value="{{ $facility->name }}" disabled>
            </div>
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <select name="title" id="title-select" class="form-select w-full" onchange="toggleTitleInput(this)">
                    <option value="">Select Title</option>
                    <option value="Registered Nurse">Registered Nurse</option>
                    <option value="Licensed Vocational Nurse">Licensed Vocational Nurse</option>
                    <option value="Certified Nursing Assistant">Certified Nursing Assistant</option>
                    <option value="Director of Nursing">Director of Nursing</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Dietary Aide">Dietary Aide</option>
                    <option value="Housekeeper">Housekeeper</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Social Worker">Social Worker</option>
                    <option value="Activities Director">Activities Director</option>
                    <option value="Receptionist">Receptionist</option>
                    <option value="Other">Other (add below)</option>
                </select>
                <input type="text" name="title_other" id="title-other" class="form-input w-full mt-2"
                    placeholder="Add new title" style="display:none;">
            </div>
            <div>
                <label class="block font-semibold mb-1">Job Summary</label>
                <textarea name="description" class="form-input w-full" rows="2"></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Detailed Description</label>
                <textarea name="detailed_description" class="form-input w-full" rows="2"></textarea>
            </div>

            <!-- Job Description Template Modal Trigger Button -->
            <div class="col-span-2 flex justify-end">
                <button type="button" id="open-template-modal"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                    <i class="fas fa-file-alt mr-2"></i> Use Description Template
                </button>
            </div>
            <!-- Modal Markup -->
            <div id="template-modal"
                class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-40 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
                    <button id="close-template-modal"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Job Description Templates</h2>
                    <div class="mb-4 flex gap-2">
                        <select id="template-title-filter" class="form-select">
                            <option value="">All Titles</option>
                            <option value="Registered Nurse">Registered Nurse</option>
                            <option value="Licensed Vocational Nurse">Licensed Vocational Nurse</option>
                            <option value="Certified Nursing Assistant">Certified Nursing Assistant</option>
                            <option value="Director of Nursing">Director of Nursing</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Dietary Aide">Dietary Aide</option>
                            <option value="Housekeeper">Housekeeper</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Social Worker">Social Worker</option>
                            <option value="Activities Director">Activities Director</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Other">Other</option>
                        </select>
                        <button id="fetch-templates"
                            class="px-2 py-1 bg-gray-300 rounded hover:bg-gray-400">Refresh</button>
                        <button id="new-template-btn"
                            class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 ml-auto">New
                            Template</button>
                    </div>
                    <div class="overflow-y-auto max-h-40 mb-4">
                        <table class="min-w-full table-auto text-sm" id="templates-table">
                            <thead>
                                <tr>
                                    <th class="px-2 py-1">Name</th>
                                    <th class="px-2 py-1">Title</th>
                                    <th class="px-2 py-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Templates will be loaded here via JS -->
                            </tbody>
                        </table>
                    </div>
                    <form id="template-form" class="space-y-2">
                        <input type="hidden" id="template-id">
                        <div>
                            <label class="block font-semibold mb-1">Template Name</label>
                            <input type="text" id="template-name" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Title (optional)</label>
                            <input type="text" id="template-title" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Description</label>
                            <textarea id="template-description" class="form-input w-full" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Detailed Description</label>
                            <textarea id="template-detailed-description" class="form-input w-full" rows="2"></textarea>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button type="button" id="save-template-btn"
                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                            <button type="button" id="delete-template-btn"
                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 hidden">Delete</button>
                            <button type="button" id="apply-template-btn"
                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">Apply to
                                Form</button>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-1">Department</label>
                <select name="department" class="form-select w-full">
                    <option value="">Select Department</option>
                    <option value="Nursing">Nursing</option>
                    <option value="Administration">Administration</option>
                    <option value="Dietary">Dietary</option>
                    <option value="Housekeeping">Housekeeping</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Social Services">Social Services</option>
                    <option value="Activities">Activities</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Employment Type</label>
                <select name="employment_type" class="form-select w-full">
                    <option value="">Select Employment Type</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Per Diem">Per Diem</option>
                    <option value="Temporary">Temporary</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Reporting To</label>
                <select name="reporting_to" class="form-select w-full">
                    <option value="">Select Reporting To</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Director of Nursing">Director of Nursing</option>
                    <option value="Assistant Director of Nursing">Assistant Director of Nursing</option>
                    <option value="Charge Nurse">Charge Nurse</option>
                    <option value="Social Services">Social Services</option>
                    <option value="Activities Director">Activities Director</option>
                    <option value="Maintenance Supervisor">Maintenance Supervisor</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Posted At</label>
                <input type="date" name="posted_at" class="form-input w-full">
            </div>
            <div>
                <label class="block font-semibold mb-1">Expires At</label>
                <input type="date" name="expires_at" class="form-input w-full">
            </div>
            <div>
                <label class="block font-semibold mb-1">Active</label>
                <select name="active" class="form-select w-full">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="status" class="form-select w-full">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="pending">Pending</option>
                    <option value="filled">Filled</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Created By</label>
                <input type="text" class="form-input w-full" value="{{ auth()->user()->name ?? 'N/A' }}" disabled>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit"
                style="background-color: #22c55e; color: #fff; font-weight: bold; padding: 0.75rem 2rem; border-radius: 0.5rem; font-size: 1.1rem; box-shadow: 0 2px 6px #0001; border: none;">Submit
                Job Listing</button>
        </div>
        @push('scripts')
        <script>
            function toggleTitleInput(select) {
                var otherInput = document.getElementById('title-other');
                if (select.value === 'Other') {
                    otherInput.style.display = '';
                    otherInput.required = true;
                } else {
                    otherInput.style.display = 'none';
                    otherInput.required = false;
                }
            }

            // Modal logic
            const openModalBtn = document.getElementById('open-template-modal');
            const modal = document.getElementById('template-modal');
            const closeModalBtn = document.getElementById('close-template-modal');
            openModalBtn.addEventListener('click', () => { modal.classList.remove('hidden'); modal.classList.add('flex'); fetchTemplates(); });
            closeModalBtn.addEventListener('click', () => { modal.classList.add('hidden'); modal.classList.remove('flex'); clearTemplateForm(); });
            window.addEventListener('click', (e) => { if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); clearTemplateForm(); } });

            // AJAX helpers
            function getCSRF() { return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }
            function fetchTemplates() {
                const title = document.getElementById('template-title-filter').value;
                fetch(`/admin/job-description-templates?title=${encodeURIComponent(title)}`)
                    .then(res => res.json())
                    .then(data => renderTemplatesTable(data));
            }
            function renderTemplatesTable(templates) {
                const tbody = document.querySelector('#templates-table tbody');
                tbody.innerHTML = '';
                templates.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td class='px-2 py-1'>${t.name}</td><td class='px-2 py-1'>${t.title || ''}</td><td class='px-2 py-1'><button type='button' class='text-blue-600 hover:underline' onclick='editTemplate(${JSON.stringify(t)})'>Edit</button></td>`;
                    tbody.appendChild(tr);
                });
            }
            document.getElementById('fetch-templates').onclick = fetchTemplates;
            document.getElementById('template-title-filter').onchange = fetchTemplates;

            // Template form logic
            function clearTemplateForm() {
                document.getElementById('template-id').value = '';
                document.getElementById('template-name').value = '';
                document.getElementById('template-title').value = '';
                document.getElementById('template-description').value = '';
                document.getElementById('template-detailed-description').value = '';
                document.getElementById('delete-template-btn').classList.add('hidden');
            }
            document.getElementById('new-template-btn').onclick = () => { clearTemplateForm(); };

            window.editTemplate = function(template) {
                document.getElementById('template-id').value = template.id;
                document.getElementById('template-name').value = template.name;
                document.getElementById('template-title').value = template.title || '';
                document.getElementById('template-description').value = template.description || '';
                document.getElementById('template-detailed-description').value = template.detailed_description || '';
                document.getElementById('delete-template-btn').classList.remove('hidden');
            };

            document.getElementById('save-template-btn').onclick = function() {
                const id = document.getElementById('template-id').value;
                const payload = {
                    name: document.getElementById('template-name').value,
                    title: document.getElementById('template-title').value,
                    description: document.getElementById('template-description').value,
                    detailed_description: document.getElementById('template-detailed-description').value
                };
                const url = id ? `/admin/job-description-templates/${id}` : '/admin/job-description-templates';
                const method = id ? 'PUT' : 'POST';
                fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(() => { fetchTemplates(); clearTemplateForm(); });
            };

            document.getElementById('delete-template-btn').onclick = function() {
                const id = document.getElementById('template-id').value;
                if (!id) return;
                if (!confirm('Delete this template?')) return;
                fetch(`/admin/job-description-templates/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': getCSRF() }
                })
                .then(res => res.json())
                .then(() => { fetchTemplates(); clearTemplateForm(); });
            };

            document.getElementById('apply-template-btn').onclick = function() {
                document.querySelector('textarea[name="description"]').value = document.getElementById('template-description').value;
                document.querySelector('textarea[name="detailed_description"]').value = document.getElementById('template-detailed-description').value;
                modal.classList.add('hidden');
                clearTemplateForm();
            };
        </script>
        @endpush
    </form>

    <h2 class="text-xl font-semibold mb-2">All Job Openings</h2>
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Reporting To</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                <tr>
                    <td class="border px-4 py-2">{{ $job->title }}</td>
                    <td class="border px-4 py-2">{{ $job->reporting_to }}</td>
                    <td class="border px-4 py-2">
                        <form method="POST"
                            action="{{ route('admin.facility.job_openings.update', [$facility, $job]) }}">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="form-select">
                                <option value="open" @if($job->status=='open') selected @endif>Open</option>
                                <option value="closed" @if($job->status=='closed') selected @endif>Closed</option>
                            </select>
                        </form>
                    </td>
                    <td class="border px-4 py-2 flex gap-2">
                        <!-- Edit Icon -->
                        <a href="{{ route('admin.facility.job_openings.edit', [$facility, $job]) }}" title="Edit"
                            class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- View Icon -->
                        <a href="{{ route('admin.facility.job_openings.show', [$facility, $job]) }}" title="View"
                            class="text-green-600 hover:text-green-800">
                            <i class="fas fa-eye"></i>
                        </a>
                        <!-- Delete Icon -->
                        <form method="POST"
                            action="{{ route('admin.facility.job_openings.destroy', [$facility, $job]) }}"
                            style="display:inline;" onsubmit="return confirm('Delete this job opening?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete" class="text-red-600 hover:text-red-800"
                                style="background:none; border:none; padding:0;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection