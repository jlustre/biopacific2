@php
    $hasAssessmentPeriod = ! empty($assessmentPeriodId);
    $firstAccordion = isset($sections[0]) ? ($sections[0]['accordion_key'] ?? null) : null;
@endphp

<div class="mb-4">
    <h3 class="mb-2 font-bold">PERFORMANCE AREAS</h3>
    <p class="mb-2 text-xs italic text-slate-600">
        Assess each item below. Select <strong>E</strong>, <strong>S</strong>, <strong>U</strong>, or <strong>N</strong> for every applicable line; ratings save when selected.
        Use <strong>Save</strong> under each section to store that section&rsquo;s comments and sync all ratings for the period.
    </p>

    @if($evaluatorActionsDisabled)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-950" role="status">
            {{ \App\Support\PreventsSelfAssessment::DEFAULT_MESSAGE }}
        </div>
    @endif

    @if (! $hasAssessmentPeriod)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 shadow-sm">
            <strong>No assessment period selected.</strong> Please create or select an assessment period above to enable
            performance appraisal actions.
        </div>
    @endif

    <div id="partFTableContainer">
        <style>
            .pfa-row-even { background-color: #f1f5f9; }
            .pfa-row-odd { background-color: #e2e8f0; }
            .pfa-row-hover:hover { background-color: #fef9c3 !important; }
        </style>

        @foreach ($sections as $secIdx => $section)
            @php
                $sectionLabel = $section['section'];
                $accordionKey = $section['accordion_key'];
                $docTypeId = $section['doc_type_id'];
            @endphp

            <section class="mb-4 mt-6" wire:key="pfa-section-{{ $secIdx }}-{{ $accordionKey }}">
                <div>
                    <table class="table w-full border border-gray-300">
                        <thead>
                            <tr>
                                <th colspan="5" class="scroll-mt-4 border border-gray-300 bg-blue-100 px-4 py-1 text-left text-base font-bold text-gray-700 outline-none">
                                    <div class="flex min-w-0 items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-2">
                                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-accordion-toggle', [
                                                'accordionKey' => $accordionKey,
                                                'store' => 'partFAccordion',
                                                'toggleClass' => 'pfa-accordion-btn',
                                            ])
                                            <span class="truncate">{{ $sectionLabel }}</span>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody x-show="$store.partFAccordion.openSection === '{{ $accordionKey }}'" x-transition>
                            <tr class="bg-blue-50">
                                <td class="border border-gray-300 pl-2 text-start text-md font-bold text-gray-700">Items</td>
                                <td class="border border-gray-300 px-3 text-center text-md font-semibold text-gray-700">E</td>
                                <td class="border border-gray-300 px-3 text-center text-md font-semibold text-gray-700">S</td>
                                <td class="border border-gray-300 px-3 text-center text-md font-semibold text-gray-700">U</td>
                                <td class="border border-gray-300 px-3 text-center text-md font-semibold text-gray-700">N</td>
                            </tr>
                            @foreach ($section['rows'] as $rIdx => $row)
                                @if (! empty($row['isMainParentItem']))
                                    <tr wire:key="pfa-main-{{ $row['id'] }}"
                                        class="{{ $rIdx % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} transition-colors hover:bg-slate-100"
                                        data-indent-level="{{ $row['indentLevel'] }}"
                                        data-has-child-items="1"
                                    >
                                        <td colspan="5" class="border border-gray-300 px-2 py-1.5 text-sm font-semibold">
                                            <span class="inline-flex items-center gap-2">
                                                <span wire:ignore>
                                                    <button type="button" class="hierarchy-toggle inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse child items">▲</button>
                                                </span>
                                                <span>{{ $row['display'] }}</span>
                                            </span>
                                        </td>
                                    </tr>
                                @elseif (! empty($row['isStructuralParent']))
                                    <tr wire:key="pfa-str-{{ $row['id'] }}" class="bg-blue-50 text-slate-900"
                                        data-indent-level="{{ $row['indentLevel'] }}"
                                        data-has-child-items="1"
                                    >
                                        <td class="border border-gray-300 text-md font-bold text-gray-700" colspan="5" style="padding-left: calc(0.5rem + {{ ($row['indentLevel'] ?? 0) * 20 }}px);">
                                            <span class="inline-flex items-center gap-2">
                                                <span wire:ignore>
                                                    <button type="button" class="hierarchy-toggle inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100" data-expanded="1" aria-label="Collapse child items">▲</button>
                                                </span>
                                                <span>{{ $row['display'] }}</span>
                                            </span>
                                        </td>
                                    </tr>
                                @else
                                    @php
                                        $rid = (int) $row['id'];
                                        $rating = $latestRatings[$rid] ?? '';
                                        $summaryExclude = ! empty($row['hasChildItems']) ? '1' : '0';
                                        $scorable = $this->rowIsRatingRow($row) ? '1' : '0';
                                        $itemCell = trim(collect([$row['label'] ?? '', $row['display'] ?? ''])->filter()->implode(' '));
                                    @endphp
                                    <tr wire:key="pfa-row-{{ $rid }}"
                                        class="pfa-row-{{ $rIdx % 2 === 0 ? 'even' : 'odd' }} pfa-row-hover"
                                        data-indent-level="{{ $row['indentLevel'] }}"
                                        data-has-child-items="{{ ! empty($row['hasChildItems']) ? '1' : '0' }}"
                                        data-summary-exclude="{{ $summaryExclude }}"
                                        data-partf-scorable="{{ $scorable }}"
                                        data-partf-rating="{{ $rating }}"
                                    >
                                        <td class="border border-gray-300 py-0 text-sm" style="padding-left: calc(0.5rem + {{ ($row['indentLevel'] ?? 0) * 20 }}px);">
                                            {{ $itemCell }}
                                        </td>
                                        @foreach (['E', 'S', 'U', 'N'] as $ratingCode)
                                            <td class="border border-gray-300 py-0 text-center">
                                                <input
                                                    type="radio"
                                                    name="partf-response-{{ $rid }}"
                                                    value="{{ $ratingCode }}"
                                                    wire:key="partf-response-{{ $rid }}-{{ $ratingCode }}"
                                                    wire:model.live="latestRatings.{{ $rid }}"
                                                    @disabled(! $hasAssessmentPeriod || $assessmentLocked || $evaluatorActionsDisabled)
                                                    class="h-4 w-4 border-slate-400 text-slate-700 focus:ring-slate-400"
                                                />
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div
                    class="mb-2 rounded-md border border-slate-400 bg-slate-50 px-3 py-2 shadow-sm"
                    wire:ignore
                    x-show="$store.partFAccordion.openSection === '{{ $accordionKey }}'"
                    x-transition
                >
                    <div class="flex flex-col items-center gap-2 md:flex-row">
                        <div class="flex w-full flex-1 flex-col">
                            <label class="mb-1 ml-2 w-full text-[11px] font-semibold text-slate-700 md:mb-0">Comments:</label>
                            <textarea class="section-comment-textarea mt-1 min-h-[40px] w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 placeholder:text-slate-500 md:mt-0"
                                rows="2"
                                data-doc-type-id="{{ $docTypeId }}"
                                data-section-label="{{ $sectionLabel }}"
                                data-emp-id="{{ $employeeNum }}"
                                data-assessment-period-id="{{ $assessmentPeriodId }}"
                                placeholder="{{ $hasAssessmentPeriod ? 'Enter comments for this section...' : 'Create or select an assessment period to enable comments.' }}"
                                @disabled(! $hasAssessmentPeriod || $assessmentLocked)
                            >{{ $docTypeId ? ($sectionComments[$docTypeId] ?? '') : '' }}</textarea>
                        </div>
                        <div class="flex flex-col">
                            <button type="button"
                                class="section-comment-save-btn ml-0 cursor-pointer rounded-md bg-slate-700 px-3 py-1 text-white md:ml-2 disabled:cursor-not-allowed disabled:opacity-50"
                                data-doc-type-id="{{ $docTypeId }}"
                                data-section-label="{{ $sectionLabel }}"
                                data-emp-id="{{ $employeeNum }}"
                                data-assessment-period-id="{{ $assessmentPeriodId }}"
                                @disabled(! $hasAssessmentPeriod || $assessmentLocked || ! $docTypeId)
                            >Save</button>
                            <span class="section-comment-status ml-2 text-xs"></span>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</div>

@script
<script>
    const registerPartFAccordionStore = () => {
        if (!Alpine.store('partFAccordion')) {
            Alpine.store('partFAccordion', { openSection: null });
        }

        if (!Alpine.store('partFAccordion').openSection && @json($firstAccordion)) {
            Alpine.store('partFAccordion').openSection = @json($firstAccordion);
        }
    };

    const initPartFHierarchyToggles = () => {
        if (typeof window.initializeHierarchyToggles === 'function') {
            window.initializeHierarchyToggles(document.getElementById('partFTableContainer'));
        }
    };

    const applyPartFSummaryFromWire = () => {
        const totalField = document.getElementById('partFTotalScore');
        const averageField = document.getElementById('partFAverageScore');
        const overallField = document.getElementById('partFOverallRating');
        const overallValueField = document.getElementById('partFOverallRatingValue');

        if (totalField) {
            totalField.value = String($wire.summaryTotalScore ?? '');
        }
        if (averageField) {
            averageField.value = String($wire.summaryAverageScore ?? '');
        }
        if (overallField) {
            overallField.value = String($wire.summaryOverallRating ?? '');
        }
        if (overallValueField) {
            overallValueField.value = String($wire.summaryOverallRating ?? '');
        }
    };

    const refreshPartFSummary = () => {
        applyPartFSummaryFromWire();
        if (typeof window.updatePartFSummaryScores === 'function') {
            window.updatePartFSummaryScores();
        }
    };

    if (window.Alpine) {
        registerPartFAccordionStore();
        initPartFHierarchyToggles();
    } else {
        document.addEventListener('alpine:init', () => {
            registerPartFAccordionStore();
            initPartFHierarchyToggles();
        });
    }

    $wire.on('partf-summary-updated', refreshPartFSummary);

    if (typeof window.bindPartFRatingSummaryListeners === 'function') {
        window.bindPartFRatingSummaryListeners();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.bindPartFRatingSummaryListeners === 'function') {
                window.bindPartFRatingSummaryListeners();
            }
        }, { once: true });
    }

    refreshPartFSummary();
</script>
@endscript
