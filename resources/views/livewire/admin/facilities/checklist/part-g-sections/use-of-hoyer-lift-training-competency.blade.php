@php
    $hltJsItems = [];
    foreach ($hoyerLiftCompetencyItems as $item) {
        $hltJsItems[] = [
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
        x-data="partGSectionSummary(@js($hltJsItems))"
        @hlt-responses-updated.window="syncResponses($event.detail.responses)"
    >
        <style>
            .hlt-row-even { background-color: #f1f5f9; }
            .hlt-row-odd { background-color: #e2e8f0; }
            .hlt-row-hover:hover { background-color: #fef9c3 !important; }
            .competency-summary-focus { outline: 2px solid #f59e0b; outline-offset: 3px; }
        </style>

        <div>
            <table class="table w-full border border-gray-300">
                <thead>
                    <tr>
                        <th id="hlt-section-heading" tabindex="-1" colspan="5" class="scroll-mt-4 bg-blue-100 text-gray-700 font-bold text-base border border-gray-300 px-4 py-1 text-left outline-none">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-accordion-toggle', [
                                        'accordionKey' => 'hoyer-lift',
                                    ])
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-title-with-status', [
                                        'title' => 'USE OF HOYER LIFT',
                                        'sectionItems' => $hoyerLiftCompetencyItems,
                                    ])
                                </div>
                                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'hoyer-lift',
                                    'summaryFormId' => 'hlt-summary-form',
                                ])
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'hoyer-lift'" x-transition>
                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-items-column-header')
                    @foreach($hoyerLiftCompetencyItems as $index => $item)
                        @if($item['isParent'] ?? false)
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.nested-parent-row', [
                                'item' => $item,
                                'wireKey' => 'hlt-parent-'.$item['id'],
                            ])
                        @else
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-scorable-item-row', [
                                'item' => $item,
                                'index' => $index,
                                'wireKeyPrefix' => 'hlt',
                                'rowClassPrefix' => 'hlt',
                                'disabled' => $this->sectionItemReviewsLocked(),
                            ])
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div id="hlt-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'hoyer-lift'" x-transition>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-evaluation-summary-heading', [
                    'title' => 'USE OF HOYER LIFT EVALUATION SUMMARY',
                    'accordionKey' => 'hoyer-lift',
                    'sectionHeadingId' => 'hlt-section-heading',
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
                                wire:key="hlt-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
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
