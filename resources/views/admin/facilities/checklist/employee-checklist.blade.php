@vite(['resources/css/app.css', 'resources/js/app.js'])
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.bpEvaluatorActionsDisabled = @json($evaluatorActionsDisabled ?? false);
    window.bpSelfAssessmentDeniedMessage = @json(\App\Support\PreventsSelfAssessment::DEFAULT_MESSAGE);

    document.addEventListener('alpine:init', function () {
        if (typeof Alpine === 'undefined') {
            return;
        }
        if (typeof window.registerPartGAlpineStore === 'function') {
            window.registerPartGAlpineStore();
        }
        if (!Alpine.store('partFAccordion')) {
            Alpine.store('partFAccordion', { openSection: null });
        }
    });
</script>
<style>
    #employee-checklist-root [data-checklist-tab-btn].bg-white:hover {
        color: #1e40af !important;
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
    }

    #employee-checklist-root [data-checklist-tab-btn].bg-blue-600:hover {
        color: #fff !important;
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    #partA table,
    #partB table,
    #partC table,
    #partD table,
    #partE table,
    #partF table {
        border-color: #99f6e4;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.12);
    }

    /* PART A–D: narrow date/verified columns; give item + on-file columns the rest */
    #partA table,
    #partB table,
    #partC table,
    #partD table {
        table-layout: fixed;
        width: 100%;
    }

    #partA th:nth-child(1),
    #partA td:nth-child(1),
    #partB th:nth-child(1),
    #partB td:nth-child(1),
    #partC th:nth-child(1),
    #partC td:nth-child(1),
    #partD th:nth-child(1),
    #partD td:nth-child(1) {
        width: calc(100% - 32rem);
    }

    #partA th:nth-child(2),
    #partA td:nth-child(2),
    #partB th:nth-child(2),
    #partB td:nth-child(2),
    #partC th:nth-child(2),
    #partC td:nth-child(2),
    #partD th:nth-child(2),
    #partD td:nth-child(2) {
        width: 10.5rem;
        max-width: 10.5rem;
        white-space: nowrap;
    }

    #partA th:nth-child(3),
    #partA td:nth-child(3),
    #partA th:nth-child(4),
    #partA td:nth-child(4),
    #partB th:nth-child(3),
    #partB td:nth-child(3),
    #partB th:nth-child(4),
    #partB td:nth-child(4),
    #partC th:nth-child(3),
    #partC td:nth-child(3),
    #partC th:nth-child(4),
    #partC td:nth-child(4),
    #partD th:nth-child(3),
    #partD td:nth-child(3),
    #partD th:nth-child(4),
    #partD td:nth-child(4) {
        width: 6.5rem;
        max-width: 6.5rem;
        padding-left: 0.35rem;
        padding-right: 0.35rem;
        white-space: nowrap;
    }

    #partA th:nth-child(5),
    #partA td:nth-child(5),
    #partB th:nth-child(5),
    #partB td:nth-child(5),
    #partC th:nth-child(5),
    #partC td:nth-child(5),
    #partD th:nth-child(5),
    #partD td:nth-child(5) {
        width: 8.5rem;
        max-width: 8.5rem;
        padding-left: 0.35rem;
        padding-right: 0.35rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #partA thead th:nth-child(3),
    #partA thead th:nth-child(4),
    #partA thead th:nth-child(5),
    #partB thead th:nth-child(3),
    #partB thead th:nth-child(4),
    #partB thead th:nth-child(5),
    #partC thead th:nth-child(3),
    #partC thead th:nth-child(4),
    #partC thead th:nth-child(5),
    #partD thead th:nth-child(3),
    #partD thead th:nth-child(4),
    #partD thead th:nth-child(5) {
        font-size: 0.65rem;
        line-height: 1.1;
        word-break: break-word;
        white-space: normal;
    }

    #partA thead tr,
    #partB thead tr,
    #partC thead tr,
    #partD thead tr,
    #partE thead tr,
    #partF thead tr {
        background-color: #0f766e;
        color: #f0fdfa;
    }

    #partA th,
    #partA td,
    #partB th,
    #partB td,
    #partC th,
    #partC td,
    #partD th,
    #partD td,
    #partE th,
    #partE td,
    #partF th,
    #partF td {
        border-color: #99f6e4;
    }

    #partA tbody tr:nth-child(odd),
    #partB tbody tr:nth-child(odd),
    #partC tbody tr:nth-child(odd),
    #partD tbody tr:nth-child(odd),
    #partE tbody tr:nth-child(odd),
    #partF tbody tr:nth-child(odd) {
        background-color: #f0fdfa;
    }

    #partA tbody tr:nth-child(even),
    #partB tbody tr:nth-child(even),
    #partC tbody tr:nth-child(even),
    #partD tbody tr:nth-child(even),
    #partE tbody tr:nth-child(even),
    #partF tbody tr:nth-child(even) {
        background-color: #ccfbf1;
    }

    #partA tbody tr:hover,
    #partB tbody tr:hover,
    #partC tbody tr:hover,
    #partD tbody tr:hover,
    #partE tbody tr:hover,
    #partF tbody tr:hover {
        background-color: #99f6e4;
    }

    #partA .bg-gray-100,
    #partB .bg-gray-100,
    #partC .bg-gray-100,
    #partD .bg-gray-100,
    #partE .bg-gray-100,
    #partF .bg-gray-100 {
        background-color: #0f766e !important;
        color: #f0fdfa;
    }

    #partF .section-comment-save-btn,
    #partF .load-employee-btn {
        background-color: #0f766e;
    }

    #partF .section-comment-save-btn:hover,
    #partF .load-employee-btn:hover {
        background-color: #115e59;
    }

    #employeeFileTabs [data-tooltip] {
        position: relative;
    }

    #employeeFileTabs [data-tooltip]:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background-color: #1f2937;
        color: #fff;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
        white-space: normal;
        text-align: center;
        width: max-content;
        max-width: 16rem;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    #employeeFileTabs [data-tooltip]:hover::before {
        content: '';
        position: absolute;
        bottom: calc(100% + 2px);
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1f2937;
        z-index: 1000;
        pointer-events: none;
    }

    #employee-checklist-root:not(.checklist-tabs-ready) [data-checklist-tab-panel] { display: none !important; }
    @foreach(['partA', 'partB', 'partC', 'partD', 'partE', 'partF', 'partG'] as $checklistPartId)
    #employee-checklist-root[data-checklist-tab="{{ $checklistPartId }}"]:not(.checklist-tabs-ready) [data-checklist-tab-panel="{{ $checklistPartId }}"] { display: block !important; }
    #employee-checklist-root[data-checklist-tab="{{ $checklistPartId }}"]:not(.checklist-tabs-ready) [data-checklist-tab-btn="{{ $checklistPartId }}"] { background-color: #2563eb !important; color: #fff !important; }
    @endforeach
</style>
@php
    $initialChecklistTab = request('checklist_tab');
    if (! in_array($initialChecklistTab, ['partA', 'partB', 'partC', 'partD', 'partE', 'partF', 'partG'], true)) {
        $initialChecklistTab = filled(request('checklist_section')) ? 'partG' : null;
    }
@endphp
<div id="employee-checklist-root"
    data-checklist-tab="{{ $initialChecklistTab ?? 'partA' }}"
    x-show="tab === 'checklist'"
    x-cloak
    data-employee-tab-panel="checklist"
    data-checklist-tabs="alpine"
    x-data="{
    checklistTab: window.__checklistInitialTab || 'partA',
    init() {
        this.$el.classList.add('checklist-tabs-ready');
        this.$el.setAttribute('data-checklist-tab', this.checklistTab);
        localStorage.setItem('checklistTab', this.checklistTab);
        localStorage.setItem('employeeChecklistActiveTab', this.checklistTab);
        this.$watch('checklistTab', value => {
            this.$el.setAttribute('data-checklist-tab', value);
            localStorage.setItem('checklistTab', value);
            localStorage.setItem('employeeChecklistActiveTab', value);
        });
    },
    setChecklistTab(part) {
        this.checklistTab = part;
        this.$el.setAttribute('data-checklist-tab', part);
        localStorage.setItem('checklistTab', part);
        localStorage.setItem('employeeChecklistActiveTab', part);
        var url = new URL(window.location.href);
        url.searchParams.set('tab', 'checklist');
        url.searchParams.set('checklist_tab', part);
        window.history.replaceState({}, '', url);
    }
}">
<script>
    (function () {
        var root = document.getElementById('employee-checklist-root');
        if (!root) return;
        var fromUrl = new URLSearchParams(window.location.search).get('checklist_tab');
        var tab = fromUrl;
        if (!tab || !/^part[A-G]$/.test(tab)) {
            if (new URLSearchParams(window.location.search).get('checklist_section')) {
                tab = 'partG';
            }
        }
        if (!tab || !/^part[A-G]$/.test(tab)) {
            try {
                tab = localStorage.getItem('checklistTab') || localStorage.getItem('employeeChecklistActiveTab');
            } catch (e) {
                tab = null;
            }
        }
        if (!tab || !/^part[A-G]$/.test(tab)) {
            tab = root.getAttribute('data-checklist-tab') || 'partA';
        }
        window.__checklistInitialTab = tab;
        root.setAttribute('data-checklist-tab', tab);
    })();
</script>
    <div class="bg-white p-4 rounded shadow">
        @php
        $empChecklistItems = optional($empChecklists->firstWhere('employee_num', $employee->employee_num))->items ?? [];
        $resolveChecklistKey = function ($item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            $itemName = is_array($item) ? ($item['name'] ?? null) : ($item->name ?? null);

            return $itemId ? 'item_' . $itemId : $itemName;
        };
        $normalizeChecklistEntry = function ($entry): object {
            $data = is_array($entry) ? $entry : (array) $entry;

            return (object) array_merge([
                'on_file' => false,
                'verified_dt' => null,
                'exp_dt' => null,
                'verified_by' => null,
                'comments' => null,
                'exp_dt_not_required' => 0,
            ], $data);
        };
        $resolveChecklistEntry = function ($item) use ($empChecklistItems, $normalizeChecklistEntry) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            $itemName = is_array($item) ? ($item['name'] ?? null) : ($item->name ?? null);
            $raw = null;

            if ($itemId && isset($empChecklistItems['item_' . $itemId])) {
                $raw = $empChecklistItems['item_' . $itemId];
            } elseif ($itemName && isset($empChecklistItems[$itemName])) {
                $raw = $empChecklistItems[$itemName];
            } else {
                $legacyName = is_string($itemName) ? rtrim($itemName, '*') : $itemName;
                if ($legacyName && $legacyName !== $itemName && isset($empChecklistItems[$legacyName])) {
                    $raw = $empChecklistItems[$legacyName];
                }
            }

            return $raw !== null ? $normalizeChecklistEntry($raw) : null;
        };
        $checklistItemsByNameLookup = $checklistItems->keyBy('name');
        @endphp
        <script>
            window.checklistItemsByName = @json($checklistItems->mapWithKeys(fn ($item) => [
                $item->name => ['isExpiring' => (bool) $item->isExpiring, 'id' => $item->id],
            ]));
            window.checklistItemsById = @json($checklistItems->mapWithKeys(fn ($item) => [
                (string) $item->id => ['isExpiring' => (bool) $item->isExpiring, 'name' => $item->name],
            ]));
        </script>
        @if(isset($isAddMode) && $isAddMode)
        <div class="mb-4">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded">
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">Please complete and save the Personal tab form before continuing with the checklist.</span>
            </div>
        </div>
        @endif

        @php
            $checklistTabTooltips = [
                'partA' => 'Part A — Applicant info, identifications & verifications',
                'partB' => 'Part B — Acknowledgement of receipts',
                'partC' => 'Part C — HR acknowledgements',
                'partD' => 'Part D — Policies & required notices',
                'partE' => 'Part E — Orientation checklist',
                'partF' => 'Part F — Employee performance appraisal',
                'partG' => 'Part G — Competencies checklist',
            ];
        @endphp
        
        <!-- Tabs -->
        <ul class="flex border-b mb-6" id="employeeFileTabs">
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partA" @click="setChecklistTab('partA')" :class="checklistTab === 'partA' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partA'] }}" title="{{ $checklistTabTooltips['partA'] }}" aria-label="{{ $checklistTabTooltips['partA'] }}">PART A</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partB" @click="setChecklistTab('partB')" :class="checklistTab === 'partB' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partB'] }}" title="{{ $checklistTabTooltips['partB'] }}" aria-label="{{ $checklistTabTooltips['partB'] }}">PART B</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partC" @click="setChecklistTab('partC')" :class="checklistTab === 'partC' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partC'] }}" title="{{ $checklistTabTooltips['partC'] }}" aria-label="{{ $checklistTabTooltips['partC'] }}">PART C</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partD" @click="setChecklistTab('partD')" :class="checklistTab === 'partD' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partD'] }}" title="{{ $checklistTabTooltips['partD'] }}" aria-label="{{ $checklistTabTooltips['partD'] }}">PART D</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partE" @click="setChecklistTab('partE')" :class="checklistTab === 'partE' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partE'] }}" title="{{ $checklistTabTooltips['partE'] }}" aria-label="{{ $checklistTabTooltips['partE'] }}">PART E</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partF" @click="setChecklistTab('partF')" :class="checklistTab === 'partF' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partF'] }}" title="{{ $checklistTabTooltips['partF'] }}" aria-label="{{ $checklistTabTooltips['partF'] }}">PART F</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" data-checklist-tab-btn="partG" @click="setChecklistTab('partG')" :class="checklistTab === 'partG' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150" data-tooltip="{{ $checklistTabTooltips['partG'] }}" title="{{ $checklistTabTooltips['partG'] }}" aria-label="{{ $checklistTabTooltips['partG'] }}">PART G</button>
            </li>
        </ul>

        @if(!empty($evaluatorActionsDisabled))
            <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950" role="status">
                {{ \App\Support\PreventsSelfAssessment::DEFAULT_MESSAGE }}
            </div>
        @endif

        <!-- PART A -->
        <div x-show="checklistTab === 'partA'" x-cloak data-checklist-tab-panel="partA">
            @include('admin.facilities.checklist.employee-checklist-part_a')
        </div>

        <!-- PART B -->
        <div x-show="checklistTab === 'partB'" x-cloak data-checklist-tab-panel="partB">
            @include('admin.facilities.checklist.employee-checklist-part_b')
        </div>

        <!-- PART C -->
        <div x-show="checklistTab === 'partC'" x-cloak data-checklist-tab-panel="partC">
            @include('admin.facilities.checklist.employee-checklist-part_c')
        </div>

        <!-- PART D -->
        <div x-show="checklistTab === 'partD'" x-cloak data-checklist-tab-panel="partD">
            @include('admin.facilities.checklist.employee-checklist-part_d')
        </div>

        <!-- PART E -->
        <div x-show="checklistTab === 'partE'" x-cloak data-checklist-tab-panel="partE">
            @include('admin.facilities.checklist.employee-checklist-part_e')
        </div>

        <!-- PART F: Performance Appraisal -->
        <div x-show="checklistTab === 'partF'" x-cloak data-checklist-tab-panel="partF">
            @include('admin.facilities.checklist.employee-checklist-part_f')
        </div>

        <!-- PART G: Competencies Checklist -->
        <div x-show="checklistTab === 'partG'" x-cloak data-checklist-tab-panel="partG">
            @include('admin.facilities.checklist.employee-checklist-part_g')
        </div>
    </div>
</div>


<!-- Modal for Verify Document Type Item -->
@include('admin.facilities.checklist.employee-checklist-modal-ae')
@include('admin.facilities.checklist.employee-checklist-modal-f')
@include('admin.facilities.checklist.employee-assessment-period-modals') 

@include('admin.facilities.checklist.employee-checklist-scripts_ae')
@include('admin.facilities.checklist.employee-assessment-period-scripts')
@include('admin.facilities.checklist.employee-checklist-scripts_f')
@include('admin.facilities.checklist.employee-checklist-scripts_g')
@include('admin.facilities.checklist.employee-checklist-pdf-loader')