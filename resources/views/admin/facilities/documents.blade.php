
@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Facility Uploads</h1>
    @php
        $required = "";
    @endphp
    {{-- Success & Error Messaging --}}
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
    <div class="p-6 mb-6 bg-white rounded shadow">
        <form id="facility-upload-form"
              x-data="facilityUploadForm({
                  facilityId: '{{ old('facility_id', $facility->id ?? '') }}',
                  oldEmployeeId: '{{ old('employee_id') }}',
                  oldUploadTypeId: '{{ old('upload_type_id') }}',
                  oldEffectiveStartDate: '{{ old('effective_start_date') }}',
                  oldEffectiveEndDate: '{{ old('effective_end_date') }}',
                  oldExpiresAt: '{{ old('expires_at') }}',
                  oldComments: @json(old('comments', '')),
              })"
              x-init="init()"
              method="POST"
              action="{{ route('admin.facility.uploads.store', ['facility' => $facility->id]) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    {{-- <div class="mb-2 text-xs text-blue-700">Employees found: <span x-text="employees.length"></span></div> --}}
                    <label class="block mb-1 text-xs font-semibold">Select Facility <span class="text-red-600">*</span></label>
                    <select x-model="facility_id" @change="fetchEmployees()" name="facility_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600" required>
                        <option value="">-- Choose Facility --</option>
                        <template x-for="facility in facilities" :key="facility.id">
                            <option :value="facility.id" x-text="facility.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold">Employee <span class="text-red-600">*</span></label>
                    <select x-model="employee_id" name="employee_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600" required>
                        <option value="">-- Select Employee --</option>
                        <template x-for="employee in employees" :key="employee.emp_id">
                            <option :value="employee.emp_id" x-text="employee.last_name + ', ' + employee.first_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
                    <select x-model="upload_type_id" name="upload_type_id" class="form-select w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600" required>
                        <option value="">Select Type</option>
                        <template x-for="type in uploadTypes" :key="type.id">
                            <option :value="type.id" x-text="type.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold">File <span class="text-red-600">*</span></label>
                    <input type="file" name="file" class="form-input w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600" required>
                </div>
                <div class="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <template x-if="selectedUploadType && (selectedUploadType.requires_expiry == 1 || selectedUploadType.requires_expiry == '1')">
                        <div class="contents">
                            <div>
                                <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                                <input type="date" name="effective_start_date" x-model="effective_start_date" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold">Effective End Date</label>
                                <input type="date" name="effective_end_date" x-model="effective_end_date" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold">Expires At <span class='text-red-600'>*</span></label>
                                <input type="date" name="expires_at" x-model="expires_at" :required="selectedUploadType && selectedUploadType.requires_expiry == 1" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
                            </div>
                        </div>
                    </template>
                </div>
                <div class="col-span-2 flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block mb-1 text-xs font-semibold">Comments</label>
                        <textarea name="comments" x-model="comments" rows="2" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full min-w-[220px] resize-y"></textarea>
                    </div>
                    <div class="flex-shrink-0">
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Upload</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
    function facilityUploadForm(opts) {
        return {
            facilities: [],
            employees: [],
            uploadTypes: [],
            facility_id: opts.facilityId || '',
            employee_id: opts.oldEmployeeId || '',
            upload_type_id: opts.oldUploadTypeId || '',
            effective_start_date: opts.oldEffectiveStartDate || '',
            effective_end_date: opts.oldEffectiveEndDate || '',
            expires_at: opts.oldExpiresAt || '',
            comments: opts.oldComments || '',
            get selectedUploadType() {
                return this.uploadTypes.find(t => t.id == this.upload_type_id) || null;
            },
            init() {
                fetch('/admin/facilities/all')
                    .then(res => res.json())
                    .then(data => {
                        this.facilities = data;
                        if (this.facility_id) {
                            this.facility_id = String(this.facility_id);
                            this.fetchEmployees();
                        }
                    });
                fetch('/admin/upload-types/all')
                    .then(res => res.json())
                    .then(data => { this.uploadTypes = data; });
            },
            fetchEmployees() {
                if (!this.facility_id) { this.employees = []; return; }
                fetch(`/admin/facility/${this.facility_id}/employees/all`)
                    .then(res => res.json())
                    .then(data => {
                        this.employees = data;
                    });
            }
        }
    }
    </script>
    <div class="p-6 bg-white rounded shadow">
        <form method="GET" class="flex flex-wrap items-end gap-4 mb-4">
            <div>
                <label class="block mb-1 text-xs font-semibold">Search by Name</label>
                <input type="text" name="search" value="{{ request('search') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input" placeholder="File name...">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Filter by Facility</label>
                <select name="facility_id" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="">All Facilities</option>
                    @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                    <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 ml-2 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Filter</button>
        </form>
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border text-sm">File Name</th>
                    <th class="px-3 py-2 border text-sm">Type</th>
                    <th class="px-3 py-2 border text-sm">Facility</th>
                    <th class="px-3 py-2 border text-sm">Uploaded By</th>
                    <th class="px-3 py-2 border text-sm">Size</th>
                    <th class="px-3 py-2 border text-sm">Effective Dates</th>
                    <th class="px-3 py-2 border text-sm">Expires</th>
                    <th class="px-3 py-2 border text-sm">Uploaded</th>
                    <th class="px-3 py-2 border text-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $query = App\Models\Upload::with(['facility','user','uploadType']);
                if(request('facility_id')) $query->where('facility_id', request('facility_id'));
                if(request('search')) $query->where('original_filename', 'like', '%'.request('search').'%');
                $uploads = $query->latest()->paginate(15);
                @endphp
                @forelse($uploads as $upload)
                <tr>
                    <td class="px-3 py-2 border text-sm">{{ $upload->original_filename }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->uploadType->name ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->facility->name ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->user->name ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ number_format($upload->file_size / 1024, 2) }} KB</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->effective_start_date }} - {{ $upload->effective_end_date ?? 'Current' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->expires_at ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->uploaded_at ? \Carbon\Carbon::parse($upload->uploaded_at)->format('M d, Y g:i A') : '-' }}</td>
                    <td class="px-3 py-2 border text-sm">
                        <a href="{{ route('admin.facility.uploads.download', ['facility' => $upload->facility_id, 'upload' => $upload]) }}" class="mr-2 text-blue-600 hover:underline">Download</a>
                        <a href="{{ route('admin.facility.uploads.view', ['facility' => $upload->facility_id, 'upload' => $upload]) }}" target="_blank" class="mr-2 text-green-600 hover:underline">View</a>
                        <form action="{{ route('admin.facility.uploads.destroy', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this upload?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-6 text-center text-gray-500">No uploads found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $uploads->withQueryString()->links() }}</div>
    </div>
</div>
@endsection