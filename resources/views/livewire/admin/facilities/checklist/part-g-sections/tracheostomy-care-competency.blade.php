@php
    $trachJsProcedures = collect($procedureRows)->map(fn ($row) => [
        'key' => $row['key'],
        'text' => $row['text'],
        'note' => $row['note'] ?? null,
        'response' => $procedureReviews[$row['key']] ?? null,
    ])->values()->all();
@endphp

<section class="mb-4 mt-6">
    <div
        x-data="partGTrachSummary(@js($trachJsProcedures))"
        @trach-procedure-updated.window="syncReviews($event.detail.reviews)"
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
                                    <span class="truncate">TRACHEOSTOMY CARE COMPETENCY</span>
                                </div>
                                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'tracheostomy',
                                    'summaryFormId' => 'trach-summary-form',
                                ])
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion.openSection === 'tracheostomy'" x-transition>
                    <tr class="bg-white text-slate-900">
                        <td colspan="5" class="border border-gray-300 px-3 py-2 text-[11px] leading-snug md:text-xs">
                            <p>Tracheostomy care maintains a patient&rsquo;s airway by evacuating secretions, thereby preventing or reducing infections.</p>
                            <p class="mt-1 font-semibold">Rationale</p>
                            <p>Tracheostomy care maintains a patent airway by evacuating secretions, thereby preventing or reducing infections.</p>
                        </td>
                    </tr>

                    <tr class="bg-blue-50">
                        <td class="border border-gray-300 font-bold text-gray-700 text-start pl-2 text-md" colspan="4">Equipment / Supplies</td>
                        <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">Check</td>
                    </tr>

                    @foreach($renderItems as $index => $item)
                        @if(($item['type'] ?? '') === 'equipment_header')
                            <tr wire:key="trach-header-{{ $item['id'] }}">
                                <td colspan="5" class="border border-gray-300 font-bold text-gray-700 bg-blue-50 text-md" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                    {{ $item['item'] ?? '' }}
                                </td>
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
                                        @disabled($assessmentLocked || $sectionExcluded)
                                        class="h-4 w-4 rounded border-slate-400 text-slate-700 focus:ring-slate-400"
                                        aria-label="Mark {{ $item['item'] }} as checked"
                                    >
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    @if(count($procedureRows) > 0)
                        <tr class="bg-white">
                            <td colspan="5" class="border border-gray-300 px-2 py-2">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-slate-500 text-[10px] leading-tight text-slate-900 md:text-[11px]">
                                        <thead>
                                            <tr class="bg-slate-50 text-slate-900">
                                                <th rowspan="2" class="border border-slate-500 px-2 py-2 text-center font-bold">Procedure</th>
                                                <th colspan="3" class="border border-slate-500 px-2 py-2 text-center font-semibold">Check if</th>
                                            </tr>
                                            <tr class="bg-slate-50 text-slate-900">
                                                <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">E</th>
                                                <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">S</th>
                                                <th class="w-12 border border-slate-500 px-2 py-1 text-center font-bold">U</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($procedureRows as $procedureRow)
                                                <tr wire:key="trach-procedure-{{ $procedureRow['key'] }}-{{ $procedureReviews[$procedureRow['key']] ?? 'none' }}">
                                                    <td class="border border-slate-500 px-3 py-2">
                                                        {{ $procedureRow['key'] }}. {{ $procedureRow['text'] }}
                                                        @if(!empty($procedureRow['note']))
                                                            <br><span class="italic">({{ $procedureRow['note'] }})</span>
                                                        @endif
                                                    </td>
                                                    @foreach(['E', 'S', 'U'] as $procedureRating)
                                                        <td class="border border-slate-500 px-2 py-2 text-center align-middle">
                                                            <input
                                                                type="radio"
                                                                name="trach-procedure-{{ $procedureRow['key'] }}"
                                                                value="{{ $procedureRating }}"
                                                                wire:key="trach-procedure-{{ $procedureRow['key'] }}-{{ $procedureRating }}"
                                                                wire:model.live="procedureReviews.{{ $procedureRow['key'] }}"
                                                                @disabled($assessmentLocked || $sectionExcluded)
                                                                class="h-4 w-4 border-slate-400 text-slate-700 focus:ring-slate-400"
                                                                aria-label="Procedure {{ $procedureRow['key'] }} rating {{ $procedureRating }}"
                                                            >
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div id="trach-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion.openSection === 'tracheostomy'" x-transition>
                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-evaluation-summary-heading', [
                    'title' => 'TRACHEOSTOMY CARE COMPETENCY EVALUATION SUMMARY',
                    'accordionKey' => 'tracheostomy',
                    'sectionHeadingId' => 'trach-section-heading',
                ])
                <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>

                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')
                @if(! $sectionExcluded)
                <div class="mb-3">
                        <div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
                            Average Legend: <span class="font-normal">Below 1.5 = Unsatisfactory &nbsp; 1.5 to 2.49 = Satisfactory &nbsp; 2.5 and above = Excellent</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                        <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                            <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL STEPS</div>
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
                                wire:key="trach-draft-feedback-{{ md5($draftSaveMessage.$draftSaveType) }}"
                                class="mt-2 block w-full rounded border px-3 py-2 text-sm font-semibold shadow-sm {{ $draftSaveType === 'success' ? 'border-green-300 bg-green-100 text-green-800' : 'border-red-300 bg-red-100 text-red-800' }}"
                                role="status"
                            >
                                {{ $draftSaveMessage }}
                            </p>
                        @endif
                    @endif

                    @error('procedureReviews')
                        <span class="mt-2 block text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>
