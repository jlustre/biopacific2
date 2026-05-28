@php use App\Models\BPUnion; @endphp
@php
$latest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
$latestEffdt = $latest->effdt ?? '';
$latestEffseq = $latest->effseq ?? '';
$assignmentPositions = App\Models\Position::query()
    ->with('department:id,name')
    ->orderBy('title')
    ->get();
$hourlyStatusTypeId = \App\Models\Optionstype::where('name', 'Hourly Status')->value('id');
$compensationRateTypeId = \App\Models\Optionstype::where('name', 'Compensation Rate')->value('id');
$hourlyStatusOptions = $hourlyStatusTypeId
    ? \App\Models\SelectOption::where('type_id', $hourlyStatusTypeId)->where('isActive', 1)->orderBy('sort_order')->get()
    : collect();
$compensationRateOptions = $compensationRateTypeId
    ? \App\Models\SelectOption::where('type_id', $compensationRateTypeId)->where('isActive', 1)->orderBy('sort_order')->get()
    : collect();
$unionCodeOptions = \App\Models\BPBargainingUnit::query()
    ->whereNotNull('union_code')
    ->orderBy('union_code')
    ->pluck('union_code')
    ->unique()
    ->values();
@endphp
<div x-show="tab === 'job-data'" x-cloak data-employee-tab-panel="job-data" x-data="assignmentForm()" x-init="initAssignment()">
    @if(isset($isAddMode) && $isAddMode)
        <div class="p-6 mb-6 bg-white rounded shadow text-gray-600">
            <div class="mb-2 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
                <strong>Notice:</strong> Please complete and save the Personal tab form before continuing with the checklist.
            </div>
            <em>Save the employee record before adding job data.</em>
        </div>
    @else
    @if($isSelfService ?? false)
        @php
            $current = $employee->currentAssignment;
        @endphp
        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
            Job assignment details are managed by HR. Contact your facility administrator if anything looks incorrect.
        </div>
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Facility</p>
                    <p class="mt-1 font-medium text-slate-900">{{ $current?->facility?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Position</p>
                    <p class="mt-1 font-medium text-slate-900">{{ $current?->position?->title ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Department</p>
                    <p class="mt-1 font-medium text-slate-900">{{ $current?->department?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Employment type</p>
                    <p class="mt-1 font-medium text-slate-900">{{ strtoupper((string) ($current?->full_part_time ?? '')) ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Standard hours / week</p>
                    <p class="mt-1 font-medium text-slate-900">{{ $current?->std_hrs_week ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Effective date</p>
                    <p class="mt-1 font-medium text-slate-900">{{ $current?->effdt?->format('Y-m-d') ?? '—' }}</p>
                </div>
            </div>
        </div>
    @else
    <div class="flex justify-end items-center mb-4 space-x-4">
        <button type="button" @click="clearAssignment()"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer">
            Add New Job Data
        </button>
        <template x-if="isLatestRecord()">
            <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">Latest Record</span>
        </template>
    </div>
    <form method="POST" action="{{ route('admin.employees.update_assignment', $employee->id) }}" @submit="confirmAssignmentSubmit($event)">
        @csrf
        @method('PUT')
        @if(auth()->user() && auth()->user()->hasRole('facility-admin'))
            <input type="hidden" name="facility_id" value="{{ auth()->user()->facility_id ?? ($employee->facility_id ?? '') }}">
        @endif
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Position</label>
                    <select name="position_id" x-model="currentAssignment.position_id" @change="handlePositionChange()"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Position</option>
                        @foreach($assignmentPositions as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('rdhr')))
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
                @endif
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Department</label>
                    <input type="hidden" name="dept_id" :value="currentAssignment.dept_id">
                    <input type="text" x-bind:value="currentDepartmentName()"
                        readonly
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 bg-gray-100 cursor-not-allowed text-gray-700"
                        placeholder="Select Position First">
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Reports To</label>
                    <select name="reports_to" x-model="currentAssignment.reports_to"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1">
                        <option value="">Select Supervisor</option>
                        @foreach(App\Models\Position::query()->supervisorRoles()->orderBy('title')->get() as $supervisor)
                            <option value="{{ $supervisor->id }}">{{ $supervisor->title }}</option>
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
                    <label class="block text-sm font-medium mb-2">Hourly Status</label>
                    <select name="hourly_status_id" x-model="currentAssignment.hourly_status_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('hourly_status_id') ? 'border-red-500' : '' }}">
                        <option value="">-- Select --</option>
                        @foreach($hourlyStatusOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('hourly_status_id')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Std. Hrs./Week</label>
                    <input type="number" name="std_hrs_week" min="0" max="168" step="1"
                        x-model="currentAssignment.std_hrs_week"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('std_hrs_week') ? 'border-red-500' : '' }}">
                    @error('std_hrs_week')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Compensation Rate</label>
                    <select name="compensation_rate_id" x-model="currentAssignment.compensation_rate_id"
                        class="form-select w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('compensation_rate_id') ? 'border-red-500' : '' }}">
                        <option value="">-- Select --</option>
                        @foreach($compensationRateOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('compensation_rate_id')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Amount</label>
                    <input type="number" name="amount" min="0" step="0.01"
                        x-model="currentAssignment.amount"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('amount') ? 'border-red-500' : '' }}">
                    @error('amount')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Union Code</label>
                    <input type="text" name="union_code" maxlength="50" list="job-data-union-code-options"
                        x-model="currentAssignment.union_code"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('union_code') ? 'border-red-500' : '' }}">
                    <datalist id="job-data-union-code-options">
                        @foreach($unionCodeOptions as $code)
                            <option value="{{ $code }}"></option>
                        @endforeach
                    </datalist>
                    @error('union_code')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Effective Date of Membership</label>
                    <input type="date" name="effdt_of_membership" x-model="currentAssignment.effdt_of_membership"
                        class="form-input w-full border border-teal-300 rounded-lg px-2 py-1 {{ $errors->has('effdt_of_membership') ? 'border-red-500' : '' }}">
                    @error('effdt_of_membership')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
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
                        Job Data</button>
                </div>
            </div>
        </div>
    </form>
    @endif
    @endif
    <!-- Job Data History Table -->
    @include('admin.facilities.employee.employee-assignment-table')
</div>