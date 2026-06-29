<?php

use Illuminate\Http\Request;
use App\Models\Employee;

use App\Models\BPEmployee;

// Existing route for /admin/facility/{facility}/employees (kept for compatibility)
Route::get('/admin/facility/{facility}/employees', function ($facilityId, Request $request) {
    $employees = BPEmployee::whereHas('assignments', function($q) use ($facilityId) {
        $q->where('facility_id', $facilityId);
    })
    ->orderedByName()
    ->get(['employee_num as id', 'employee_num', 'first_name', 'middle_name', 'last_name'])
    ->map(fn (BPEmployee $employee) => array_merge($employee->only(['id', 'employee_num', 'first_name', 'middle_name', 'last_name']), [
        'name' => $employee->formalName(),
    ]));
    return response()->json($employees);
})->name('admin.facility.employees.ajax');

// New route for /admin/facility/{facility}/employees/all to match frontend AJAX call
Route::get('/admin/facility/{facility}/employees/all', function ($facilityId, Request $request) {
    $employees = BPEmployee::whereHas('assignments', function($q) use ($facilityId) {
        $q->where('facility_id', $facilityId);
    })
    ->orderedByName()
    ->get(['employee_num as id', 'employee_num', 'first_name', 'middle_name', 'last_name'])
    ->map(fn (BPEmployee $employee) => array_merge($employee->only(['id', 'employee_num', 'first_name', 'middle_name', 'last_name']), [
        'name' => $employee->formalName(),
    ]));
    return response()->json($employees);
});
