<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpJobData extends Model
{
    protected $table = 'bp_emp_job_data';

    protected $primaryKey = 'assign_id';

    protected $fillable = [
        'assign_id',
        'employee_num',
        'effdt',
        'effseq',
        'facility_id',
        'dept_id',
        'position_id',
        'reports_to',
        'reg_temp',
        'full_part_time',
        'hourly_status_id',
        'std_hrs_week',
        'compensation_rate_id',
        'amount',
        'bargaining_unit_id',
        'union_seniority_dt',
        'created_by',
        'updated_by',
        'start_date',
        'end_date',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    protected $casts = [
        'effdt' => 'date',
        'effseq' => 'integer',
        'std_hrs_week' => 'integer',
        'amount' => 'decimal:2',
        'union_seniority_dt' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function hourlyStatus()
    {
        return $this->belongsTo(SelectOption::class, 'hourly_status_id');
    }

    public function compensationRate()
    {
        return $this->belongsTo(SelectOption::class, 'compensation_rate_id');
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
        if (!$position && $this->position_id) {
            $position = $this->position()->with('reportsToPosition')->first();
        } elseif ($position && !$position->relationLoaded('reportsToPosition')) {
            $position->load('reportsToPosition');
        }

        return $position?->reportsToPosition?->title;
    }
}
