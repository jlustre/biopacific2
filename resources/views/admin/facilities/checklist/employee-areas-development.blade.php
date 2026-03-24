<div class="mb-4">
    <h3 class="font-bold mb-2">AREAS FOR DEVELOPMENT</h3>
    <div class="mb-2">
        <label class="font-semibold">Areas Requiring Further Development:</label>
        <div class="italic text-xs text-gray-600 mb-1">Describe the areas in the employee’s performance that need to be
            further developed.</div>
        <textarea name="areas_for_development" class="border rounded w-full min-h-[40px] mt-1 px-2 py-1"
            rows="2">{{ $areasForDevelopment ?? '' }}</textarea>
    </div>
    <div class="mb-2">
        <label class="font-semibold">Development Plans:</label>
        <div class="italic text-xs text-gray-600 mb-1">Indicate plans to develop or improve the employee’s performance
            or potential.</div>
        <textarea name="development_plans" class="border rounded w-full min-h-[40px] mt-1 px-2 py-1"
            rows="3">{{ $developmentPlans ?? '' }}</textarea>
    </div>
    <div class="mb-2">
        <label class="font-semibold">Employee Comments (Optional):</label>
        <textarea name="employee_comments" class="border rounded w-full min-h-[40px] mt-1 px-2 py-1" rows="3"
            @if(!(auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id)) readonly @endif>{{ $employeeComments ?? '' }}</textarea>
    </div>
</div>