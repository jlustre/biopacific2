<?php

namespace App\Helpers;

use App\Models\BPEmployee;
use App\Models\BPEmpAssignment;
use App\Models\BPEmpAddress;
use App\Models\BPEmpPhone;

class EmployeeHelper {
    /**
     * Get all employees assigned to a facility, with address and phone info.
     *
     * @param int|string $facilityId
     * @return array
     */
    public static function getAllEmployeesByFacility($facilityId)
    {
        $employees = BPEmployee::whereHas('assignments', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })
        ->with(['assignments', 'address', 'phone'])
        ->orderBy('last_name')
        ->get();

        return $employees->map(function($employee) {
            return [
                'id' => $employee->id,
                'employee_num' => $employee->employee_num,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'assignments' => $employee->assignments,
                'address' => $employee->address,
                'phone' => $employee->phone,
            ];
        })->toArray();
    }

    /**
     * Get detailed employee info including assignments, address, and phone.
     *
     * @param string $empId
     * @return array|null
     */
    public static function getEmployeeInfo($empId)
    {
        $employee = BPEmployee::with([
            'assignments',
            'address',
            'phone',
        ])->find($empId);

        if (!$employee) {
            return null;
        }

        return [
            'id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'assignments' => $employee->assignments,
            'address' => $employee->address,
            'phone' => $employee->phone,
        ];
    }
}
