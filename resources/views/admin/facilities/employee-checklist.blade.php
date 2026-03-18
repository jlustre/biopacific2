<meta name="csrf-token" content="{{ csrf_token() }}">
<div x-show="tab === 'checklist'">
    <div class="bg-white p-4 rounded shadow">
        <!-- Tabs -->
        <ul class="flex border-b mb-6" id="employeeFileTabs">
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold active hover:text-teal-700 hover:border-teal-500"
                    href="#partA">PART A</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-teal-700 hover:border-teal-500"
                    href="#partB">PART B</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partC">PART C</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partD">PART D</a>
            </li>
            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partE">PART E</a>
            </li>

            <li class="-mb-px mr-1">
                <a class="tab-link bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 font-semibold hover:text-blue-700 hover:border-blue-500"
                    href="#partF">PART F</a>
            </li>
        </ul>

        <!-- PART A -->
        @include('admin.facilities.employee-checklist-part_a')

        <!-- PART B -->
        @include('admin.facilities.employee-checklist-part_b')

        <!-- PART C -->
        @include('admin.facilities.employee-checklist-part_c')

        <!-- PART D -->
        @include('admin.facilities.employee-checklist-part_d')

        <!-- PART E -->
        @include('admin.facilities.employee-checklist-part_e')

        <!-- PART F: Performance Appraisal -->
        @include('admin.facilities.employee-checklist-part_f')
    </div>
</div>

<!-- Modal for Verify Checklist Item -->
@include('admin.facilities.employee-checklist-modal')

{{-- Scripts for Employee Checklist --}}
@include('admin.facilities.employee-checklist-scripts')