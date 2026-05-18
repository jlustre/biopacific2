@php
    $lncJsItems = [];
    foreach ($lnCompetencyItems as $item) {
        $lncJsItems[] = [
            'id' => $item['id'],
            'label' => $item['item'] ?? '',
            'isParent' => false, // Force all to child for troubleshooting
            'indentLevel' => $item['indentLevel'] ?? 0,
            'response' => $draftResponses[$item['id']] ?? null,
        ];
    }
@endphp

<section class="mb-4">
    {{-- @if(isset($rawDraftRow) && $rawDraftRow)
        <div class="bg-yellow-100 text-xs text-amber-900 border border-amber-400 rounded p-2 mb-2">
            <strong>DEBUG: rawDraftRow-&gt;responses:</strong>
            <pre>{{ $rawDraftRow->responses }}</pre>
        </div>
        <div class="bg-blue-100 text-xs text-blue-900 border border-blue-400 rounded p-2 mb-2">
            <strong>DEBUG: decoded draftResponses array:</strong>
            <pre>@json($draftResponses)</pre>
        </div>
    @else
        <div class="bg-red-100 text-xs text-red-900 border border-red-400 rounded p-2 mb-2">
            <strong>DEBUG:</strong> No draft row found for this employee/period.<br>
            employee_num: <b>{{ $employee->employee_num ?? 'null' }}</b><br>
            assessment_period_id: <b>{{ $selectedAssessmentPeriodId ?? 'null' }}</b>
        </div>
    @endif --}}
    <form id="lnCompetencyForm" method="POST" action="{{ route('partg.ln_competency_skills.save') }}">
        <div id="js-debug-payload" class="bg-green-100 text-xs text-green-900 border border-green-400 rounded p-2 mb-2" style="display:none;"></div>
        @csrf
        <input type="hidden" name="employee_num" value="{{ $employee->employee_num ?? '' }}">
        <input type="hidden" name="employee_assessment_period_id" value="{{ $selectedAssessmentPeriodId ?? '' }}">
        {{-- Tailwind CSS classes will be used for row colors and hover --}}
        <div x-data="lncSummary()">
        <style>
            /* Custom alternating row colors and hover effect */
            .lnc-row-even { background-color: #f1f5f9; } /* slate-100 */
            .lnc-row-odd { background-color: #e2e8f0; }  /* slate-200 */
            .lnc-row-hover:hover { background-color: #fef9c3 !important; } /* yellow-100 */
            .competency-summary-focus { outline: 2px solid #f59e0b; outline-offset: 3px; }
        </style>
        <div>
            <table class="table w-full border border-gray-300">
                <thead>
                    <tr>
                        <th colspan="5" class="bg-blue-100 text-gray-700 font-bold text-base border border-gray-300 px-4 py-1 text-left">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <button type="button"
                                        class="section-toggle inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100"
                                        x-text="$store.partGAccordion.openSection === 'ln' ? '▲' : '▼'"
                                        x-bind:aria-label="$store.partGAccordion.openSection === 'ln' ? 'Collapse section items' : 'Expand section items'"
                                        x-on:click="$store.partGAccordion.openSection = $store.partGAccordion.openSection === 'ln' ? null : 'ln'"
                                        x-bind:data-expanded="$store.partGAccordion.openSection === 'ln' ? '1' : '0'"
                                    ></button>
                                    <span class="truncate">LICENSED NURSE COMPETENCIES</span>
                                </div>
                                <button type="button"
                                    class="go-to-summary-btn shrink-0 rounded border border-slate-500 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm hover:bg-amber-50 hover:border-amber-500 hover:text-amber-900"
                                    x-on:click="
                                        $store.partGAccordion.openSection = 'ln';
                                        $nextTick(() => {
                                            const el = document.getElementById('lnc-summary-form');
                                            if (!el) return;
                                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                            el.focus({ preventScroll: true });
                                            el.classList.add('competency-summary-focus');
                                            setTimeout(() => el.classList.remove('competency-summary-focus'), 2000);
                                        });
                                    "
                                >
                                    Evaluation Summary ↓
                                </button>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody x-show="$store.partGAccordion.openSection === 'ln'" x-transition>
                <!-- Extra row for Items and Ratings -->
                <tr class="bg-blue-50">
                    <td class="border border-gray-300 font-bold text-gray-700 text-start pl-2 text-md">Items</td>
                    <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">E</td>
                    <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">S</td>
                    <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">U</td>
                    <td class="text-center border border-gray-300 font-semibold text-gray-700 px-3">N</td>
                </tr>
                @foreach($lnCompetencyItems as $index => $item)
                    @if($item['isParent'] ?? false)
                        <tr>
                            <td class="border border-gray-300 font-bold text-gray-700 bg-blue-50 text-md" colspan="5" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                {{ $item['item'] ?? '' }}
                            </td>
                        </tr>
                    @else
                        <tr class="lnc-row-{{ $index % 2 === 0 ? 'even' : 'odd' }} lnc-row-hover">
                            <td class="border border-gray-300 py-0 text-sm" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
                                {{ $item['item'] ?? '' }}
                            </td>
                            <td class="text-center border border-gray-300 py-0">
                                <input type="radio" name="items[{{ $item['id'] }}][response]" value="E" {{ (isset($draftResponses[$item['id']]) && $draftResponses[$item['id']] === 'E') ? 'checked' : '' }}>
                            </td>
                            <td class="text-center border border-gray-300 py-0">
                                <input type="radio" name="items[{{ $item['id'] }}][response]" value="S" {{ (isset($draftResponses[$item['id']]) && $draftResponses[$item['id']] === 'S') ? 'checked' : '' }}>
                            </td>
                            <td class="text-center border border-gray-300 py-0">
                                <input type="radio" name="items[{{ $item['id'] }}][response]" value="U" {{ (isset($draftResponses[$item['id']]) && $draftResponses[$item['id']] === 'U') ? 'checked' : '' }}>
                            </td>
                            <td class="text-center border border-gray-300 py-0">
                                <input type="radio" name="items[{{ $item['id'] }}][response]" value="N" {{ (isset($draftResponses[$item['id']]) && $draftResponses[$item['id']] === 'N') ? 'checked' : '' }}>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div id="lnc-summary-form" tabindex="-1" class="mt-8 rounded-lg border border-gray-300 bg-slate-50 p-4 scroll-mt-4 outline-none" x-show="$store.partGAccordion.openSection === 'ln'" x-transition>
            <div class="font-bold text-lg text-gray-800 mb-1">LICENSED NURSE COMPETENCY EVALUATION SUMMARY</div>
            <div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>
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
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
                <textarea name="summary_comments" id="summary_comments" class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y" placeholder="Enter comments here...">{{ $rawDraftRow?->comments ?? '' }}</textarea>
            </div>
            <!-- EMPLOYEE COMMENTS (hidden by default, shown only for employee) -->
            <div class="mb-3" id="employee-comments-section" style="display:none;">
                <label class="block text-xs font-semibold text-blue-700 mb-1">EMPLOYEE COMMENTS</label>
                <textarea name="employee_comments" id="employee_comments" class="w-full rounded border border-blue-300 bg-blue-50 p-3 text-blue-700 min-h-[100px] resize-y" placeholder="Enter employee comments here...">{{ $rawDraftRow?->employee_comments ?? '' }}</textarea>
            </div>
            <button type="button" id="showEmployeeCommentsBtn" class="text-xs text-blue-700 underline mb-2" style="display:none;">Add/View Employee Comments</button>
            <div class="grid grid-cols-1 gap-4 mb-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER NAME/SIGNATURE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" name="reviewer_name" value="{{ $reviewer->name ?? (auth()->user()->name ?? '') }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER TITLE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" name="reviewer_title" value="{{ $reviewer->title ?? (auth()->user()->title ?? '') }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEW SIGN DATE</label>
                        <input type="date" class="w-full rounded border border-gray-300 bg-white p-2" id="review_sign_date" name="review_sign_date" required />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE NAME/SIGNATURE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" name="employee_name" value="{{ $employee->last_name ?? '' }}, {{ $employee->first_name ?? '' }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE TITLE</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2" name="employee_title" value="{{ $employee->currentPosition->title ?? $employee->position ?? '' }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">EMPLOYEE SIGN DATE</label>
                        <input type="date" class="w-full rounded border border-gray-300 bg-white p-2" id="employee_sign_date" name="employee_sign_date" />
                    </div>
                </div>
                <div class="flex flex-col md:flex-row justify-end gap-2 mt-2">
                    <button type="button" id="saveAsDraftBtn" class="rounded border-gray-400 bg-white px-6 py-2 font-semibold textn inline-flex items-center text-amber-900 shadow hover:bg-amber-200 border">Save as Draft</button>
                    <button type="submit" id="submitAssessmentBtn" class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800">Submit Assessment</button>
                    <button type="button" id="reviewMissingBtn" class="rounded border border-red-600 bg-red-100 px-6 py-2 font-semibold text-red-700 hover:bg-red-200 border-dashed hidden">Review Unchecked Items</button>
                </div>
                <p id="draftSavedMsg" class="mt-2 hidden w-full rounded border px-3 py-2 text-sm font-semibold shadow-sm" role="status"></p>
            </div>
        </div>
        </div>
        </div>
    </form>
</section>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Register Accordion Store
    if (!Alpine.store('partGAccordion')) {
        Alpine.store('partGAccordion', { openSection: 'ln' });
    }
    // Register lncSummary Alpine component
    Alpine.data('lncSummary', () => ({
        items: @json($lncJsItems),
        summary: {
            totalItems: 0,
            checkedOfTotal: '',
            totalPoints: 0,
            average: '—',
            overallRating: '—',
        },
        init() {
            this.updateSummary();
        },
        updateSummary() {
            let total = 0, checked = 0, points = 0;
            this.items.forEach(item => {
                if (!item.isParent) {
                    total++;
                    if (item.response) {
                        checked++;
                        points += this.responseToPoints(item.response);
                    }
                }
            });
            this.summary.totalItems = total;
            this.summary.checkedOfTotal = `${checked} of ${total} checked`;
            this.summary.totalPoints = points;
            this.summary.average = total > 0 ? (points / total).toFixed(2) : '—';
            this.summary.overallRating = this.getOverallRating(points, total);
        },
        responseToPoints(val) {
            return val === 'E' ? 3 : val === 'S' ? 2 : val === 'U' ? 1 : 0;
        },
        getOverallRating(points, total) {
            if (total === 0) return '—';
            const avg = points / total;
            if (avg >= 2.5) return 'Excellent';
            if (avg >= 1.5) return 'Satisfactory';
            if (avg > 0) return 'Unsatisfactory';
            return 'Needs Improvement';
        },
    }));
});
</script>

    function showDraftFeedback(draftMsg, type, message) {
        if (!draftMsg) {
            return;
        }
        draftMsg.textContent = message;
        draftMsg.classList.remove('hidden', 'border-green-300', 'bg-green-100', 'text-green-800', 'border-red-300', 'bg-red-100', 'text-red-800');
        draftMsg.classList.add(type === 'success' ? 'border-green-300' : 'border-red-300', type === 'success' ? 'bg-green-100' : 'bg-red-100', type === 'success' ? 'text-green-800' : 'text-red-800');
    }

    function initLnDraftSave() {
        const saveBtn = document.getElementById('saveAsDraftBtn');
        const form = document.getElementById('lnCompetencyForm');
        const draftMsg = document.getElementById('draftSavedMsg');

        if (!saveBtn || !form || saveBtn.dataset.lnDraftBound === '1') {
            return;
        }

        saveBtn.dataset.lnDraftBound = '1';

        saveBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const csrfToken = form.querySelector('input[name="_token"]')?.value
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                showDraftFeedback(draftMsg, 'error', 'Security token missing. Refresh the page and try again.');
                return;
            }

            const formData = new FormData(form);
            formData.append('save_as_draft', '1');

            saveBtn.disabled = true;
            const originalLabel = saveBtn.textContent;
            saveBtn.textContent = 'Saving...';

            fetch(saveDraftUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: formData,
            })
            .then(async (response) => {
                let data = {};
                try {
                    data = await response.json();
                } catch (error) {
                    data = { success: false, message: 'Unexpected server response.' };
                }

                if (response.ok && data.success) {
                    showDraftFeedback(draftMsg, 'success', data.message || 'Draft saved successfully!');
                    setTimeout(() => draftMsg?.classList.add('hidden'), 4000);
                } else {
                    showDraftFeedback(draftMsg, 'error', data.message || 'Failed to save draft. Please try again.');
                }
            })
            .catch(() => {
                showDraftFeedback(draftMsg, 'error', 'Failed to save draft. Please try again.');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = originalLabel;
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLnDraftSave);
    } else {
        initLnDraftSave();
    }

    document.addEventListener('livewire:navigated', initLnDraftSave);
})();
</script>
@endpush