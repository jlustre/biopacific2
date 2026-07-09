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
                                'disabled' => $this->sectionItemReviewsLocked(),
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
                        @include('admin.facilities.checklist.partials.part-g-average-legend')
                    </div>
                </div>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-alpine-summary-metrics-grid')
                @endif

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-reviewer-comments-field')

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-summary-signature-fields')

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-draft-submit-actions')

                @if($draftSaveMessage)
                            <p
                                wire:key="bac-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
                                class="mt-2 block w-full rounded border px-3 py-2 text-sm font-semibold shadow-sm {{ $draftSaveType === 'success' ? 'border-green-300 bg-green-100 text-green-800' : 'border-red-300 bg-red-100 text-red-800' }}"
                                role="status"
                            >
                                {{ $draftSaveMessage }}
                            </p>
                        @endif

                    @error('responses')
                        <span class="mt-2 block text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">{{ $message }}</span>
                    @enderror

                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-acknowledgement-host')
            </div>
        </div>
    </div>
</section>
