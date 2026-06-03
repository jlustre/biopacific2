@extends('layouts.dashboard')

@section('content')
@php
$selectedFacility = $scopedFacility ?? null;
if (! $selectedFacility && ! empty($facilityFilterId)) {
    $selectedFacility = $facilities->firstWhere('id', $facilityFilterId);
}
if (! $selectedFacility && request('facility')) {
    $selectedFacility = $facilities->firstWhere('id', request('facility'));
} elseif (! $selectedFacility && isset($facility)) {
    $selectedFacility = $facility;
} elseif (! $selectedFacility && isset($facilities) && count($facilities)) {
    $selectedFacility = $facilities[0];
}
$employeesFacilityQuery = ! empty($facilityFilterId) ? '?facility=' . $facilityFilterId : '';
@endphp
<div class="px-0 py-0">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-4">
        <div class="w-full sm:w-auto px-4 sm:px-0">
            @if($selectedFacility)
                <a href="{{ route('member.facility.dashboard', ['facility' => $selectedFacility->slug ?? $selectedFacility->id]) }}" class="rounded-md block w-full bg-teal-500 sm:w-auto px-4 py-3 sm:py-2 text-center">
                    &larr; Back to Facility Dashboard
                </a>
            @endif
        </div>
        <div class="w-full sm:w-auto px-4 sm:px-0">
            <a href="{{ route('admin.employees.create') }}"
                class="rounded-md block w-full sm:w-auto px-4 py-3 sm:py-2 bg-green-600 text-white hover:bg-green-700 transition text-center">
                <span class="font-semibold text-base w-full">+ Add Employee</span>
            </a>
        </div>
    </div>
    <h1 class="text-2xl sm:text-3xl font-bold mb-2 text-center">Employees</h1>

    @if(session('success'))
        <div class="mx-2 sm:mx-0 mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mx-2 sm:mx-0 mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mx-2 sm:mx-0 mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    
    @if($selectedFacility && !(auth()->user() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('rdhr'))))
        <div class="mb-4 text-center">
            <span class="inline-block px-4 py-1 bg-teal-100 text-teal-800 rounded font-semibold">
                Facility: {{ $selectedFacility->name }}
            </span>
        </div>
    @endif

    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div class="flex flex-col sm:flex-row flex-wrap gap-2 items-end w-full md:w-auto">
            <form method="GET" action="" class="flex flex-col sm:flex-row flex-wrap gap-2 items-end w-full px-2 sm:px-0" id="employee-filter-form" autocomplete="off">
                <div class="w-full sm:w-auto px-0 sm:px-0">
                    <label for="reports_to" class="block text-sm font-medium">Reports To</label>
                    <select name="reports_to" id="reports_to"
                        class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                        onchange="this.form.submit()">
                        <option value="">All Supervisors</option>
                        @foreach($supervisorPositions as $supervisor)
                        <option value="{{ $supervisor->position_id }}" @if(request('reports_to')==$supervisor->position_id) selected @endif>{{ $supervisor->title }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('rdhr')))
                <div class="w-full sm:w-auto px-0 sm:px-0">
                    <label for="facility" class="block text-sm font-medium">Facility</label>
                    <select name="facility" id="facility"
                        class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                        onchange="this.form.submit()">
                        <option value="">All Facilities</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @if(request('facility')==$facility->id) selected @endif>{{
                            $facility->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="w-full sm:w-auto px-0 sm:px-0">
                    <label for="department" class="block text-sm font-medium">Department</label>
                    @if(!empty($isDonDepartmentScoped) && !empty($selectedDepartmentId))
                    <input type="hidden" name="department" value="{{ $selectedDepartmentId }}">
                    @endif
                    <select name="department" id="department"
                        class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                        {{ !empty($isDonDepartmentScoped) ? 'disabled' : '' }}
                        onchange="this.form.submit()">
                        @if(empty($isDonDepartmentScoped))
                        <option value="">All Departments</option>
                        @endif
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}" @if(($selectedDepartmentId ?? request('department'))==$department->id) selected
                            @endif>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
           
            <div class="w-full sm:w-auto px-0 sm:px-0">
                <label for="position" class="block text-sm font-medium">Position</label>
                <select name="position" id="position"
                    class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                    onchange="this.form.submit()">
                    <option value="">All Positions</option>
                    @foreach($positions as $position)
                    <option value="{{ $position->position_id }}" @if(request('position')==$position->position_id)
                        selected @endif>{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Union Status filter removed -->
            <div class="w-full sm:w-auto px-0 sm:px-0">
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="form-input w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                    placeholder="Employee Name or Number..." onblur="this.form.submit()"
                    onkeydown="if(event.key==='Enter'){this.form.submit();}">
            </div>
            <div class="w-full sm:w-auto px-0 sm:px-0">
                <label for="per_page" class="block text-sm font-medium">Page Size</label>
                <select name="per_page" id="per_page"
                    class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1"
                    onchange="this.form.submit()">
                    @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" @if((request('per_page', $perPage ?? 10)==$size)) selected @endif>{{
                        $size }}</option>
                    @endforeach
                </select>
            </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded shadow pt-2 px-0 sm:px-4">
        <div class="mb-2 text-sm text-gray-700 ml-4 mt-2">
            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} results
        </div>
        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <table class="min-w-full divide-y divide-gray-300 text-xs sm:text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">EMP_NUM</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Name</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Position</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Department</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr class="sm:text-sm">
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->employee_num ?? '-' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->last_name }}, {{ $employee->first_name }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->current_position?->title ?? '-' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->current_department?->name ?? '-' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap text-center">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.employees.edit', $employee->id) }}{{ $employeesFacilityQuery }}" class="text-blue-600 hover:text-blue-800 transition" title="View/Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <title>View/Edit</title>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-1.414.94l-4.243 1.415 1.415-4.243a4 4 0 01.94-1.414z" />
                                    </svg>
                                </a>

                                @php
                                    $hasPortalUser = ($registrationCodeService ?? app(\App\Support\RegistrationCodeService::class))
                                        ->employeeHasPortalUser($employee);
                                    $pendingRegistrationCode = ($activeRegistrationCodes ?? collect())->get($employee->employee_num);
                                @endphp

                                @if($hasPortalUser)
                                    <span class="text-emerald-600" title="Registered portal user">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                @elseif($canGenerateRegistrationCodes ?? false)
                                    <form method="POST" action="{{ route('admin.employees.registration_code.generate', $employee->id) }}" class="inline">
                                        @csrf
                                        @if(!empty($facilityFilterId))
                                            <input type="hidden" name="facility" value="{{ $facilityFilterId }}">
                                        @endif
                                        <button type="submit"
                                            class="{{ $pendingRegistrationCode ? 'text-amber-600 hover:text-amber-800' : 'text-teal-600 hover:text-teal-800' }} transition"
                                            title="{{ $pendingRegistrationCode ? 'Resend registration code (' . $pendingRegistrationCode->code . ')' : 'Generate registration code and email invite' }}">
                                            @if($pendingRegistrationCode)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 my-4 text-center">
            @include('admin.facilities.employee.employee-pagination')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var facilitySelect = document.getElementById('facility');
        var form = document.getElementById('employee-filter-form');
        if (facilitySelect && form) {
            var urlParams = new URLSearchParams(window.location.search);
            var facilityVal = facilitySelect.value;
            if (facilityVal && !urlParams.has('facility')) {
                // Add facility param and submit
                urlParams.set('facility', facilityVal);
                window.location.search = urlParams.toString();
            }
        }
    });
</script>
@endpush