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
} elseif (! $selectedFacility && ($sessionFacilityId = \App\Support\SelectedFacility::id())) {
    $selectedFacility = $facilities->firstWhere('id', $sessionFacilityId);
} elseif (! $selectedFacility && isset($facilities) && count($facilities)) {
    $selectedFacility = $facilities[0];
}
$employeesFacilityQuery = ! empty($facilityFilterId) ? '?facility=' . $facilityFilterId : '';
$employeeImportPresetOptions = ($employeeImportPresets ?? collect())->map(fn ($preset) => [
    'id' => $preset->id,
    'name' => $preset->name,
    'isGlobal' => $preset->isGlobal(),
    'facilityId' => (int) $preset->facility_id,
    'mappingsCount' => $preset->mappingsCount(),
    'mappings' => $preset->mappings ?? [],
    'primaryWorksheet' => $preset->mappings[0]['worksheet'] ?? '',
    'runImportUrl' => route('admin.facility.mapping-presets.run-import', $preset->id),
    'validateUrl' => route('admin.facility.mapping-presets.validate', $preset->id),
    'defaultFacilityId' => $importTargetFacilityId ?? null,
])->values();
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
        <div class="flex w-full flex-col gap-2 px-4 sm:w-auto sm:flex-row sm:px-0">
            @if($canImportEmployees ?? false)
                <button type="button"
                    id="openEmployeeImportPresetChooser"
                    @disabled($employeeImportPresetOptions->isEmpty())
                    title="{{ $employeeImportPresetOptions->isEmpty() ? 'No usable import presets are available' : 'Import employees using an existing preset' }}"
                    class="rounded-md block w-full sm:w-auto px-4 py-3 sm:py-2 bg-teal-600 text-white hover:bg-teal-700 transition text-center disabled:cursor-not-allowed disabled:opacity-50">
                    <span class="font-semibold text-base"><i class="fas fa-file-import mr-1"></i> Import Employees</span>
                </button>
            @endif
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
                        class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1">
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
                        class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1">
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
                        {{ !empty($isDonDepartmentScoped) ? 'disabled' : '' }}>
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
                    class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1">
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
                    placeholder="Employee Name or Number...">
            </div>
            <div class="w-full sm:w-auto px-0 sm:px-0">
                <label for="per_page" class="block text-sm font-medium">Page Size</label>
                <select name="per_page" id="per_page"
                    class="form-select w-full sm:w-auto border border-teal-300 bg-teal-50 focus:border-teal-500 focus:bg-white transition rounded-lg px-4 py-3 sm:px-2 sm:py-1">
                    @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" @if((request('per_page', $perPage ?? 10)==$size)) selected @endif>{{
                        $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto px-0 sm:px-0">
                <span class="block text-sm font-medium text-transparent select-none" aria-hidden="true">&nbsp;</span>
                <button type="submit"
                    class="w-full sm:w-auto rounded-lg bg-teal-600 px-4 py-3 sm:py-2 text-sm font-semibold text-white hover:bg-teal-700 transition">
                    Apply Filters
                </button>
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
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Actions</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">EMP_NUM</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Name</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Email</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Original Hire Date</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Position</th>
                        <th class="px-2 sm:px-4 py-2 text-left font-medium text-gray-500 uppercase whitespace-nowrap">Department</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr class="sm:text-sm">
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
                                @elseif(($canGenerateRegistrationCodes ?? false) && filled($employee->email))
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
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->employee_num ?? '-' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->formalName() }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ filled($employee->email) ? $employee->email : '—' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->original_hire_dt?->format('m/d/Y') ?? '—' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->current_position?->title ?? '-' }}</td>
                        <td class="px-2 sm:px-4 py-2 whitespace-nowrap">{{ $employee->current_department?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">No employees found.</td>
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

@if(($canImportEmployees ?? false) && $employeeImportPresetOptions->isNotEmpty())
    <div id="employeeImportPresetChooser" class="fixed inset-0 z-40 hidden" role="dialog" aria-modal="true" aria-labelledby="employeeImportPresetChooserTitle">
        <div class="absolute inset-0 bg-slate-900/50" data-employee-import-chooser-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 id="employeeImportPresetChooserTitle" class="text-lg font-bold text-slate-900">Import employees</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Choose an existing mapping preset, then upload the employee workbook.
                        </p>
                    </div>
                    <button type="button" data-employee-import-chooser-close
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100"
                            aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-6 py-5">
                    @if(!empty($importTargetFacilityId))
                        <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                            Import target: <strong>{{ $facilities->firstWhere('id', $importTargetFacilityId)?->name }}</strong>
                        </div>
                    @endif
                    <label for="employeeImportPresetSelect" class="mb-1 block text-sm font-semibold text-slate-700">Import preset</label>
                    <select id="employeeImportPresetSelect"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                        @foreach($employeeImportPresetOptions as $presetOption)
                            <option value="{{ $presetOption['id'] }}">
                                {{ $presetOption['name'] }}{{ $presetOption['isGlobal'] ? ' (All facilities)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                    <button type="button" data-employee-import-chooser-close
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" id="continueEmployeeImport"
                            class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.import-mapping-presets.partials.import-modal', ['canImport' => true])

    @push('scripts')
    <script>
    (function () {
        const chooser = document.getElementById('employeeImportPresetChooser');
        const openButton = document.getElementById('openEmployeeImportPresetChooser');
        const continueButton = document.getElementById('continueEmployeeImport');
        const presetSelect = document.getElementById('employeeImportPresetSelect');
        const presets = @json($employeeImportPresetOptions);

        if (!chooser || !openButton || !continueButton || !presetSelect) return;

        const closeChooser = () => {
            chooser.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        openButton.addEventListener('click', () => {
            chooser.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });

        chooser.querySelectorAll('[data-employee-import-chooser-close]').forEach((element) => {
            element.addEventListener('click', closeChooser);
        });

        continueButton.addEventListener('click', () => {
            const preset = presets.find((item) => String(item.id) === String(presetSelect.value));
            if (!preset || typeof window.openPresetImportModal !== 'function') return;

            closeChooser();
            window.openPresetImportModal(preset);
        });
    })();
    </script>
    @endpush
@endif
@endsection