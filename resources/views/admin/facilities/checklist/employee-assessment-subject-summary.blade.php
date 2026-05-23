@php
    $managerId = $managerId ?? 'default';
    $assessmentAssignment = $employee->currentAssignment;
    $assessmentEmployeeName = trim($employee->last_name . ', ' . $employee->first_name . ($employee->middle_name ? ' ' . $employee->middle_name : ''));
    $assessmentPosition = $assessmentAssignment?->position?->title ?? 'No Position Assigned';
    $assessmentDepartment = $assessmentAssignment?->department?->dept_name ?? $assessmentAssignment?->department?->name ?? 'N/A';
    $assessmentReportsTo = $assessmentAssignment?->reportsToPositionTitle() ?: '—';
    $assessmentReviewer = !empty($reviewDate)
        ? ($reviewerName ?? (auth()->user()->name ?? ''))
        : (auth()->user()->name ?? ($reviewerName ?? ''));
    $assessmentFacilityName = $assessmentAssignment?->facility?->name ?? '';
    $assessmentFacilityId = $assessmentAssignment?->facility?->id ?? '';
@endphp

<div class="assessment-subject-summary h-full rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm" data-manager-id="{{ $managerId }}">
    <div class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Facility Name</div>
            <div class="truncate text-sm text-slate-900" title="{{ $assessmentFacilityName }}">{{ $assessmentFacilityName }}</div>
            <input type="hidden" class="js-assessment-facility-name" value="{{ $assessmentFacilityName }}">
            <input type="hidden" class="js-assessment-facility-id" value="{{ $assessmentFacilityId }}">
        </div>
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Department</div>
            <div class="truncate text-sm text-slate-900" title="{{ $assessmentDepartment }}">{{ $assessmentDepartment }}</div>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Employee</div>
            <div class="truncate text-sm font-semibold text-slate-900" title="{{ $assessmentEmployeeName }}">{{ $assessmentEmployeeName }}</div>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reports To</div>
            <div class="truncate text-sm text-slate-900" title="{{ $assessmentReportsTo }}">{{ $assessmentReportsTo }}</div>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Position</div>
            <div class="truncate text-sm text-slate-900" title="{{ $assessmentPosition }}">{{ $assessmentPosition }}</div>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reviewer</div>
            <div class="truncate text-sm text-slate-900" title="{{ $assessmentReviewer }}">{{ $assessmentReviewer }}</div>
        </div>
    </div>
</div>
