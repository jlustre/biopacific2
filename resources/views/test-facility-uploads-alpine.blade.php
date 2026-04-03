@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Facility Uploads (Alpine.js Demo)</h1>
    <div class="p-6 mb-6 bg-white rounded shadow">
        <form id="facility-upload-form" x-data="facilityUploadForm()" x-init="init()" method="POST" action="{{ url('/test-facility-uploads-alpine') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
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
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Upload</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function facilityUploadForm() {
    return {
        facilities: [],
        employees: [],
        uploadTypes: [],
        facility_id: '',
        employee_id: '',
        upload_type_id: '',
        init() {
            fetch('/admin/facilities/all')
                .then(res => res.json())
                .then(data => { this.facilities = data; });
            fetch('/admin/upload-types/all')
                .then(res => res.json())
                .then(data => { this.uploadTypes = data; });
        },
        fetchEmployees() {
            if (!this.facility_id) { this.employees = []; return; }
            fetch(`/admin/facility/${this.facility_id}/employees/all`)
                .then(res => res.json())
                .then(data => { this.employees = data; });
        }
    }
}
</script>
@endsection
