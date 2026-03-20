@extends('layouts.dashboard')

@section('content')
<div class="container py-8 max-w-4xl mx-auto" x-data="{
        tab: localStorage.getItem('employeeTab') || 'personal',
        setTab(newTab) {
            this.tab = newTab;
            localStorage.setItem('employeeTab', newTab);
        }
    }">
    <div class="mb-4 flex justify-between items-center">
        <a href="{{ route('admin.facility.employees', ['facility' => $employee->currentAssignment->facility->slug ?? $employee->currentAssignment->facility_id]) }}@if(request('facility'))?facility={{ request('facility') }}@endif"
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
            if(!$selectedFacility && isset($employee->currentAssignment->facility)) {
            $selectedFacility = $employee->currentAssignment->facility;
            }
            @endphp
            {{ $selectedFacility ? $selectedFacility->name : '' }}
        </span>
    </div>
    <h1 class="text-2xl font-bold mb-1 text-center">View/Edit Employee</h1>
    <div class="text-lg font-semibold text-gray-800 mb-4 text-center">
        {{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{
        $employee->middle_name }}@endif / {{ $employee->emp_id }}
    </div>

    @include('livewire.messages')

    <!-- Tab Navigation -->
    <div class="flex space-x-2 my-4 border-b border-teal-500">
        <button type="button" @click="setTab('checklist')"
            :class="tab === 'checklist' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Checklist</button>
        <button type="button" @click="setTab('personal')"
            :class="tab === 'personal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Personal</button>
        <button type="button" @click="setTab('address')"
            :class="tab === 'address' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Address</button>
        <button type="button" @click="setTab('assignment')"
            :class="tab === 'assignment' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Assignment</button>
    </div>

    @include('admin.facilities.checklist.employee-checklist')

    @include('admin.facilities.employee.employee-profile')

    @include('admin.facilities.employee.employee-address')

    @include('admin.facilities.employee.employee-assignment')

</div>
@endsection

@php
$assignmentLatest = $employee->assignments->sortBy([['effdt', 'desc'], ['effseq', 'desc']])->first();
if ($assignmentLatest) {
$assignmentLatest->bargaining_unit_id = $assignmentLatest->bargaining_unit_id ?? '';
}
@endphp

<script>
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