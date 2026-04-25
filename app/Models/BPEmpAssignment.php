<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpAssignment extends Model
{
    protected $table = 'bp_emp_assignments';
    protected $primaryKey = 'assign_id';
    protected $fillable = [
        'assign_id',
        'employee_num',
        'effdt',
        'effseq',
        'facility_id',
        'dept_id',
        'job_code_id',
        'reports_to_employee_num',
        'reg_temp',
        'full_part_time',
        'bargaining_unit_id',
        'union_seniority_dt',
        'created_by',
        'updated_by',
        'start_date',
        'end_date',
    ];

    // Assignment belongs to a facility
    public function facility()
    {
        return $this->belongsTo(\App\Models\Facility::class, 'facility_id', 'id');
    }

    // Assignment belongs to a department
    public function department()
    {
        return $this->belongsTo(\App\Models\BPDepartment::class, 'dept_id', 'dept_id');
    }

    // Assignment belongs to a position
    public function position()
    {
        return $this->belongsTo(\App\Models\BPPosition::class, 'job_code_id', 'position_id');
    }
}
