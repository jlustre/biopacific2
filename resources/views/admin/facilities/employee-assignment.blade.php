@php use App\Models\BPUnion; @endphp
@php
$latest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
$latestEffdt = $latest->effdt ?? '';
$latestEffseq = $latest->effseq ?? '';
@endphp
<div x-show="tab === 'assignment'" x-data="{
    currentAssignment: {
        facility_id: '', dept_id: '', job_code_id: '', reports_to_emp_id: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''
    },
    latestEffdt: '{{ $latestEffdt }}',
    latestEffseq: '{{ $latestEffseq }}',
    setAssignment(assign) {
        this.currentAssignment = Object.assign({facility_id: '', dept_id: '', job_code_id: '', reports_to_emp_id: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''}, assign);
    },
    clearAssignment() {
        this.currentAssignment = {facility_id: '', dept_id: '', job_code_id: '', reports_to_emp_id: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''};
    },
    isLatestRecord() {
        return this.currentAssignment.effdt == this.latestEffdt && String(this.currentAssignment.effseq) == String(this.latestEffseq);
    }
}" x-init="if (latestEffdt && latestEffseq) { setAssignment({ effdt: latestEffdt, effseq: latestEffseq,
        facility_id: '{{ $latest->facility_id ?? '' }}',
        dept_id: '{{ $latest->dept_id ?? '' }}',
        job_code_id: '{{ $latest->job_code_id ?? '' }}',
        reports_to_emp_id: '{{ $latest->reports_to_emp_id ?? '' }}',
        reg_temp: '{{ $latest->reg_temp ?? 'r' }}',
        full_part_time: '{{ $latest->full_part_time ?? 'ft' }}',
        bargaining_unit_id: '{{ $latest->bargaining_unit_id ?? '' }}',
        seniority_date: '{{ $latest->seniority_date ?? '' }}'
    }); }">
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
                    <label class="block text-sm font-medium mb-2">Effective Date</label>
                    <input type="date" name="effdt" x-model="currentAssignment.effdt"
                        :min="(new Date()).toISOString().split('T')[0]"
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
    <div class="bg-white shadow rounded-lg p-4 mt-8">
        <h2 class="text-lg font-bold mb-4">Assignment History</h2>
        <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Date</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff. Seq</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Facility</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Regular/Temp</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Full/Pt</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']]) as $assign)
                <tr class="border-b">
                    <td class="px-3 py-2 text-sm">{{ $assign->effdt }}</td>
                    <td class="px-3 py-2 text-sm">{{ $assign->effseq }}</td>
                    <td class="px-3 py-2 text-sm">{{ optional($assign->position)->position_title }}</td>
                    <td class="px-3 py-2 text-sm">{{ optional($assign->facility)->name }}</td>
                    <td class="px-3 py-2 text-sm">{{ optional($assign->department)->dept_name }}</td>
                    <td class="px-3 py-2 text-sm">
                        @if($assign->reg_temp === 'r') Regular @elseif($assign->reg_temp === 't') Temporary @else —
                        @endif
                    </td>
                    <td class="px-3 py-2 text-sm">
                        @if($assign->full_part_time === 'ft') Full-time @elseif($assign->full_part_time === 'pt')
                        Part-time @elseif($assign->full_part_time === 'pd') Per Diem @else — @endif
                    </td>
                    <td class="px-3 py-2 text-sm">
                        <a href="#" class="text-blue-600 hover:underline cursor-pointer" @click.prevent="setAssignment({
                            facility_id: '{{ $assign->facility_id }}',
                            dept_id: '{{ $assign->dept_id }}',
                            job_code_id: '{{ $assign->job_code_id }}',
                            reports_to_emp_id: '{{ $assign->reports_to_emp_id }}',
                            reg_temp: '{{ $assign->reg_temp }}',
                            full_part_time: '{{ $assign->full_part_time }}',
                            bargaining_unit_id: '{{ $assign->bargaining_unit_id }}',
                            seniority_date: '{{ $assign->seniority_date }}',
                            effdt: '{{ $assign->effdt }}',
                            effseq: '{{ $assign->effseq }}'
                        })">View/Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>