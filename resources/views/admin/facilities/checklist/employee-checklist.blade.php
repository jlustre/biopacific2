<meta name="csrf-token" content="{{ csrf_token() }}">
<div x-show="tab === 'checklist'">
    <div class="bg-white p-4 rounded shadow">
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
    </div>
</div>

<!-- Modal for Verify Checklist Item -->
@include('admin.facilities.checklist.employee-checklist-modal-ae')
@include('admin.facilities.checklist.employee-checklist-modal-f')

{{-- Scripts for Employee Checklist --}}
@include('admin.facilities.checklist.employee-checklist-scripts_ae')
@include('admin.facilities.checklist.employee-checklist-scripts_f')