@php
    $instructionsVariant = $instructionsVariant ?? 'screen';
@endphp

@if($instructionsVariant === 'pdf')
<div class="section part-f-instructions">
    <h3 class="part-f-instructions-title">INSTRUCTIONS ON COMPLETING THIS FORM</h3>

    <p class="part-f-instructions-heading">PURPOSE OF THIS FORM</p>
    <p class="part-f-instructions-body">
        The performance appraisal form is designed to communicate behaviors that model Company's performance expectations.
        This appraisal should reflect overall performance of the employee considering such factors as knowledge, skills, and abilities,
        but primarily on whether the employee&rsquo;s performance produced the desired results. An explanation will be required in the
        comments section where the rating either exceeds or is below the expectation level.
    </p>

    <p class="part-f-instructions-heading">WHEN TO USE THIS FORM</p>
    <p class="part-f-instructions-body">
        The Employee Appraisal should be used for both exempt and non-exempt employees. This performance appraisal form is to be
        completed in conjunction with salary reviews, promotions, transfers, and may also be completed on terminations, or otherwise
        when considered desirable.
    </p>

    <hr class="part-f-instructions-divider">

    <p class="part-f-instructions-heading">HOW TO USE THIS FORM</p>
    <p class="part-f-instructions-body">
        The immediate supervisor will complete all sections and discuss with employee. Employees should be asked to sign the appraisal,
        acknowledging that he/she has participated in the review. Place one copy of the performance appraisal in the employee&rsquo;s file.
        Provide a copy to the employee.
    </p>
</div>
@else
<div
    class="mb-4 overflow-hidden rounded-md border border-slate-400 bg-white text-[11px] leading-relaxed text-slate-800 shadow-sm"
    x-data="{ instructionsOpen: true }"
>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-4 py-2">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Instructions on Completing This Form</h3>
        <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-md border border-slate-400 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-slate-100"
            x-on:click="instructionsOpen = !instructionsOpen"
            x-bind:aria-expanded="instructionsOpen"
            aria-controls="partFInstructionsPanel"
        >
            <span x-text="instructionsOpen ? 'Collapse' : 'Expand'"></span>
            <span class="text-[9px]" aria-hidden="true" x-text="instructionsOpen ? '▲' : '▼'"></span>
        </button>
    </div>

    <div
        id="partFInstructionsPanel"
        class="space-y-3 px-4 py-3"
        x-show="instructionsOpen"
        x-collapse
    >
        <div>
            <h4 class="mb-1 font-bold uppercase text-slate-900">Purpose of This Form</h4>
            <p>
                The performance appraisal form is designed to communicate behaviors that model Company's performance expectations.
                This appraisal should reflect overall performance of the employee considering such factors as knowledge, skills, and abilities,
                but primarily on whether the employee&rsquo;s performance produced the desired results. An explanation will be required in the
                comments section where the rating either exceeds or is below the expectation level.
            </p>
        </div>

        <div>
            <h4 class="mb-1 font-bold uppercase text-slate-900">When to Use This Form</h4>
            <p>
                The Employee Appraisal should be used for both exempt and non-exempt employees. This performance appraisal form is to be
                completed in conjunction with salary reviews, promotions, transfers, and may also be completed on terminations, or otherwise
                when considered desirable.
            </p>
        </div>

        <hr class="border-slate-300">

        <div>
            <h4 class="mb-1 font-bold uppercase text-slate-900">How to Use This Form</h4>
            <p>
                The immediate supervisor will complete all sections and discuss with employee. Employees should be asked to sign the appraisal,
                acknowledging that he/she has participated in the review. Place one copy of the performance appraisal in the employee&rsquo;s file.
                Provide a copy to the employee.
            </p>
        </div>
    </div>
</div>
@endif
