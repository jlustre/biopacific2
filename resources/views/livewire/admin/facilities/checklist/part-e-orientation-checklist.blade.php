<div class="overflow-x-auto" data-part-e-orientation-checklist>
    @if($evaluatorActionsDisabled)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950" role="status">
            {{ \App\Support\PreventsSelfAssessment::DEFAULT_MESSAGE }}
        </div>
    @endif
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <h2 class="text-xl font-bold">ORIENTATION CHECKLIST: {{ $positionTitle }}</h2>
        @if(! $jobCodeId)
            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-900">No position on assignment</span>
        @endif
    </div>

    @if($summaryError)
        <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-900" role="alert">
            {{ $summaryError }}
        </div>
    @endif

    @if($summaryFeedback)
        <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-900" role="status">
            {{ $summaryFeedback }}
        </div>
    @endif

    @if(count($orientationRows) > 0)
        <table class="min-w-full table-fixed overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 shadow-sm md:text-xs">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">ORIENTATION ITEMS</th>
                    <th class="w-28 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMATION</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMED DATE</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">EXPIRY DATE</th>
                    <th class="w-24 border border-slate-500 px-1.5 py-1.5 text-center font-semibold tracking-wide">CONFIRMED BY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orientationRows as $itemIdx => $row)
                    @php
                        $empChecklist = $this->resolveChecklistEntry($row);
                        $itemLevel = (int) ($row['indentLevel'] ?? 0);
                        $displayIndentClass = match (true) {
                            $itemLevel >= 2 => 'inline-block pl-8',
                            $itemLevel === 1 => 'inline-block pl-4',
                            default => '',
                        };
                        $isParent = (bool) ($row['isParent'] ?? false);
                        $checklistKey = $row['checklistKey'] ?? ('item_'.$row['id']);
                        $rowOdd = $itemIdx % 2 === 0;
                        $rowClasses = $rowOdd ? 'bg-white text-slate-900' : 'bg-slate-50 text-slate-900';
                    @endphp
                    <tr
                        class="{{ $rowClasses }} hover:bg-slate-100 transition-colors"
                        wire:key="part-e-row-{{ $row['id'] }}"
                        data-doc-type-id="{{ $row['doc_type_id'] ?? '' }}"
                        data-item-name="{{ $row['name'] }}"
                        data-item-level="{{ $itemLevel }}"
                        data-has-child-items="{{ $isParent ? '1' : '0' }}"
                        data-item-disabled="{{ !empty($row['disabled']) ? '1' : '0' }}"
                    >
                        <td class="border border-slate-500 px-2 py-1.5 align-top @if(!empty($row['disabled'])) line-through @endif">
                            <span class="text-[11px] leading-tight {{ $displayIndentClass }} {{ $itemLevel === 0 ? 'font-bold' : '' }}">
                                @if($isParent)
                                    <span wire:ignore>
                                        <button
                                            type="button"
                                            class="partE-hierarchy-toggle bp-checklist-expand-toggle mr-2 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100"
                                            data-expanded="1"
                                            aria-label="Collapse child items"
                                        >▲</button>
                                    </span>
                                @endif
                                <span>{{ $row['displayName'] }}</span>
                            </span>
                        </td>
                        <td class="border border-slate-500 px-1.5 py-1.5 align-top text-center whitespace-nowrap">
                            @if($isParent)
                                <span class="text-slate-400">—</span>
                            @else
                                    <input
                                        type="checkbox"
                                        {{ $empChecklist && $empChecklist->on_file ? 'checked' : '' }}
                                        class="part-e-confirm-checkbox pointer-events-auto"
                                        tabindex="0"
                                        aria-label="Open confirmation for this item"
                                        @if(!empty($row['disabled']) || $evaluatorActionsDisabled) disabled @endif
                                    >
                                @if(empty($row['disabled']))
                                    @if($empChecklist && $empChecklist->verified_by)
                                        <a href="#" class="text-red-600 underline ml-2 mr-1 unverify-link" title="Click to unconfirm Item"
                                            data-item-name="{{ $row['name'] }}" data-item-id="{{ $row['id'] }}" data-checklist-key="{{ $checklistKey }}" data-emp-id="{{ $employeeNum }}">Confirmed</a>
                                        <span>|</span>
                                        <a href="#" class="text-slate-700 underline ml-1 view-link" title="View Confirmation Details"
                                            data-item-name="{{ $row['name'] }}"
                                            data-item-id="{{ $row['id'] }}"
                                            data-checklist-key="{{ $checklistKey }}"
                                            data-emp-id="{{ $employeeNum }}"
                                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? '1' : '0' }}"
                                            data-verified-dt="{{ $empChecklist?->verified_dt ?? '' }}"
                                            data-exp-dt="{{ $empChecklist?->exp_dt ?? '' }}"
                                            data-comments="{{ $empChecklist?->comments ?? '' }}"
                                            data-verified-by="{{ $this->verifiedByLabel($empChecklist) }}"
                                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? '1' : '0' }}"
                                        >View</a>
                                    @else
                                        <a href="#" class="text-slate-700 underline ml-2 verify-link" title="Confirm Item"
                                            data-item-name="{{ $row['name'] }}"
                                            data-item-id="{{ $row['id'] }}"
                                            data-checklist-key="{{ $checklistKey }}"
                                            data-emp-id="{{ $employeeNum }}"
                                            data-on-file="{{ $empChecklist && $empChecklist->on_file ? '1' : '0' }}"
                                            data-verified-dt="{{ $empChecklist?->verified_dt ?? '' }}"
                                            data-exp-dt="{{ $empChecklist?->exp_dt ?? '' }}"
                                            data-comments="{{ $empChecklist?->comments ?? '' }}"
                                            data-verified-by="{{ $empChecklist?->verified_by ?? '' }}"
                                            data-exp-dt-not-required="{{ ($empChecklist && ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '')) ? '1' : '0' }}"
                                        >Confirm</a>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                            @if(!$isParent)
                                @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                                    {{ ($empChecklist->verified_dt === null || $empChecklist->verified_dt === '') ? 'N/A' : $empChecklist->verified_dt }}
                                @else
                                    {{ $empChecklist?->verified_dt ?? '' }}
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                            @if(!$isParent)
                                @if($empChecklist && ($empChecklist->on_file || $empChecklist->verified_dt))
                                    {{ ($empChecklist->exp_dt === null || $empChecklist->exp_dt === '') ? 'N/A' : $empChecklist->exp_dt }}
                                @else
                                    {{ $empChecklist?->exp_dt ?? '' }}
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="border border-slate-500 px-1.5 py-1.5 text-center align-top whitespace-nowrap">
                            @if(!$isParent)
                                @if($empChecklist && $empChecklist->verified_by)
                                    {{ $this->verifiedByLabel($empChecklist) }}
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 rounded-lg border border-slate-400 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">Orientation checklist status</h3>
                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $this->workflowStatusBadgeClass() }}">
                    {{ $this->workflowStatusLabel() }}
                </span>
            </div>
            <p class="text-xs text-slate-600 mb-2">History of status changes for this orientation record (newest first).</p>
            <div class="overflow-x-auto rounded border border-slate-300">
                <table class="min-w-full text-left text-[11px] text-slate-900 md:text-xs">
                    <thead class="bg-slate-100 text-slate-800">
                        <tr>
                            <th class="border-b border-slate-300 px-2 py-2 font-semibold">When</th>
                            <th class="border-b border-slate-300 px-2 py-2 font-semibold">Status</th>
                            <th class="border-b border-slate-300 px-2 py-2 font-semibold">Recorded by</th>
                            <th class="border-b border-slate-300 px-2 py-2 font-semibold">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_reverse($orientationStatusHistory) as $entry)
                            @php
                                $when = $entry['recorded_at'] ?? '';
                                $whenLabel = $when !== '' ? \Illuminate\Support\Carbon::parse($when)->timezone(config('app.timezone'))->format('M j, Y g:i A') : '—';
                            @endphp
                            <tr class="odd:bg-white even:bg-slate-50">
                                <td class="border-b border-slate-200 px-2 py-1.5 whitespace-nowrap">{{ $whenLabel }}</td>
                                <td class="border-b border-slate-200 px-2 py-1.5 font-medium">{{ $this->workflowHistoryStatusLabel((string) ($entry['status'] ?? '')) }}</td>
                                <td class="border-b border-slate-200 px-2 py-1.5">{{ $entry['actor_label'] ?? '—' }}</td>
                                <td class="border-b border-slate-200 px-2 py-1.5">{{ $entry['detail'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-4 text-center text-slate-500">No status changes recorded yet. Use &ldquo;Save as Draft&rdquo; or &ldquo;Submit Checklist&rdquo; to begin tracking.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $formLocked = $orientationWorkflowStatus === \App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist::WORKFLOW_COMPLETED;
            $lockReviewerSignDate = in_array($orientationWorkflowStatus, ['employee_signature_needed', 'completed'], true);
            $lockEmployeeSignDate = in_array($orientationWorkflowStatus, ['reviewer_signature_needed', 'completed'], true);
        @endphp

        <div id="part-e-orientation-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none">
            <div class="font-bold text-lg text-gray-800 mb-1">ORIENTATION SUMMARY</div>
            <div class="text-sm text-gray-700 mb-3">
                @if($orientationWorkflowStatus === 'draft')
                    Save a draft with reviewer comments, then submit the checklist to start the signature process.
                @elseif($orientationWorkflowStatus === 'employee_signature_needed')
                    Enter the employee sign date, then confirm &ldquo;Record employee signature&rdquo; to continue.
                @elseif($orientationWorkflowStatus === 'reviewer_signature_needed')
                    Enter the reviewer sign date, then confirm &ldquo;Record reviewer signature&rdquo; to complete.
                @else
                    This orientation checklist is completed.
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                    <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL ITEMS</div>
                    <div class="text-2xl font-bold text-gray-700">{{ $this->orientationStats['total'] }}</div>
                </div>
                <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
                    <div class="text-xs font-semibold text-gray-500 mb-1">CONFIRMED</div>
                    <div class="text-2xl font-bold text-gray-700">{{ $this->orientationStats['confirmed'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">of {{ $this->orientationStats['total'] }} requiring confirmation</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
                <textarea
                    wire:model="orientationSummary.reviewer_comments"
                    @disabled($formLocked)
                    class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y @if($formLocked) opacity-70 cursor-not-allowed @endif"
                    placeholder="Enter comments here..."
                ></textarea>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-semibold text-blue-700 mb-1">EMPLOYEE COMMENTS</label>
                <textarea
                    wire:model="orientationSummary.employee_comments"
                    @disabled($formLocked)
                    class="w-full rounded border border-blue-300 bg-blue-50 p-3 text-blue-700 min-h-[100px] resize-y @if($formLocked) opacity-70 cursor-not-allowed @endif"
                    placeholder="Enter employee comments here..."
                ></textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerNameDisplay }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $reviewerTitleDisplay }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW SIGN DATE</label>
                        <input
                            type="date"
                            wire:model="orientationSummary.review_sign_date"
                            @disabled($formLocked || $lockReviewerSignDate)
                            class="w-full rounded border border-gray-300 bg-white p-2 @if($formLocked || $lockReviewerSignDate) opacity-70 cursor-not-allowed @endif"
                        >
                        @error('orientationSummary.review_sign_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeNameDisplay }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" value="{{ $employeeTitleDisplay }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE SIGN DATE</label>
                        <input
                            type="date"
                            wire:model="orientationSummary.employee_sign_date"
                            @disabled($formLocked || $lockEmployeeSignDate)
                            class="w-full rounded border border-gray-300 bg-white p-2 @if($formLocked || $lockEmployeeSignDate) opacity-70 cursor-not-allowed @endif"
                        >
                        @error('orientationSummary.employee_sign_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-end gap-2 mt-2 flex-wrap">
                @if($orientationWorkflowStatus === \App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist::WORKFLOW_DRAFT && ! $evaluatorActionsDisabled)
                    <button
                        type="button"
                        wire:click="saveOrientationDraft"
                        wire:loading.attr="disabled"
                        class="rounded border border-amber-700 bg-white px-6 py-2 font-semibold text-amber-900 hover:bg-amber-50"
                    >
                        <span wire:loading.remove wire:target="saveOrientationDraft">Save as Draft</span>
                        <span wire:loading wire:target="saveOrientationDraft">Saving…</span>
                    </button>
                    <button
                        type="button"
                        wire:click="submitOrientationChecklist"
                        wire:loading.attr="disabled"
                        class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800"
                    >
                        <span wire:loading.remove wire:target="submitOrientationChecklist">Submit Checklist</span>
                        <span wire:loading wire:target="submitOrientationChecklist">Submitting…</span>
                    </button>
                @elseif($orientationWorkflowStatus === \App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist::WORKFLOW_EMPLOYEE_SIGNATURE)
                    <button
                        type="button"
                        wire:click="completeEmployeeSignatureStep"
                        wire:loading.attr="disabled"
                        class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800"
                    >
                        <span wire:loading.remove wire:target="completeEmployeeSignatureStep">Record employee signature</span>
                        <span wire:loading wire:target="completeEmployeeSignatureStep">Saving…</span>
                    </button>
                @elseif($orientationWorkflowStatus === \App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist::WORKFLOW_REVIEWER_SIGNATURE && ! $evaluatorActionsDisabled)
                    <button
                        type="button"
                        wire:click="completeReviewerSignatureStep"
                        wire:loading.attr="disabled"
                        class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800"
                    >
                        <span wire:loading.remove wire:target="completeReviewerSignatureStep">Record reviewer signature</span>
                        <span wire:loading wire:target="completeReviewerSignatureStep">Saving…</span>
                    </button>
                @endif
            </div>
        </div>
    @else
        <p class="rounded-md border border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
            @if(! $jobCodeId)
                Assign a position to this employee to load the orientation checklist.
            @elseif(! in_array($positionTitle, \App\Orientation\OrientationChecklistSource::seededOrientationPositionTitles(), true))
                No orientation checklist items are seeded for &ldquo;{{ $positionTitle }}&rdquo;. Orientation lists exist for:
                {{ collect(\App\Orientation\OrientationChecklistSource::seededOrientationPositionTitles())->join(', ', ' and ') }}.
            @else
                No orientation checklist items were found in the database for this position. Run
                <code class="rounded bg-slate-200 px-1">OrientationChecklistItemsSeeder</code>
                (or refresh seed data) if this role should have a list.
            @endif
        </p>
    @endif
</div>
