<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
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
</style>
<div x-show="tab === 'checklist'" data-checklist-tabs="alpine" x-data="{
    checklistTab: localStorage.getItem('checklistTab') || localStorage.getItem('employeeChecklistActiveTab') || 'partA',
    init() {
        localStorage.setItem('checklistTab', this.checklistTab);
        localStorage.setItem('employeeChecklistActiveTab', this.checklistTab);
        this.$watch('checklistTab', value => {
            localStorage.setItem('checklistTab', value);
            localStorage.setItem('employeeChecklistActiveTab', value);
        });
    }
}">
    <div class="bg-white p-4 rounded shadow">
        @php
        $empChecklistItems = optional($empChecklists->firstWhere('employee_num', $employee->employee_num))->items ?? [];
        $resolveChecklistKey = function ($item) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            $itemName = is_array($item) ? ($item['name'] ?? null) : ($item->name ?? null);

            return $itemId ? 'item_' . $itemId : $itemName;
        };
        $resolveChecklistEntry = function ($item) use ($empChecklistItems) {
            $itemId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
            $itemName = is_array($item) ? ($item['name'] ?? null) : ($item->name ?? null);

            if ($itemId && isset($empChecklistItems['item_' . $itemId])) {
                return (object) $empChecklistItems['item_' . $itemId];
            }

            if ($itemName && isset($empChecklistItems[$itemName])) {
                return (object) $empChecklistItems[$itemName];
            }

            return null;
        };
        @endphp
        @if(isset($isAddMode) && $isAddMode)
        <div class="mb-4">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded">
                <strong class="font-bold">Notice:</strong>
                <span class="block sm:inline">Please complete and save the Personal tab form before continuing with the checklist.</span>
            </div>
        </div>
        @endif
        
        <!-- Tabs -->
        <ul class="flex border-b mb-6" id="employeeFileTabs">
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partA'" :class="checklistTab === 'partA' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART A</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partB'" :class="checklistTab === 'partB' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART B</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partC'" :class="checklistTab === 'partC' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART C</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partD'" :class="checklistTab === 'partD' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART D</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partE'" :class="checklistTab === 'partE' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART E</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partF'" :class="checklistTab === 'partF' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART F</button>
            </li>
            <li class="-mb-px mr-1">
                <button type="button" @click="checklistTab = 'partG'" :class="checklistTab === 'partG' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="tab-link inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-100 hover:border-blue-500">PART G</button>
            </li>
        </ul>

        <!-- PART A -->
        <div x-show="checklistTab === 'partA'">
            @include('admin.facilities.checklist.employee-checklist-part_a')
        </div>

        <!-- PART B -->
        <div x-show="checklistTab === 'partB'">
            @include('admin.facilities.checklist.employee-checklist-part_b')
        </div>

        <!-- PART C -->
        <div x-show="checklistTab === 'partC'">
            @include('admin.facilities.checklist.employee-checklist-part_c')
        </div>

        <!-- PART D -->
        <div x-show="checklistTab === 'partD'">
            @include('admin.facilities.checklist.employee-checklist-part_d')
        </div>

        <!-- PART E -->
        <div x-show="checklistTab === 'partE'">
            @include('admin.facilities.checklist.employee-checklist-part_e')
        </div>

        <!-- PART F: Performance Appraisal -->
        <div x-show="checklistTab === 'partF'">
            @include('admin.facilities.checklist.employee-checklist-part_f')
        </div>

        <!-- PART G: Competencies Checklist -->
        <div x-show="checklistTab === 'partG'">
            @include('admin.facilities.checklist.employee-checklist-part_g')
        </div>
    </div>
</div>


<!-- Modal for Verify Checklist Item -->
@include('admin.facilities.checklist.employee-checklist-modal-ae')
@include('admin.facilities.checklist.employee-checklist-modal-f')
@include('admin.facilities.checklist.employee-assessment-period-modals') 

{{-- Scripts for Employee Checklist --}}
@include('admin.facilities.checklist.employee-checklist-scripts_ae')
@include('admin.facilities.checklist.employee-assessment-period-scripts')
@include('admin.facilities.checklist.employee-checklist-scripts_f')