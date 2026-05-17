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
        'reports_to',
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
        return $this->belongsTo(\App\Models\Department::class, 'dept_id', 'id');
    }

    // Assignment belongs to a position
    public function position()
    {
        return $this->belongsTo(\App\Models\Position::class, 'job_code_id', 'id');
    }

    public function reportsToPosition()
    {
        return $this->belongsTo(Position::class, 'reports_to', 'id');
    }

    /**
     * Supervisory position title from the employee's position row (positions.reports_to_position_id).
     */
    public function reportsToPositionTitle(): ?string
    {
        $position = $this->position;
        if (!$position && $this->job_code_id) {
            $position = $this->position()->with('reportsToPosition')->first();
        } elseif ($position && !$position->relationLoaded('reportsToPosition')) {
            $position->load('reportsToPosition');
        }

        return $position?->reportsToPosition?->title;
    }
}
