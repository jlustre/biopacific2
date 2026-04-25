                                        @if(isset($editUpload) && $editUpload)
                                        <div class="col-span-1">
                                            <label class="block mb-1 text-xs font-semibold">Uploaded By</label>
                                            <input type="text" readonly class="form-input w-full bg-gray-100 border-teal-300 rounded border-1" value="{{ $editUpload->user ? $editUpload->user->name : '-' }}">
                                        </div>
                                        @endif
                    @if(isset($editUpload) && $editUpload)
                    <div class="col-span-1">
                        <label class="block mb-1 text-xs font-semibold">Uploaded</label>
                        <input type="text" readonly class="form-input w-full bg-gray-100 border-teal-300 rounded border-1" value="{{ $editUpload->uploaded_at ? \Carbon\Carbon::parse($editUpload->uploaded_at)->format('Y-m-d g:i A') : '-' }}">
                    </div>
                    @endif

@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <div class="mb-4">
        <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">
            &larr; Back to Facility HR Dashboard
        </a>
    </div>
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
        <div id="facility-upload-section" class="p-6 mb-6 bg-white rounded shadow" tabindex="-1">
            <h2 class="text-xl font-bold mb-6">
                Facility: <span class="text-teal-600">{{ session('facility_name') ?? ($facility->name ?? 'Unknown Facility') }}</span>
            </h2>
            <form id="facility-upload-form" method="POST" action="{{ isset($editUpload) && $editUpload ? route('admin.facility.uploads.update', ['facility' => $facility->id, 'upload' => $editUpload->id]) : route('admin.facility.uploads.store', ['facility' => $facility->id]) }}" enctype="multipart/form-data">
                <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                @csrf
                @if(isset($editUpload) && $editUpload)
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"
                    x-data="{
                        requiresExpiry: false,
                        updateExpiry() {
                            const select = $refs.uploadType;
                            if (!select) { this.requiresExpiry = false; return; }
                            const selected = select.options[select.selectedIndex];
                            this.requiresExpiry = !!(selected && selected.dataset.requiresExpiry === '1');
                        }
                    }"
                    x-init="updateExpiry()"
                >
                    <div>
                        <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
                        <select name="upload_type_id" x-ref="uploadType" @change="updateExpiry()" class="form-select w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600" required>
                            <option value="" hidden selected>Select Type</option>
                            @foreach($uploadTypes as $type)
                                <option value="{{ $type->id }}" data-requires-expiry="{{ $type->requires_expiry ? '1' : '0' }}" @if((isset($editUpload) && $editUpload && $editUpload->upload_type_id == $type->id) || old('upload_type_id') == $type->id) selected @endif>
                                    {{ $type->name }}
                                    @if($type->requires_expiry)
                                        <span style="color: #dc2626; font-weight: bold;">**</span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="requiresExpiry" x-cloak>
                        <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                        <input type="date" name="effective_start_date" value="{{ isset($editUpload) && $editUpload ? $editUpload->effective_start_date : old('effective_start_date') }}" class="px-2 py-1 border-teal-300 rounded border-1 bg-teal-50 focus:border-teal-600 form-input w-full">
                    </div>
                    <div x-show="requiresExpiry" x-cloak>
                        <label class="block mb-1 text-xs font-semibold">Expires At</label>
                        <input type="date" name="expires_at" value="{{ isset($editUpload) && $editUpload ? $editUpload->expires_at : old('expires_at') }}" class="px-2 py-1 border-teal-300 rounded border-1 bg-teal-50 focus:border-teal-600 form-input w-full">
                    </div>
                    <div class="flex md:flex-row flex-col gap-4 col-span-2 ">
                        <div class="flex-1">
                            <label class="block mb-1 text-xs font-semibold">Employee <span class="text-red-600">*</span></label>
                            <select name="employee_num" class="form-select w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee['employee_num'] }}"
                                        @if(isset($editUpload) && $editUpload && $editUpload->employee_num == $employee['employee_num'])
                                            selected
                                        @elseif(!isset($editUpload) && old('employee_num') !== null && old('employee_num') !== '' && old('employee_num') == $employee['employee_num'])
                                            selected
                                        @endif
                                    >{{ $employee['last_name'] }}, {{ $employee['first_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <div class="flex items-center gap-2">
                                <label class="block mb-1 text-xs font-semibold">File <span class="text-red-600">*</span></label>
                                @if(isset($editUpload) && $editUpload)
                                    <input type="checkbox" id="reupload" name="reupload" value="1" onchange="document.getElementById('file-input').disabled = !this.checked;">
                                    <label for="reupload" class="ml-0 text-xs text-red-600">Check this to re-upload</label>
                                @endif
                            </div>
                            <input type="file" id="file-input" name="file" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600" @if(isset($editUpload) && $editUpload) disabled @endif>
                        </div>
                    </div>

                    <!-- Date fields now handled above for conditional display -->
                    <div class="col-span-3 flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <label class="block mb-1 text-xs font-semibold">Comments</label>
                            <textarea name="comments" rows="2" class="px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full min-w-[220px] resize-y">{{ isset($editUpload) && $editUpload ? $editUpload->comments : old('comments') }}</textarea>
                        </div>
                        <div class="flex-shrink-0">
                            <button type="submit" class=" -mt-2 px-2 py-1 bg-teal-600 text-white rounded hover:bg-teal-700">{{ isset($editUpload) && $editUpload ? 'Update' : 'Upload' }}</button>
                            @if(isset($editUpload) && $editUpload)
                                <a href="{{ route('admin.facility.uploads.index', ['facility' => $facility->id]) }}" class="ml-2 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-3 mt-2 text-sm">
                        <span style="color: #dc2626; font-weight: bold;">**</span>
                        <span class="text-xs text-gray-700 align-middle ml-1">Requires Expiry Date Tracking</span>
                    </div>
                </div>
            </form>

        </div>
    <!-- List of existing uploads -->
    @include('admin.facilities.partials.upload-table')

        <script>
        // Duplicate check before upload submit
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare uploads array for duplicate check
            window.uploadsForDuplicateCheck = [
                @foreach(App\Models\Upload::where('facility_id', $facility->id)->get() as $u)
                {
                    id: {{ $u->id }},
                    upload_type_id: {{ $u->upload_type_id }},
                    employee_num: @json($u->employee_num),
                    expires_at: @json($u->expires_at)
                },
                @endforeach
            ];

            var form = document.getElementById('facility-upload-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var isEdit = form.querySelector('[name="_method"][value="PUT"]') !== null;
                    var uploadType = form.querySelector('[name="upload_type_id"]').value;
                    var employeeNum = form.querySelector('[name="employee_num"]').value;
                    var expiresAt = form.querySelector('[name="expires_at"]') ? form.querySelector('[name="expires_at"]').value : '';
                    var editId = isEdit ? {{ isset($editUpload) && $editUpload ? $editUpload->id : 'null' }} : null;

                    // Only check if not editing
                    if (!isEdit) {
                        var found = window.uploadsForDuplicateCheck.find(function(u) {
                            return u.upload_type_id == uploadType &&
                                   u.employee_num == employeeNum &&
                                   (u.expires_at || '') == (expiresAt || '');
                        });
                        if (found) {
                            if (!confirm('A record with the same Upload Type, Employee, and Expires At already exists. Do you want to continue?')) {
                                e.preventDefault();
                                return false;
                            }
                        }
                    }
                });
            }
        });
            // Robust focus management using sessionStorage and vanilla JS
            document.addEventListener('DOMContentLoaded', function() {
                // Focus after filtering

                if (sessionStorage.getItem('focus-upload-table')) {
                    var tableDiv = document.getElementById('upload-table');
                    if (tableDiv) {
                        tableDiv.setAttribute('tabindex', '-1');
                        tableDiv.focus({preventScroll: false});
                        // Fallback for browsers that don't focus non-interactive elements
                        setTimeout(function() { tableDiv.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 10);
                    }
                    sessionStorage.removeItem('focus-upload-table');
                }
                // Focus after edit
                if (sessionStorage.getItem('focus-upload-section')) {
                    var uploadSection = document.getElementById('facility-upload-section');
                    if (uploadSection) {
                        uploadSection.setAttribute('tabindex', '-1');
                        uploadSection.focus({preventScroll: false});
                        setTimeout(function() { uploadSection.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 10);
                    }
                    sessionStorage.removeItem('focus-upload-section');
                }

                // Set flag before filtering
                var form = document.getElementById('upload-table-filter-form');
                if (form) {
                    form.addEventListener('submit', function() {
                        sessionStorage.setItem('focus-upload-table', '1');
                    });
                }
                // Set flag before edit
                var editLinks = document.querySelectorAll('a[title="Edit"]');
                editLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('focus-upload-section', '1');
                    });
                });
            });
        </script>
</div>



@endsection