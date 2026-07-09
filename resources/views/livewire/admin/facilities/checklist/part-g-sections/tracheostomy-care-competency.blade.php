@php
    $trachJsItems = [];
    foreach ($procedureCompetencyItems as $item) {
        $trachJsItems[] = [
            'id' => $item['id'],
            'label' => $item['item'] ?? '',
            'isParent' => false,
            'indentLevel' => $item['indentLevel'] ?? 0,
            'response' => $responses[$item['id']] ?? null,
        ];
    }
    $trachStatusItems = array_merge($renderItems, $procedureCompetencyItems);
@endphp

<section class="mb-4 mt-6">
    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-review-modal')

    <div
        x-data="partGSectionSummary(@js($trachJsItems))"
        @trach-responses-updated.window="syncResponses($event.detail.responses)"
    >
        <style>
            .trach-row-even { background-color: #f1f5f9; }
            .trach-row-odd { background-color: #e2e8f0; }
            .trach-row-hover:hover { background-color: #fef9c3 !important; }
            .competency-summary-focus { outline: 2px solid #f59e0b; outline-offset: 3px; }
        </style>

        <div>
            <table class="table w-full border border-gray-300">
                <thead>
                    <tr>
                        <th id="trach-section-heading" tabindex="-1" colspan="5" class="scroll-mt-4 bg-blue-100 text-gray-700 font-bold text-base border border-gray-300 px-4 py-1 text-left outline-none">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-accordion-toggle', [
                                        'accordionKey' => 'tracheostomy',
                                    ])
                                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-title-with-status', [
                                        'title' => 'TRACHEOSTOMY CARE',
                                        'sectionItems' => $trachStatusItems,
                                    ])
                                </div>
                                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'tracheostomy',
                                    'summaryFormId' => 'trach-summary-form',
                                ])
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'tracheostomy'" x-transition>
                    <tr class="bg-white text-slate-900">
                        <td colspan="5" class="border border-gray-300 px-3 py-2 text-[11px] leading-snug md:text-xs">
                            <p>Tracheostomy care maintains a patient&rsquo;s airway by evacuating secretions, thereby preventing or reducing infections.</p>
                            <p class="mt-1 font-semibold">Rationale</p>
                            <p>Tracheostomy care maintains a patent airway by evacuating secretions, thereby preventing or reducing infections.</p>
                        </td>
                    </tr>

                    @foreach($renderItems as $index => $item)
                        @if(($item['type'] ?? '') === 'equipment_header')
                            <tr wire:key="trach-header-{{ $item['id'] }}" class="bg-blue-50">
                                <td colspan="4" class="border border-gray-300 font-bold text-gray-700 text-start text-md" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                    {{ $item['item'] ?? '' }}
                                </td>
                                <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">Check</td>
                            </tr>
                        @elseif(($item['type'] ?? '') === 'equipment_item')
                            <tr wire:key="trach-equip-{{ $item['id'] }}" class="trach-row-{{ $index % 2 === 0 ? 'even' : 'odd' }} trach-row-hover">
                                <td colspan="4" class="border border-gray-300 py-1 text-sm" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                    {{ $item['item'] ?? '' }}
                                </td>
                                <td class="text-center border border-gray-300 py-1">
                                    <input
                                        type="checkbox"
                                        wire:key="trach-equip-check-{{ $item['id'] }}"
                                        @checked($this->isEquipmentChecked($item['rawItem']))
                                        wire:click="toggleEquipmentCheckById({{ (int) $item['id'] }})"
                                        @disabled($this->sectionItemReviewsLocked() || $sectionExcluded)
                                        class="h-4 w-4 rounded border-slate-400 text-slate-700 focus:ring-slate-400"
                                        aria-label="Mark {{ $item['item'] }} as checked"
                                    >
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    @if(count($procedureCompetencyItems) > 0)
                        <tr class="bg-blue-50">
                            <td colspan="5" class="border border-gray-300 px-2 py-1.5 text-sm font-bold text-gray-700">
                                Competency Items
                            </td>
                        </tr>
                        @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-items-column-header')
                        @foreach($procedureCompetencyItems as $index => $item)
                            @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-scorable-item-row', [
                                'item' => $item,
                                'index' => $index,
                                'wireKeyPrefix' => 'trach',
                                'rowClassPrefix' => 'trach',
                                'disabled' => $this->sectionItemReviewsLocked(),
                            ])
                            @if(!empty($item['note']))
                                <tr wire:key="trach-note-{{ $item['id'] }}" class="bg-white">
                                    <td colspan="5" class="border border-gray-300 px-3 py-1 text-[11px] italic text-slate-600" style="padding-left: calc(1.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                        Note: {{ $item['note'] }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
            </table>

            <div id="trach-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion && $store.partGAccordion.openSection === 'tracheostomy'" x-transition>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-evaluation-summary-heading', [
                    'title' => 'TRACHEOSTOMY CARE EVALUATION SUMMARY',
                    'accordionKey' => 'tracheostomy',
                    'sectionHeadingId' => 'trach-section-heading',
                ])
                <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')
                @if(! $sectionExcluded)
                <div class="mb-3">
                        <div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
                            @include('admin.facilities.checklist.partials.part-g-average-legend')
                        </div>
                    </div>
                    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-alpine-summary-metrics-grid', [
                        'totalItemsLabel' => 'TOTAL STEPS',
                    ])
                @endif

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-reviewer-comments-field')

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-summary-signature-fields')

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-draft-submit-actions')

                @if($draftSaveMessage)
                            <p
                                wire:key="trach-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
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
