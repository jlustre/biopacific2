<?php

use Illuminate\Http\Request;
use App\Models\Employee;

use App\Models\BPEmployee;
Route::get('/admin/facility/{facility}/employees', function ($facilityId, Request $request) {
    $employees = BPEmployee::whereHas('assignments', function($q) use ($facilityId) {
        $q->where('facility_id', $facilityId);
    })
    ->orderBy('last_name')
    ->get(['emp_id as id', 'emp_id', 'first_name', 'middle_name', 'last_name']);
    return response()->json($employees);
})->name('admin.facility.employees.ajax');
