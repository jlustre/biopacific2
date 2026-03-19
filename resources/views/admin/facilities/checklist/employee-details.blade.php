<div>
    <div class="mb-2">
        <span class="font-semibold">Name:</span> {{ $employee->last_name }}, {{ $employee->first_name }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Position:</span> {{ $employee->current_position?->position_title ?? '-' }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Department:</span> {{ $employee->current_department?->dept_name ?? '-' }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Facility:</span> {{ $employee->current_facility?->name ?? '-' }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Union:</span> {{ $employee->current_union_status ? 'Yes' : 'No' }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Date of Birth:</span> {{ $employee->dob ?? '-' }}
    </div>
    <div class="mb-2">
        <span class="font-semibold">Original Hire Date:</span> {{ $employee->original_hire_dt ?? '-' }}
    </div>
    <!-- Add more fields as needed -->
</div>