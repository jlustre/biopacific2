@php
    $bacJsItems = [];
    foreach ($bloodCompetencyItems as $item) {
        $bacJsItems[] = [
            'id' => $item['id'],
            'label' => $item['item'] ?? '',
            'isParent' => (bool) ($item['isParent'] ?? false),
            'indentLevel' => $item['indentLevel'] ?? 0,
            'response' => $responses[$item['id']] ?? null,
        ];
    }
@endphp

<section class="mb-4 mt-6">
    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-review-modal')

    <div
        x-data="partGSectionSummary(@js($bacJsItems))"
        @bac-responses-updated.window="syncResponses($event.detail.responses)"
    >
        <style>
            .bac-row-even { background-color: #f1f5f9; }
            .bac-row-odd { background-color: #e2e8f0; }
            .bac-row-hover:hover { background-color: #fef9c3 !important; }
            .competency-summary-focus { outline: 2px solid #f59e0b; outline-offset: 3px; }
        </style>

        <div>
            <table class="table w-full border border-gray-300">
                <thead>
                    <tr>
                        <th id="bac-section-heading" tabindex="-1" colspan="5" class="scroll-mt-4 bg-blue-100 text-gray-700 font-bold text-base border border-gray-300 px-4 py-1 text-left outline-none">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-accordion-toggle', [
                                        'accordionKey' => 'blood',
                                    ])
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-title-with-status', [
                                        'title' => 'BLOOD ADMINISTRATION',
                                        'sectionItems' => $bloodCompetencyItems,
                                    ])
                                </div>
                                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'blood',
                                    'summaryFormId' => 'bac-summary-form',
                                ])
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'blood'" x-transition>
                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-items-column-header')
                    @foreach($bloodCompetencyItems as $index => $item)
                        @if($item['isParent'] ?? false)
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.nested-parent-row', [
                                'item' => $item,
                                'wireKey' => 'bac-parent-'.$item['id'],
                            ])
                        @else
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-scorable-item-row', [
                                'item' => $item,
                                'index' => $index,
                                'wireKeyPrefix' => 'bac',
                                'rowClassPrefix' => 'bac',
                                'disabled' => $assessmentLocked || $sectionExcluded || ! $assessmentPeriodId,
                            ])
                        @endif

                        @if(($item['rawItem'] ?? '') === \App\Livewire\Admin\Facilities\Checklist\PartGSections\BloodAdministrationCompetency::TRANSFUSION_TABLE_AFTER_ITEM)
                            <tr class="bg-white text-slate-900">
                                <td colspan="5" class="border border-gray-300 px-2 py-2">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border border-slate-500 text-[10px] leading-tight text-slate-900 md:text-[11px]">
                                            <thead>
                                                <tr class="bg-slate-200 text-center font-bold">
                                                    <th colspan="3" class="border border-slate-500 px-1.5 py-1">Non-emergent Blood Component Transfusions</th>
                                                </tr>
                                                <tr class="bg-slate-50 text-center font-semibold">
                                                    <th rowspan="3" class="border border-slate-500 px-1.5 py-1 align-bottom">Blood Component</th>
                                                    <th colspan="2" class="border border-slate-500 px-1.5 py-1">Suggested Adult Flow Rate<br>Reference: AABB Technical Manual, 19th Edition, 2017, Bethesda, MD</th>
                                                </tr>
                                                <tr class="bg-slate-50 text-center font-semibold">
                                                    <th class="border border-slate-500 px-1.5 py-1">First 15 minutes</th>
                                                    <th class="border border-slate-500 px-1.5 py-1">After first 15 minutes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="border border-slate-500 px-1.5 py-1 font-semibold">Red Blood Cells (RBCs)</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">1-2 mL/min (60-120 mL/hr)</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">As rapidly as tolerated; approximately 4 mL/min or 240 mL/hour</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-slate-500 px-1.5 py-1 font-semibold">Platelets</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">2-5 mL/min (120-300 mL/hr)</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">300 mL/hour or as tolerated</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-slate-500 px-1.5 py-1 font-semibold">Plasma</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">2-5 mL/min (120-300 mL/hr)</td>
                                                    <td class="border border-slate-500 px-1.5 py-1 text-center">As rapidly as tolerated; approximately 300 mL/hour</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-slate-500 px-1.5 py-1 font-semibold">Cryoprecipitate</td>
                                                    <td colspan="2" class="border border-slate-500 px-1.5 py-1 text-center font-semibold">As rapidly as tolerated</td>
                                                </tr>
                                                <tr class="bg-slate-100">
                                                    <td colspan="3" class="border border-slate-500 px-1.5 py-1 text-center font-semibold">Note: For patients at risk for fluid overload, use slower flow.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div id="bac-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'blood'" x-transition>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-evaluation-summary-heading', [
                    'title' => 'BLOOD ADMINISTRATION EVALUATION SUMMARY',
                    'accordionKey' => 'blood',
                    'sectionHeadingId' => 'bac-section-heading',
                ])
                <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')
                @if(! $sectionExcluded)
                <div class="mb-3">
                    <div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
                        Average Legend: <span class="font-normal">Below 1.5 = Unsatisfactory 1.5 to 2.49 = Satisfactory 2.5 and above = Excellent</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL ITEMS</div>
                        <div class="text-2xl font-bold text-gray-700" x-text="summary.totalItems"></div>
                        <div class="text-xs text-gray-500 mt-1" x-text="summary.checkedOfTotal"></div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL POINTS</div>
                        <div class="text-2xl font-bold text-gray-700" x-text="summary.totalPoints"></div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">AVERAGE</div>
                        <div class="text-2xl font-bold text-gray-700" x-text="summary.average"></div>
                    </div>
                    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                        <div class="text-xs font-semibold text-gray-500 mb-1">OVERALL RATING</div>
                        <div class="text-2xl font-bold text-gray-700" x-text="summary.overallRating"></div>
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
                    <textarea
                        wire:model="summaryComments"
                        @disabled($assessmentLocked)
                        class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y"
                        placeholder="Enter comments here..."
                    ></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-blue-700 mb-1">EMPLOYEE COMMENTS</label>
                    <textarea
                        wire:model="employeeComments"
                        @disabled($assessmentLocked)
                        class="w-full rounded border border-blue-300 bg-blue-50 p-3 text-blue-700 min-h-[100px] resize-y"
                        placeholder="Enter employee comments here..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 mb-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerName }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerTitle }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW SIGN DATE</label>
                            <input type="date" wire:model="reviewSignDate" @disabled($assessmentLocked) class="w-full rounded border border-gray-300 bg-white p-2" required />
                            @error('reviewSignDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeName }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeTitle }}" readonly />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE SIGN DATE</label>
                            <input type="date" wire:model="employeeSignDate" @disabled($assessmentLocked) class="w-full rounded border border-gray-300 bg-white p-2" />
                        </div>
                    </div>

                    @if(! $assessmentLocked)
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
                                <span wire:loading.remove wire:target="submitAssessment">Submit Assessment</span>
                                <span wire:loading wire:target="submitAssessment">Submitting...</span>
                            </button>
                        </div>

                        @if($draftSaveMessage)
                            <p
                                wire:key="bac-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
                                class="mt-2 block w-full rounded border px-3 py-2 text-sm font-semibold shadow-sm {{ $draftSaveType === 'success' ? 'border-green-300 bg-green-100 text-green-800' : 'border-red-300 bg-red-100 text-red-800' }}"
                                role="status"
                            >
                                {{ $draftSaveMessage }}
                            </p>
                        @endif
                    @endif

                    @error('responses')
                        <span class="mt-2 block text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>
