@extends('layouts.dashboard')

@section('content')
<div class="container py-8 max-w-4xl mx-auto" x-data="{
    tab: localStorage.getItem('employeeTab') || 'personal',
    setTab(newTab) {
        this.tab = newTab;
        localStorage.setItem('employeeTab', newTab);
    }
}">
    <div class="mb-4">
        <a href="{{ route('admin.facility.employees', ['facility' => $employee->currentAssignment->facility->slug ?? $employee->currentAssignment->facility_id]) }}"
            class="inline-flex items-center px-4 py-2 bg-teal-300 text-gray-700 rounded hover:bg-teal-400">
            &larr; Back to Employee List
        </a>
    </div>
    <h1 class="text-2xl font-bold mb-1 text-center">Edit Employee</h1>
    <div class="text-lg font-semibold text-gray-800 mb-4 text-center">
        {{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{
        $employee->middle_name }}@endif
    </div>
    @include('livewire.messages')

    <!-- Tab Navigation -->
    <div class="flex space-x-2 my-4 border-b border-teal-500">
        <button type="button" @click="setTab('personal')"
            :class="tab === 'personal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Personal</button>
        <button type="button" @click="setTab('address')"
            :class="tab === 'address' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Address</button>
        <button type="button" @click="setTab('assignment')"
            :class="tab === 'assignment' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Assignment</button>
        <button type="button" @click="setTab('checklist')"
            :class="tab === 'checklist' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded">Checklist</button>
        <!-- Add more tabs as needed -->
    </div>

    @include('admin.facilities.employee-profile')

    @include('admin.facilities.employee-address')

    @include('admin.facilities.employee-assignment')

    @include('admin.facilities.employee-checklist')

</div>
@endsection