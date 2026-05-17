@php
    $partFAreasReadOnly = !empty($partFAssessmentLocked) || !empty($evaluatorActionsDisabled);
    $partFEmployeeCanComment = auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id;
@endphp

<div class="space-y-3">
    <div>
        <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Areas Requiring Further Development</label>
        <div class="mt-1 text-xs italic text-slate-600">Describe the areas in the employee’s performance that need to be further developed.</div>
        <textarea name="areas_for_development" class="mt-1 min-h-[88px] w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-500" rows="4" @readonly($partFAreasReadOnly)>{{ old('areas_for_development', $areasForDevelopment ?? '') }}</textarea>
    </div>

    <div>
        <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Development Plans</label>
        <div class="mt-1 text-xs italic text-slate-600">Indicate plans to develop or improve the employee’s performance or potential.</div>
        <textarea name="development_plans" class="mt-1 min-h-[88px] w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-500" rows="4" @readonly($partFAreasReadOnly)>{{ old('development_plans', $developmentPlans ?? '') }}</textarea>
    </div>

    <div>
        <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee Comments</label>
        <div class="mt-1 text-xs italic text-slate-600">Optional comments from the employee for this assessment period.</div>
        <textarea name="employee_comments" class="mt-1 min-h-[88px] w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-500" rows="4" @readonly($partFAreasReadOnly || !$partFEmployeeCanComment)>{{ old('employee_comments', $employeeComments ?? '') }}</textarea>
    </div>
</div>