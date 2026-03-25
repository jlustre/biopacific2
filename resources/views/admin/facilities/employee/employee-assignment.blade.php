@php use App\Models\BPUnion; @endphp
@php
$latest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
$latestEffdt = $latest->effdt ?? '';
$latestEffseq = $latest->effseq ?? '';
@endphp
<div x-show="tab === 'assignment'" x-data="assignmentForm()" x-init="initAssignment()">
    <div class="flex justify-end items-center mb-4 space-x-4">
        <button type="button" @click="clearAssignment()"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer">
            Add New Assignment
        </button>
        <template x-if="isLatestRecord()">
            <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">Latest Record</span>
        </template>
    </div>
    <form method="POST" action="{{ route('admin.employees.update_assignment', $employee->emp_id) }}">
        @csrf
        @method('PUT')
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Position</label>
                    <select name="job_code_id" x-model="currentAssignment.job_code_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Position</option>
                        @foreach(App\Models\BPPosition::all() as $pos)
                        <option value="{{ $pos->position_id }}">{{ $pos->position_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Facility</label>
                    <select name="facility_id" class="form-select w-full border border-teal-300 rounded-lg px-2 py-1"
                        x-model="currentAssignment.facility_id">
                        <option value="">Select Facility</option>
                        @foreach(App\Models\Facility::all() as $facility)
                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Department</label>
                    <select name="dept_id" x-model="currentAssignment.dept_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Department</option>
                        @foreach(App\Models\BPDepartment::all() as $dept)
                        <option value="{{ $dept->dept_id }}">{{ $dept->dept_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Reports To</label>
                    <select name="reports_to_emp_id" x-model="currentAssignment.reports_to_emp_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Supervisor</option>
                        @foreach(App\Models\BPPosition::where('has_supervisor_role', true)->get() as $supervisor)
                        <option value="{{ $supervisor->position_id }}">{{ $supervisor->position_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Regular/Temp</label>
                    <select name="reg_temp" class="form-select w-full border border-teal-300 rounded-lg px-2 py-1"
                        x-model="currentAssignment.reg_temp">
                        <option value="r">Regular</option>
                        <option value="t">Temporary</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Full/Part Time</label>
                    <select name="full_part_time" class="form-select w-full border border-teal-300 rounded-lg px-2 py-1"
                        x-model="currentAssignment.full_part_time">
                        <option value="ft">Full-time</option>
                        <option value="pt">Part-time</option>
                        <option value="pd">Per Diem</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Bargaining Unit</label>
                    <select name="bargaining_unit_id" x-model="currentAssignment.bargaining_unit_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Bargaining Unit</option>
                        @foreach(App\Models\BPBargainingUnit::all() as $unit)
                        <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Seniority Date</label>
                    <input type="date" name="seniority_date" x-model="currentAssignment.seniority_date"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Union Seniority Date</label>
                    <input type="date" name="union_seniority_dt" x-model="currentAssignment.union_seniority_dt"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Effective Date</label>
                    <input type="date" name="effdt" x-model="currentAssignment.effdt"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Effective Sequence</label>
                    <input type="number" name="effseq" x-model="currentAssignment.effseq" readonly
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100 cursor-not-allowed">
                </div>
                <div class="flex justify-between mt-6 md:col-span-2 lg:col-span-4">
                    <a href="{{ url()->previous() }}"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 cursor-pointer">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">Save
                        Assignment</button>
                </div>
            </div>
        </div>
    </form>
    <!-- Assignment Info Table -->
    @include('admin.facilities.employee.employee-assignment-table')
</div>