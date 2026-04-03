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
@endphp

<div class="container py-8 max-w-4xl mx-auto" x-data="{
        tab: (sessionStorage.getItem('employeeTab') || localStorage.getItem('employeeTab') || 'personal'),
        setTab(newTab) {
            this.tab = newTab;
            sessionStorage.setItem('employeeTab', newTab);
            localStorage.setItem('employeeTab', newTab);
        }
    }">
    
    <div class="mb-4 flex justify-between items-center">
        <a href="{{ route('admin.facility.employees', ['facility' => (isset($employee->currentAssignment) && isset($employee->currentAssignment->facility) ? ($employee->currentAssignment->facility->slug ?? $employee->currentAssignment->facility_id) : ($facilities->first()->slug ?? $facilities->first()->id ?? 1))]) }}@if(request('facility'))?facility={{ request('facility') }}@endif"
            class="inline-flex items-center px-4 py-2 bg-teal-400 text-white hover:bg-teal-500"
            style="background: teal; hover:bg-teal-300; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem;">
            &larr; Back to Employee List
        </a>
        <span class="text-lg font-semibold text-gray-700">
            @php
            $selectedFacility = null;
            if(request('facility')) {
                $selectedFacility = $facilities->firstWhere('id', request('facility'));
            }
            if(!$selectedFacility && isset($employee->currentAssignment) && isset($employee->currentAssignment->facility)) {
                $selectedFacility = $employee->currentAssignment->facility;
            }
            @endphp
            {{ $selectedFacility ? $selectedFacility->name : '' }}
        </span>
    </div>
    <h1 class="text-2xl font-bold mb-1 text-center">{{ $isAddMode ? 'Add Employee' : 'View/Edit Employee' }}</h1>
    <div class="text-lg font-semibold text-gray-800 mb-4 text-center">
        @if(!$isAddMode)
            {{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{
            $employee->middle_name }}@endif / {{ $employee->emp_id }}
        @endif
    </div>

    @include('livewire.messages')

    @include('admin.facilities.employee.tab-navigation')

    @include('admin.facilities.checklist.employee-checklist')
    
    @include('admin.facilities.employee.employee-profile')
    
    @include('admin.facilities.employee.employee-address')
    
    @include('admin.facilities.employee.employee-assignment')

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
            var tab = sessionStorage.getItem('employeeTab');
            if (tab) {
                localStorage.setItem('employeeTab', tab);
            }
        });

        const assignmentLatest = @json($assignmentLatest);
        
        function assignmentForm() {
            return {
                currentAssignment: {
                    facility_id: assignmentLatest ? assignmentLatest.facility_id : '',
                    dept_id: assignmentLatest ? assignmentLatest.dept_id : '',
                    job_code_id: assignmentLatest ? assignmentLatest.job_code_id : '',
                    reports_to_emp_id: assignmentLatest ? assignmentLatest.reports_to_emp_id : '',
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
                setAssignment(assign) {
                    this.currentAssignment = Object.assign({facility_id: '', dept_id: '', job_code_id: '', reports_to_emp_id: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''}, assign);
                },
                clearAssignment() {
                    this.currentAssignment = {facility_id: '', dept_id: '', job_code_id: '', reports_to_emp_id: '', reg_temp: 'r', full_part_time: 'ft', bargaining_unit_id: '', seniority_date: '', effdt: '', effseq: ''};
                },
                isLatestRecord() {
                    return this.currentAssignment.effdt == this.latestEffdt && String(this.currentAssignment.effseq) == String(this.latestEffseq);
                },
                initAssignment() {
                    // Optionally set initial assignment if needed
                }
            }
        }
    </script>

@endpush