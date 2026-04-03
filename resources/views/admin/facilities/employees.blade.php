@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Employees</h1>

    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div class="flex flex-wrap gap-2 items-end w-full md:w-auto">
            <form method="GET" action="" class="flex flex-wrap gap-2 items-end" id="employee-filter-form">
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:justify-between">
                <div>
                <label for="facility" class="block text-sm font-medium">Facility</label>
                <select name="facility" id="facility"
                    class="form-select border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
                    onchange="this.form.submit()">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility')==$facility->id) selected @endif>{{
                        $facility->name }}</option>
                    @endforeach
                </select>
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium">Department</label>
                    <select name="department" id="department"
                        class="form-select border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
                        onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @if(request('department')==$department->dept_id) selected
                            @endif>{{ $department->dept_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-2">
                <a href="{{ route('admin.employees.create') }}"
                class="ml-auto px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition h-10 flex items-center">
                    <span class="font-semibold text-base">+ Add Employee</span>
                </a>
                </div>
            </div>
            <div>
                <label for="position" class="block text-sm font-medium">Position</label>
                <select name="position" id="position"
                    class="form-select border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
                    onchange="this.form.submit()">
                    <option value="">All Positions</option>
                    @foreach($positions as $position)
                    <option value="{{ $position->position_id }}" @if(request('position')==$position->position_id)
                        selected @endif>{{ $position->position_title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="union" class="block text-sm font-medium">Union Status2</label>
                <select name="union" id="union"
                    class="form-select border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
                    onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="union" @if(request('union')=='union' ) selected @endif>Union</option>
                    <option value="non-union" @if(request('union')=='non-union' ) selected @endif>Non-Union</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="form-input border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
                    placeholder="Employee name..." onblur="this.form.submit()"
                    onkeydown="if(event.key==='Enter'){this.form.submit();}">
            </div>
            <div>
                <label for="per_page" class="block text-sm font-medium">Page Size</label>
                <select name="per_page" id="per_page"
                    class="form-select border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-2 py-1"
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

    <div class="overflow-x-auto bg-white rounded shadow">
        <div class="mb-2 text-sm text-gray-700 ml-4 mt-2">
            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} results
        </div>

        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Facility</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Union</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($employees as $employee)
                <tr class="text-sm">
                    <td class="px-4 py-2 whitespace-nowrap">{{ $employee->last_name }}, {{ $employee->first_name }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $employee->current_position?->position_title ?? '-' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $employee->current_department?->dept_name ?? '-' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($employee->current_facility?->name ?? '-', 16, '...') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $employee->current_union_status ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <a href="{{ route('admin.employees.edit', $employee->emp_id) }}" class="text-blue-600 hover:underline">View/Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No employees found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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