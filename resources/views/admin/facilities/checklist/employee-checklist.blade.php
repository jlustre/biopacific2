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
<div x-show="tab === 'checklist'">
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
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partA" data-tab="partA">PART A</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partB" data-tab="partB">PART B</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partC" data-tab="partC">PART C</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partD" data-tab="partD">PART D</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partE" data-tab="partE">PART E</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partF" data-tab="partF">PART F</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold transition-colors duration-150 hover:text-blue-700 hover:border-blue-500"
                    href="#partG" data-tab="partG">PART G</a>
            </li>
        </ul>

        <!-- PART A -->
        @include('admin.facilities.checklist.employee-checklist-part_a')

        <!-- PART B -->
        @include('admin.facilities.checklist.employee-checklist-part_b')

        <!-- PART C -->
        @include('admin.facilities.checklist.employee-checklist-part_c')

        <!-- PART D -->
        @include('admin.facilities.checklist.employee-checklist-part_d')

        <!-- PART E -->
        @include('admin.facilities.checklist.employee-checklist-part_e')

        <!-- PART F: Performance Appraisal -->
        @include('admin.facilities.checklist.employee-checklist-part_f')

        <!-- PART G: Competencies Checklist -->
        @include('admin.facilities.checklist.employee-checklist-part_g')
    </div>
</div>

<!-- Modal for Verify Checklist Item -->
@include('admin.facilities.checklist.employee-checklist-modal-ae')
@include('admin.facilities.checklist.employee-checklist-modal-f')

{{-- Scripts for Employee Checklist --}}
@include('admin.facilities.checklist.employee-checklist-scripts_ae')
@include('admin.facilities.checklist.employee-checklist-scripts_f')