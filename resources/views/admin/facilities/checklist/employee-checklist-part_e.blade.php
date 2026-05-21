<div id="partE" class="tab-content">
    @livewire('admin.facilities.checklist.part-e-orientation-checklist', [
        'employeeNum' => $employee->employee_num,
        'jobCodeId' => $employee->currentAssignment?->position_id,
    ], key('part-e-orientation-checklist-'.$employee->employee_num))
</div>
