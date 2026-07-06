
<section class="mb-4 mt-6">
    @php
        use App\Support\AssessmentWorkflowStatus;
    @endphp
    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-review-modal')

    @php
        $lnemarSectionMetrics = $this->sectionSummaryMetrics;
    @endphp

    <script>
        const registerPartGAccordionStore = () => {
            if (!Alpine.store('partGAccordion')) {
                Alpine.store('partGAccordion', { openSection: null });
            }

            if (!Alpine.store('partGAccordion').openSection) {
                Alpine.store('partGAccordion').openSection = 'ln-emar';
            }
        };

        if (window.Alpine) {
            registerPartGAccordionStore();
        } else {
            document.addEventListener('alpine:init', () => {
                registerPartGAccordionStore();
            });
        }
    </script>
    <div wire:key="lnemar-section-{{ md5(json_encode($responses)) }}">
        <style>
            .lnemar-row-even { background-color: #f1f5f9; }
            .lnemar-row-odd { background-color: #e2e8f0; }
            .lnemar-row-hover:hover { background-color: #fef9c3 !important; }
            .competency-summary-focus { outline: 2px solid #f59e0b; outline-offset: 3px; }
        </style>

        <div>
            <table class="table w-full border border-gray-300">
                <thead>
                    <tr>
                        <th id="lnemar-section-heading" tabindex="-1" colspan="5" class="scroll-mt-4 bg-blue-100 text-gray-700 font-bold text-base border border-gray-300 px-4 py-1 text-left outline-none">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-accordion-toggle', [
                                        'accordionKey' => 'ln-emar',
                                    ])
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-title-with-status', [
                                        'title' => 'LICENSED NURSE eMAR COMPETENCY',
                                        'sectionItems' => $items,
                                    ])
                                </div>
                                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'ln-emar',
                                    'summaryFormId' => 'lnemar-summary-form',
                                ])
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'ln-emar'" x-transition>
                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-items-column-header')
                    @foreach($items as $index => $item)
                        @if($item['isParent'] ?? false)
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.nested-parent-row', [
                                'item' => $item,
                                'wireKey' => 'lnemar-parent-'.$item['id'],
                                // No showToggle passed, so partial should not render a toggle
                            ])
                        @else
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-scorable-item-row', [
                                'item' => $item,
                                'index' => $index,
                                'wireKeyPrefix' => 'lnemar',
                                'rowClassPrefix' => 'lnemar',
                                'disabled' => $assessmentLocked || $sectionExcluded || ! $assessmentPeriodId,
                            ])
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div id="lnemar-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'ln-emar'" x-transition>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-evaluation-summary-heading', [
                    'title' => 'LICENSED NURSE eMAR COMPETENCY EVALUATION SUMMARY',
                    'accordionKey' => 'ln-emar',
                    'sectionHeadingId' => 'lnemar-section-heading',
                ])
                <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')
                @if(! $sectionExcluded)
                <div class="mb-3">
                    <div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
                        @include('admin.facilities.checklist.partials.part-g-average-legend')
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL ITEMS</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $lnemarSectionMetrics['totalItems'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $lnemarSectionMetrics['checkedOfTotal'] }}</div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL POINTS</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $lnemarSectionMetrics['totalPoints'] }}</div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">AVERAGE</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $lnemarSectionMetrics['average'] }}</div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">OVERALL RATING</div>
                        <div class="text-2xl font-bold text-gray-700">{{ $lnemarSectionMetrics['overallRating'] }}</div>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
                    <textarea
                        wire:model="summaryComments"
                        @disabled($this->summaryFieldsLocked)
                        class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y"
                        placeholder="Enter comments here..."
                    ></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-blue-700 mb-1">EMPLOYEE COMMENTS</label>
                    <textarea
                        wire:model="employeeComments"
                        @disabled($this->summaryFieldsLocked)
                        class="w-full rounded border border-blue-300 bg-blue-50 p-3 text-blue-700 min-h-[100px] resize-y"
                        placeholder="Enter employee comments here..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 mb-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $this->storedReviewerName }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerTitle }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW SIGN DATE</label>
                            <input
                                type="text"
                                readonly
                                class="w-full rounded border border-gray-300 bg-slate-100 p-2"
                                value="{{ $this->displayReviewSignDate }}"
                                placeholder="Recorded automatically when the reviewer signs"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $this->storedEmployeeName }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeTitle }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE SIGN DATE</label>
                            <input
                                type="text"
                                readonly
                                class="w-full rounded border border-gray-300 bg-slate-100 p-2"
                                value="{{ $this->displayEmployeeSignDate }}"
                                placeholder="Recorded automatically when the employee signs"
                            />
                        </div>
                    </div>

                    @if($this->showDraftSubmitActions)
                        <div class="flex flex-col md:flex-row justify-end gap-2 mt-2">
                            <button
                                type="button"
                                wire:click="saveDraft"
                                wire:loading.attr="disabled"
                                class="rounded border-gray-400 bg-white px-6 py-2 font-semibold inline-flex items-center text-amber-900 shadow hover:bg-amber-200 border"
                            >
                                <span wire:loading.remove wire:target="saveDraft">Save as Draft</span>
                                <span wire:loading wire:target="saveDraft">Saving...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="submitAssessment"
                                wire:loading.attr="disabled"
                                class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800"
                            >
                                <span wire:loading.remove wire:target="submitAssessment">Submit Section</span>
                                <span wire:loading wire:target="submitAssessment">Submitting...</span>
                            </button>
                        </div>
                    @elseif($this->sectionIsSubmitted() && $this->assessmentWorkflowStatus === AssessmentWorkflowStatus::DRAFT)
                        <p class="mt-2 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-900">
                            This section has been submitted. Complete the remaining competency sections, then use <strong>Submit for Employee Confirmation</strong> in the acknowledgement block below.
                        </p>
                    @elseif($this->assessmentIsCompleted)
                        <p class="mt-2 rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">
                            This competency assessment is completed. Names and sign dates are recorded above; signature images appear on the PDF only.
                        </p>
                    @endif

                    @if($draftSaveMessage)
                        <p
                            wire:key="lnemar-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
                            class="mt-2 block w-full rounded border px-3 py-2 text-sm font-semibold shadow-sm {{ $draftSaveType === 'success' ? 'border-green-300 bg-green-100 text-green-800' : 'border-red-300 bg-red-100 text-red-800' }}"
                            role="status"
                        >
                            {{ $draftSaveMessage }}
                        </p>
                    @endif

                    @error('responses')
                        <span class="mt-2 block text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">{{ $message }}</span>
                    @enderror

                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-acknowledgement-host', [
                        'acknowledgementKey' => 'ln-emar',
                    ])
                </div>
            </div>
        </div>
    </div>
</section>
