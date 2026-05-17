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
    <div class="grid gap-2">
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Facility Name</div>
                <div class="text-sm text-slate-900">{{ $assessmentFacilityName }}</div>
                <input type="hidden" class="js-assessment-facility-name" value="{{ $assessmentFacilityName }}">
                <input type="hidden" class="js-assessment-facility-id" value="{{ $assessmentFacilityId }}">
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Department</div>
                <div class="text-sm text-slate-900">{{ $assessmentDepartment }}</div>
            </div>
        </div>
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Employee</div>
                <div class="text-sm font-semibold text-slate-900">{{ $assessmentEmployeeName }}</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reports To</div>
                <div class="text-sm text-slate-900">{{ $assessmentReportsTo }}</div>
            </div>
        </div>
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Position</div>
                <div class="text-sm text-slate-900">{{ $assessmentPosition }}</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reviewer</div>
                <div class="text-sm text-slate-900">{{ $assessmentReviewer }}</div>
            </div>
        </div>
    </div>
</div>