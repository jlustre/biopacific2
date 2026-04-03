<?php

namespace App\Livewire\Admin\Facilities;

use Livewire\Component;
use App\Models\Facility;
use App\Models\BPEmployee;

class FacilityEmployeeSelect extends Component
{
    public $facility_id;
    public $employee_id;
    public $employees = [];

    public function mount($facilityId = null, $employeeId = null)
    {
        $this->facility_id = $facilityId;
        $this->employee_id = $employeeId;
        $this->updateEmployees();
    }

    public function updatedFacilityId($value)
    {
        $this->facility_id = $value;
        $this->employee_id = '';
        $this->updateEmployees();
    }

    public function updateEmployees()
    {
        if ($this->facility_id) {
            $this->employees = BPEmployee::whereHas('assignments', function($q) {
                $q->where('facility_id', $this->facility_id);
            })
            ->orderBy('last_name')
            ->get();
        } else {
            $this->employees = collect();
        }
    }

    public function render()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('livewire.admin.facilities.facility-employee-select', [
            'facilities' => $facilities,
            'employees' => $this->employees,
        ]);
    }
}

