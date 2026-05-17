@if (!empty($reviewDate))
    <div id="review-date-warning"
        class="mb-4 flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 shadow-sm"
        style="display:none;">
        <svg class="mr-2 h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
        <span><strong>Warning:</strong> This assessment has already been submitted (Review Date: <strong>{{ $reviewDate
                }}</strong>). Further changes may not be allowed or may require special permission.</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                if (!sessionStorage.getItem('reviewDateWarned')) {
                    var warnDiv = document.getElementById('review-date-warning');
                    if (warnDiv) warnDiv.style.display = 'flex';
                    sessionStorage.setItem('reviewDateWarned', '1');
                }
            });
    </script>
    @endif
    <table class="mb-4 min-w-full overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs">
        <tr>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold" width="20%">Name</td>
            <td class="border border-slate-500 bg-white px-2 py-1.5">{{ $employee->last_name }}, {{ $employee->first_name
                }}@if($employee->middle_name), {{ $employee->middle_name }}@endif</td>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold" width="20%">Review Date</td>
            <td class="border border-slate-500 bg-white px-2 py-1.5">{{ $reviewDate ?? '' }}</td>
        </tr>
        @php
        $assignment = $employee->currentAssignment;
        $position = $assignment && $assignment->position ? $assignment->position->title : '';
        $department = $assignment && $assignment->department ? $assignment->department->dept_name : '';
        if (!empty($reviewDate)) {
        $displayReviewerName = $reviewerName ?? '';
        } else {
        $displayReviewerName = auth()->user()->name ?? ($reviewerName ?? '');
        }
        @endphp
        <tr>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Position</td>
            <td class="border border-slate-500 bg-slate-50 px-2 py-1.5">{{ $position }}</td>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Date Employed</td>
            <td class="border border-slate-500 bg-slate-50 px-2 py-1.5">{{ $employee->original_hire_dt ?? '' }}</td>
        </tr>
        <tr>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Department</td>
            <td class="border border-slate-500 bg-white px-2 py-1.5">{{ $department }}</td>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Review Type</td>
            <td class="border border-slate-500 bg-white px-2 py-1.5">{{ $reviewType ?? '' }}</td>
        </tr>
        <tr>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Reviewer's Name</td>
            <td class="border border-slate-500 bg-slate-50 px-2 py-1.5">{{ $displayReviewerName }}</td>
            <td class="border border-slate-500 bg-slate-100 px-2 py-1.5 font-semibold">Reviewer's Position</td>
            <td class="border border-slate-500 bg-slate-50 px-2 py-1.5">
                {{ $assignment?->reportsToPositionTitle() ?: '—' }}
            </td>
        </tr>
    </table>
    <div class="mb-2 italic text-xs text-slate-600">
        <strong>Instructions on Completing This Form:</strong> The performance appraisal form is designed to
        communicate behaviors that model Company's performance expectations. This appraisal should reflect
        overall performance of the employee considering such factors as knowledge, skills, and abilities,
        but primarily on whether the employee’s performance produced the desired results. An explanation
        will be required in the comments section where the rating either exceeds or is below the expectation
        level.
    </div>
    <div class="mb-2 italic text-xs text-slate-600">
        <strong>When to Use This Form:</strong> The Employee Appraisal should be used for both exempt and
        non-exempt employees. This performance appraisal form is to be completed in conjunction with salary
        reviews, promotions, transfers, and may also be completed on terminations, or otherwise when
        considered desirable.
    </div>
    <div class="mb-2 italic text-xs text-slate-600">
        <strong>How to Use This Form:</strong> The immediate supervisor will complete all sections and
        discuss with employee. Employees should be asked to sign the appraisal, acknowledging that he/she
        has participated in the review. Place one copy of the performance appraisal in the employee’s file.
        Provide a copy to the employee.
    </div>
<div class="mb-4 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-900">Performance Rating Categories</h3>
    <table class="mb-1 min-w-full overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs">
        <thead>
            <tr class="bg-slate-200 text-slate-900">
                <th class="border border-slate-500 px-2 py-1.5 font-semibold tracking-wide">RATING DESCRIPTION</th>
                <th class="border border-slate-500 px-2 py-1.5 font-semibold tracking-wide">CODES</th>
                <th class="border border-slate-500 px-2 py-1.5 font-semibold tracking-wide">RATING VALUE</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white">
                <td class="border border-slate-500 px-2 py-1.5">The employee exceeds the majority of performance expectations.</td>
                <td class="border border-slate-500 px-2 py-1.5">E = Exceeds</td>
                <td class="border border-slate-500 px-2 py-1.5">3</td>
            </tr>
            <tr class="bg-slate-50">
                <td class="border border-slate-500 px-2 py-1.5">The employee meets performance expectations with occasional deviations
                    above and below expectations.</td>
                <td class="border border-slate-500 px-2 py-1.5">M = Meets</td>
                <td class="border border-slate-500 px-2 py-1.5">2</td>
            </tr>
            <tr class="bg-white">
                <td class="border border-slate-500 px-2 py-1.5">The employee has failed to meet one or more of the significant
                    performance expectations.</td>
                <td class="border border-slate-500 px-2 py-1.5">B = Below</td>
                <td class="border border-slate-500 px-2 py-1.5">1</td>
            </tr>
        </tbody>
    </table>
</div>
