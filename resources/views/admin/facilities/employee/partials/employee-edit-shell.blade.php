@php
    if (!isset($isAddMode)) {
        $isAddMode = true;
    }
@endphp
@php
$assignmentLatest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
$assignmentLatestForForm = null;
if ($assignmentLatest) {
    $assignmentLatestForForm = [
        'facility_id' => $assignmentLatest->facility_id,
        'dept_id' => $assignmentLatest->dept_id,
        'position_id' => $assignmentLatest->position_id,
        'reports_to' => $assignmentLatest->reports_to,
        'reg_temp' => $assignmentLatest->reg_temp,
        'full_part_time' => $assignmentLatest->full_part_time,
        'hourly_status_id' => $assignmentLatest->hourly_status_id,
        'std_hrs_week' => $assignmentLatest->std_hrs_week,
        'compensation_rate_id' => $assignmentLatest->compensation_rate_id,
        'amount' => $assignmentLatest->amount,
        'effdt' => $assignmentLatest->effdt?->format('Y-m-d') ?? '',
        'effseq' => $assignmentLatest->effseq,
    ];
}
$employeeUnionForJobData = [
    'union_code' => $employee->union_code ?? '',
    'effdt_of_membership' => $employee->effdt_of_membership?->format('Y-m-d') ?? '',
];

$taxLatest = isset($employee->taxData) ? $employee->taxData->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first() : null;
$taxLatestForForm = null;
if ($taxLatest) {
    $taxLatestForForm = [
        'effdt' => $taxLatest->effdt?->format('Y-m-d') ?? '',
        'effseq' => $taxLatest->effseq,
        'fed_tax_data' => $taxLatest->fed_tax_data ?? '',
        'fed_withholding_allowance' => $taxLatest->fed_withholding_allowance,
        'state_tax_data' => $taxLatest->state_tax_data ?? '',
        'state_withholding_allowance1' => $taxLatest->state_withholding_allowance1,
        'resident' => strtoupper((string) ($taxLatest->resident ?? '')),
        'local_withholding_allowance' => $taxLatest->local_withholding_allowance,
        'locality' => $taxLatest->locality ?? '',
        'county' => $taxLatest->county ?? '',
        'addl_withholding_percentage1' => $taxLatest->addl_withholding_percentage1,
        'addl_withholding_amount1' => $taxLatest->addl_withholding_amount1,
        'addl_withholding_percentage2' => $taxLatest->addl_withholding_percentage2,
        'addl_withholding_amount2' => $taxLatest->addl_withholding_amount2,
        'resident_state' => $taxLatest->resident_state ?? 'CA',
    ];
}

$assignmentPositions = \App\Models\Position::query()
    ->with('department:id,name')
    ->orderBy('title')
    ->get(['id', 'title', 'department_id', 'reports_to_position_id', 'supervisor_role'])
    ->map(function ($position) {
        return [
            'id' => $position->id,
            'title' => $position->title,
            'department_id' => $position->department_id,
            'reports_to_position_id' => $position->reports_to_position_id,
            'supervisor_role' => $position->supervisor_role,
            'department_name' => optional($position->department)->name,
        ];
    });

$initialEmployeeTab = session('employeeTab') ?: request('tab');
if ($initialEmployeeTab === 'assignment') {
    $initialEmployeeTab = 'job-data';
}
$employeeTabIds = ['personal', 'address', 'job-data', 'tax-data', 'documents', 'checklist'];
@endphp

@push('head')
<style>
    .employee-main-tabs [data-employee-tab-btn].bg-gray-200:hover {
        background-color: #d1d5db !important;
        color: #111827 !important;
    }

    .employee-main-tabs [data-employee-tab-btn].bg-blue-600:hover {
        background-color: #1d4ed8 !important;
        color: #fff !important;
    }

    html[data-employee-tab]:not(.employee-tabs-ready) [data-employee-tab-panel] { display: none !important; }
    @foreach($employeeTabIds as $tabId)
    html[data-employee-tab="{{ $tabId }}"]:not(.employee-tabs-ready) [data-employee-tab-panel="{{ $tabId }}"] { display: block !important; }
    html[data-employee-tab="{{ $tabId }}"]:not(.employee-tabs-ready) [data-employee-tab-btn="{{ $tabId }}"] { background-color: #2563eb !important; color: #fff !important; }
    @endforeach
</style>
<script>
    (function () {
        function normalizeEmployeeTab(stored) {
            if (!stored) return null;
            return stored === 'assignment' ? 'job-data' : stored;
        }
        function resolveEmployeeTab() {
            var fromServer = @json($initialEmployeeTab);
            var normalized = normalizeEmployeeTab(fromServer);
            if (normalized) return normalized;
            try {
                var stored = sessionStorage.getItem('employeeTab') || localStorage.getItem('employeeTab');
                normalized = normalizeEmployeeTab(stored);
                if (normalized) return normalized;
            } catch (e) {}
            return 'personal';
        }
        window.__employeeInitialTab = resolveEmployeeTab();
        document.documentElement.setAttribute('data-employee-tab', window.__employeeInitialTab);
        if (window.__employeeInitialTab === 'checklist') {
            var checklistFromUrl = new URLSearchParams(window.location.search).get('checklist_tab');
            var checklistTab = checklistFromUrl;
            if (!checklistTab || !/^part[A-G]$/.test(checklistTab)) {
                try {
                    checklistTab = localStorage.getItem('checklistTab') || localStorage.getItem('employeeChecklistActiveTab');
                } catch (e) {
                    checklistTab = null;
                }
            }
            if (checklistTab && /^part[A-G]$/.test(checklistTab)) {
                window.__checklistInitialTab = checklistTab;
            }
        }
    })();
</script>
@endpush

@php
    $isSelfService = $isSelfService ?? false;
    $shellContainerClass = $isSelfService ? 'w-full max-w-6xl mx-auto' : 'container py-8 max-w-5xl mx-auto';
@endphp

<div class="{{ $shellContainerClass }}" x-data="{
        tab: window.__employeeInitialTab || 'personal',
        init() {
            document.documentElement.classList.add('employee-tabs-ready');
            document.documentElement.setAttribute('data-employee-tab', this.tab);
        },
        setTab(newTab) {
            this.tab = newTab;
            sessionStorage.setItem('employeeTab', newTab);
            localStorage.setItem('employeeTab', newTab);
            document.documentElement.setAttribute('data-employee-tab', newTab);
            var url = new URL(window.location.href);
            url.searchParams.set('tab', newTab);
            if (newTab === 'checklist') {
                try {
                    var checklistPart = localStorage.getItem('checklistTab') || localStorage.getItem('employeeChecklistActiveTab');
                    if (checklistPart) {
                        url.searchParams.set('checklist_tab', checklistPart);
                    }
                } catch (e) {}
            } else {
                url.searchParams.delete('checklist_tab');
            }
            window.history.replaceState({}, '', url);
        }
    }">
    
    @php
        $listFacility = $employeesListFacility
            ?? (isset($employee->currentAssignment) ? $employee->currentAssignment->facility : null)
            ?? $facilities->first();
        $listFacilityId = $employeesListFacilityId
            ?? request('facility')
            ?? ($listFacility->id ?? null);
        $listFacilityKey = $listFacility
            ? ($listFacility->slug ?? $listFacility->id)
            : ($facilities->first()->slug ?? $facilities->first()->id ?? 1);
    @endphp

    @if($isSelfService)
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-teal-700">My Employment</p>
            <h1 class="mt-1 text-2xl font-black text-slate-950">Your employee record</h1>
            <p class="mt-2 text-sm text-slate-600">
                Review and update your personal information, documents, and employment checklist.
                @if($listFacility)
                    <span class="font-semibold text-slate-800">{{ $listFacility->name }}</span>
                @endif
            </p>
        </div>
    @else
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ route('admin.facility.employees', ['facility' => $listFacilityKey]) }}{{ $listFacilityId ? '?facility=' . $listFacilityId : '' }}"
                class="inline-flex items-center rounded-md px-4 py-2 text-white"
                style="background: teal; padding: 0.5rem 1rem;">
                &larr; Back to Employee List
            </a>
            <span class="text-lg font-semibold text-gray-700">
                {{ $listFacility ? $listFacility->name : '' }}
            </span>
        </div>
        <h1 class="mb-1 text-center text-2xl font-bold">{{ $isAddMode ? 'Add Employee' : 'View/Edit Employee' }}</h1>
    @endif

    <div class="text-lg font-semibold text-gray-800 mb-4 text-center">
        @if(!$isAddMode)
            {{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{ $employee->middle_name }}@endif / {{ $employee->employee_num }} / {{ $employee->currentAssignment?->position?->title ?? 'No Position' }}
        @endif
    </div>

    @include('livewire.messages')

    @include('admin.facilities.employee.tab-navigation')

    @include('admin.facilities.employee.employee-profile')
    
    @include('admin.facilities.employee.employee-address')
    
    @include('admin.facilities.employee.employee-assignment')

    @include('admin.facilities.employee.employee-tax-data')

    @include('admin.facilities.employee.employee-documents')

    @include('admin.facilities.checklist.employee-checklist')
    
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var partF = document.getElementById('partF');
            var hasChecklistMessages = false;
            if (partF && (
                partF.querySelector('.bg-green-100') ||
                partF.querySelector('.bg-red-100') ||
                partF.querySelector('.border-red-500')
            )) {
                hasChecklistMessages = true;
            }
            if (hasChecklistMessages) {
                localStorage.setItem('employeeTab', 'checklist');
                sessionStorage.setItem('employeeTab', 'checklist');
                document.documentElement.setAttribute('data-employee-tab', 'checklist');
                window.__employeeInitialTab = 'checklist';
                if (partF) partF.classList.remove('hidden');
            }
        });

        const assignmentLatest = @json($assignmentLatestForForm);
        const employeeUnionFields = @json($employeeUnionForJobData);
        const assignmentPositions = @json($assignmentPositions);
        
        function assignmentForm() {
            return {
                positionsById: assignmentPositions.reduce((carry, position) => {
                    carry[String(position.id)] = position;
                    return carry;
                }, {}),
                formatDateInput(value) {
                    if (value == null || value === '') {
                        return '';
                    }
                    const str = String(value);
                    return /^\d{4}-\d{2}-\d{2}/.test(str) ? str.slice(0, 10) : str;
                },
                currentAssignment: {
                    facility_id: assignmentLatest ? assignmentLatest.facility_id : '',
                    dept_id: assignmentLatest ? assignmentLatest.dept_id : '',
                    position_id: assignmentLatest ? assignmentLatest.position_id : '',
                    reports_to: assignmentLatest ? assignmentLatest.reports_to : '',
                    reg_temp: assignmentLatest ? assignmentLatest.reg_temp : 'r',
                    full_part_time: assignmentLatest ? assignmentLatest.full_part_time : 'ft',
                    hourly_status_id: assignmentLatest && assignmentLatest.hourly_status_id != null
                        ? String(assignmentLatest.hourly_status_id) : '',
                    std_hrs_week: assignmentLatest && assignmentLatest.std_hrs_week != null
                        ? String(assignmentLatest.std_hrs_week) : '',
                    compensation_rate_id: assignmentLatest && assignmentLatest.compensation_rate_id != null
                        ? String(assignmentLatest.compensation_rate_id) : '',
                    amount: assignmentLatest && assignmentLatest.amount != null
                        ? String(assignmentLatest.amount) : '',
                    union_code: employeeUnionFields.union_code ?? '',
                    effdt_of_membership: employeeUnionFields.effdt_of_membership ?? '',
                    effdt: assignmentLatest ? assignmentLatest.effdt : '',
                    effseq: assignmentLatest ? assignmentLatest.effseq : ''
                },
                latestEffdt: assignmentLatest ? assignmentLatest.effdt : '',
                latestEffseq: assignmentLatest ? assignmentLatest.effseq : '',
                syncDepartmentFromPosition() {
                    const selectedPosition = this.positionsById[String(this.currentAssignment.position_id || '')];
                    this.currentAssignment.dept_id = selectedPosition && selectedPosition.department_id
                        ? String(selectedPosition.department_id)
                        : '';
                },
                syncReportsToFromPosition() {
                    const selectedPosition = this.positionsById[String(this.currentAssignment.position_id || '')];
                    this.currentAssignment.reports_to = selectedPosition && selectedPosition.reports_to_position_id
                        ? String(selectedPosition.reports_to_position_id)
                        : '';
                },
                handlePositionChange() {
                    this.syncDepartmentFromPosition();
                    this.syncReportsToFromPosition();
                },
                currentDepartmentName() {
                    const selectedPosition = this.positionsById[String(this.currentAssignment.position_id || '')];
                    return selectedPosition && selectedPosition.department_name
                        ? selectedPosition.department_name
                        : '';
                },
                setAssignment(assign) {
                    const unionCode = this.currentAssignment.union_code;
                    const effdtOfMembership = this.currentAssignment.effdt_of_membership;
                    this.currentAssignment = Object.assign({
                        facility_id: '', dept_id: '', position_id: '', reports_to: '', reg_temp: 'r', full_part_time: 'ft',
                        hourly_status_id: '', std_hrs_week: '', compensation_rate_id: '', amount: '',
                        union_code: unionCode, effdt_of_membership: effdtOfMembership, effdt: '', effseq: ''
                    }, assign);
                    ['hourly_status_id', 'compensation_rate_id', 'std_hrs_week', 'amount', 'reports_to', 'position_id', 'dept_id', 'facility_id'].forEach((key) => {
                        if (this.currentAssignment[key] != null && this.currentAssignment[key] !== '') {
                            this.currentAssignment[key] = String(this.currentAssignment[key]);
                        }
                    });
                    this.currentAssignment.effdt_of_membership = this.formatDateInput(this.currentAssignment.effdt_of_membership);
                    this.currentAssignment.effdt = this.formatDateInput(this.currentAssignment.effdt);
                    this.handlePositionChange();
                },
                clearAssignment() {
                    this.currentAssignment = {
                        facility_id: '', dept_id: '', position_id: '', reports_to: '', reg_temp: 'r', full_part_time: 'ft',
                        hourly_status_id: '', std_hrs_week: '', compensation_rate_id: '', amount: '',
                        union_code: employeeUnionFields.union_code ?? '',
                        effdt_of_membership: employeeUnionFields.effdt_of_membership ?? '',
                        effdt: '', effseq: ''
                    };
                },
                willUpdateExistingRecord() {
                    return Boolean(this.latestEffdt) && this.currentAssignment.effdt === this.latestEffdt;
                },
                confirmAssignmentSubmit(event) {
                    const message = this.willUpdateExistingRecord()
                        ? 'This will update the current latest job data record. Do you want to continue?'
                        : 'This will create a new job data record. Do you want to continue?';

                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                },
                isLatestRecord() {
                    return this.currentAssignment.effdt == this.latestEffdt && String(this.currentAssignment.effseq) == String(this.latestEffseq);
                },
                initAssignment() {
                    this.handlePositionChange();
                }
            }
        }

        const taxLatest = @json($taxLatestForForm);
        const isTaxSelfService = @json($isSelfService ?? false);

        function taxForm() {
            return {
                formatDateInput(value) {
                    if (value == null || value === '') {
                        return '';
                    }
                    const str = String(value);
                    return /^\d{4}-\d{2}-\d{2}/.test(str) ? str.slice(0, 10) : str;
                },
                currentTax: {
                    effdt: taxLatest ? taxLatest.effdt : '',
                    effseq: taxLatest ? taxLatest.effseq : '',
                    fed_tax_data: taxLatest && taxLatest.fed_tax_data != null ? String(taxLatest.fed_tax_data) : '',
                    fed_withholding_allowance: taxLatest && taxLatest.fed_withholding_allowance != null ? String(taxLatest.fed_withholding_allowance) : '',
                    state_tax_data: taxLatest && taxLatest.state_tax_data != null ? String(taxLatest.state_tax_data) : '',
                    state_withholding_allowance1: taxLatest && taxLatest.state_withholding_allowance1 != null ? String(taxLatest.state_withholding_allowance1) : '',
                    resident: taxLatest ? taxLatest.resident : '',
                    local_withholding_allowance: taxLatest && taxLatest.local_withholding_allowance != null ? String(taxLatest.local_withholding_allowance) : '',
                    locality: taxLatest ? (taxLatest.locality ?? '') : '',
                    county: taxLatest ? (taxLatest.county ?? '') : '',
                    addl_withholding_percentage1: taxLatest && taxLatest.addl_withholding_percentage1 != null ? String(taxLatest.addl_withholding_percentage1) : '',
                    addl_withholding_amount1: taxLatest && taxLatest.addl_withholding_amount1 != null ? String(taxLatest.addl_withholding_amount1) : '',
                    addl_withholding_percentage2: taxLatest && taxLatest.addl_withholding_percentage2 != null ? String(taxLatest.addl_withholding_percentage2) : '',
                    addl_withholding_amount2: taxLatest && taxLatest.addl_withholding_amount2 != null ? String(taxLatest.addl_withholding_amount2) : '',
                    resident_state: taxLatest ? (taxLatest.resident_state || 'CA') : 'CA',
                },
                latestEffdt: taxLatest ? taxLatest.effdt : '',
                latestEffseq: taxLatest ? taxLatest.effseq : '',
                setTax(record) {
                    this.currentTax = Object.assign({
                        effdt: '', effseq: '', fed_tax_data: '', fed_withholding_allowance: '',
                        state_tax_data: '', state_withholding_allowance1: '', resident: '',
                        local_withholding_allowance: '', locality: '', county: '',
                        addl_withholding_percentage1: '', addl_withholding_amount1: '',
                        addl_withholding_percentage2: '', addl_withholding_amount2: '',
                        resident_state: 'CA',
                    }, record);
                    this.currentTax.effdt = this.formatDateInput(this.currentTax.effdt);
                    ['fed_tax_data', 'state_tax_data', 'resident', 'resident_state'].forEach((key) => {
                        if (this.currentTax[key] != null && this.currentTax[key] !== '') {
                            this.currentTax[key] = String(this.currentTax[key]);
                        }
                    });
                },
                clearTax() {
                    this.currentTax = {
                        effdt: '', effseq: '', fed_tax_data: '', fed_withholding_allowance: '',
                        state_tax_data: '', state_withholding_allowance1: '', resident: '',
                        local_withholding_allowance: '', locality: '', county: '',
                        addl_withholding_percentage1: '', addl_withholding_amount1: '',
                        addl_withholding_percentage2: '', addl_withholding_amount2: '',
                        resident_state: 'CA',
                    };
                },
                willUpdateExistingRecord() {
                    return Boolean(this.latestEffdt) && this.currentTax.effdt === this.latestEffdt;
                },
                confirmTaxSubmit(event) {
                    if (isTaxSelfService
                        && this.currentTax.effseq
                        && this.currentTax.effdt == this.latestEffdt
                        && String(this.currentTax.effseq) == String(this.latestEffseq)) {
                        const confirmed = window.confirm(
                            'You are updating your existing tax data record.\n\n' +
                            'If your withholding information changed for a new effective period, click Cancel and use Add New Tax Data instead.\n\n' +
                            'Continue updating this record?'
                        );
                        if (!confirmed) {
                            event.preventDefault();
                        }
                        return;
                    }

                    const message = this.willUpdateExistingRecord()
                        ? 'This will update the current latest tax data record. Do you want to continue?'
                        : 'This will create a new tax data record. Do you want to continue?';
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                },
                isLatestRecord() {
                    return this.currentTax.effdt == this.latestEffdt && String(this.currentTax.effseq) == String(this.latestEffseq);
                },
                initTax() {},
            };
        }
    </script>

@endpush