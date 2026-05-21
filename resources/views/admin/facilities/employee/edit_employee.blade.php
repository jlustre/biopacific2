@extends('layouts.dashboard')

@section('content')
@php
    if (!isset($isAddMode)) {
        $isAddMode = true;
    }
@endphp
@php
$assignmentLatest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
if ($assignmentLatest) {
$assignmentLatest->bargaining_unit_id = $assignmentLatest->bargaining_unit_id ?? '';
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
@endphp

<div class="container py-8 max-w-4xl mx-auto" x-data="{
        tab: (@js($initialEmployeeTab) || sessionStorage.getItem('employeeTab') || localStorage.getItem('employeeTab') || 'personal'),
        setTab(newTab) {
            this.tab = newTab;
            sessionStorage.setItem('employeeTab', newTab);
            localStorage.setItem('employeeTab', newTab);
        }
    }">
    
    <div class="mb-4 flex justify-between items-center">
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
        <a href="{{ route('admin.facility.employees', ['facility' => $listFacilityKey]) }}{{ $listFacilityId ? '?facility=' . $listFacilityId : '' }}"
            class="inline-flex items-center px-4 py-2 bg-teal-400 text-white hover:bg-teal-500"
            style="background: teal; hover:bg-teal-300; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem;">
            &larr; Back to Employee List
        </a>
        <span class="text-lg font-semibold text-gray-700">
            {{ $listFacility ? $listFacility->name : '' }}
        </span>
    </div>
    <h1 class="text-2xl font-bold mb-1 text-center">{{ $isAddMode ? 'Add Employee' : 'View/Edit Employee' }}</h1>

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

    @include('admin.facilities.employee.employee-documents')

    @include('admin.facilities.checklist.employee-checklist')
    
</div>
@endsection

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
                // Show checklist tab and PART F
                if (window.Alpine) {
                    window.Alpine.store('tab', 'checklist');
                }
                localStorage.setItem('employeeTab', 'checklist');
                if (partF) partF.classList.remove('hidden');
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            // If a tab was set in sessionStorage (from a form submit), restore it
            var preferredTab = @json($initialEmployeeTab);
            if (preferredTab) {
                sessionStorage.setItem('employeeTab', preferredTab);
                localStorage.setItem('employeeTab', preferredTab);
            }
            var tab = sessionStorage.getItem('employeeTab');
            if (tab) {
                localStorage.setItem('employeeTab', tab);
            }
        });

        const assignmentLatest = @json($assignmentLatest);
        const assignmentPositions = @json($assignmentPositions);
        
        function assignmentForm() {
            return {
                positionsById: assignmentPositions.reduce((carry, position) => {
                    carry[String(position.id)] = position;
                    return carry;
                }, {}),
                currentAssignment: {
                    facility_id: assignmentLatest ? assignmentLatest.facility_id : '',
                    dept_id: assignmentLatest ? assignmentLatest.dept_id : '',
                    position_id: assignmentLatest ? assignmentLatest.position_id : '',
                    reports_to: assignmentLatest ? assignmentLatest.reports_to : '',
                    reg_temp: assignmentLatest ? assignmentLatest.reg_temp : 'r',
                    full_part_time: assignmentLatest ? assignmentLatest.full_part_time : 'ft',
                    bargaining_unit_id: assignmentLatest && assignmentLatest.bargaining_unit_id != null && assignmentLatest.bargaining_unit_id !== 'null'
                        ? String(assignmentLatest.bargaining_unit_id).trim() : '',
                    seniority_date: assignmentLatest && assignmentLatest.seniority_date ? assignmentLatest.seniority_date : (assignmentLatest && assignmentLatest.union_seniority_dt ? assignmentLatest.union_seniority_dt : ''),
                    union_seniority_dt: assignmentLatest && assignmentLatest.union_seniority_dt ? assignmentLatest.union_seniority_dt : '',
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
                    const selectedPosition = this.positionsById[String(this.currentAssignment.job_code_id || '')];
                    this.currentAssignment.reports_to = selectedPosition && selectedPosition.reports_to_position_id
                        ? String(selectedPosition.reports_to_position_id)
                        : '';
                },
                handlePositionChange() {
                    this.syncDepartmentFromPosition();
                    this.syncReportsToFromPosition();
                },
                currentDepartmentName() {
                    const selectedPosition = this.positionsById[String(this.currentAssignment.job_code_id || '')];
                    return selectedPosition && selectedPosition.department_name
                        ? selectedPosition.department_name
                        : '';
                },
                setAssignment(assign) {
                    this.currentAssignment = Object.assign({facility_id: '', dept_id: '', position_id: '', reports_to: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''}, assign);
                    this.handlePositionChange();
                },
                clearAssignment() {
                    this.currentAssignment = {facility_id: '', dept_id: '', position_id: '', reports_to: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''};
                },
                willUpdateExistingRecord() {
                    return Boolean(this.latestEffdt) && this.currentAssignment.effdt === this.latestEffdt;
                },
                confirmAssignmentSubmit(event) {
                    const message = this.willUpdateExistingRecord()
                        ? 'This will update the current latest assignment record. Do you want to continue?'
                        : 'This will create a new assignment record. Do you want to continue?';

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
    </script>

@endpush