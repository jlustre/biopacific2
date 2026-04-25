<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmployee extends Model
{
    // Relationship: Employee has one phone (primary)
    public function phone()
    {
        return $this->hasOne(\App\Models\BPEmpPhone::class, 'employee_num', 'employee_num')->where('is_primary', 1);
    }

    // Relationship: Employee has one address
    public function address()
    {
        return $this->hasOne(\App\Models\BPEmpAddress::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee belongs to a user
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
    protected $table = 'bp_employees';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'employee_num',
        'ssn',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'assignment_id',
        'dob',
        'original_hire_dt',
        'marital_status_id',
        'ethnic_group_id',
        'military_status_id',
        'citizenship_status_id',
        // Newly added columns
        'action_id',
        'hourly_status_id',
        'std_hrs_week',
        'federal_tax_data_id',
        'state_tax_data_id',
        'local_tax_data_id',
        'compensation_rate_id',
        'amount',
        'union_code',
        'effdt_of_membership',
        'email',
    ];

    // Relationship: Employee has one phone (primary)
    // Relationship: Employee has many uploads
    public function uploads()
    {
        return $this->hasMany(\App\Models\Upload::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee has many assignments
    public function assignments()
    {
        return $this->hasMany(\App\Models\BPEmpAssignment::class, 'employee_num', 'employee_num');
    }

    /**
     * Get the current (most recent) assignment from bp_emp_assignments
     */
    public function currentAssignment()
    {
        // Always get the latest assignment by effdt and effseq
        return $this->hasOne(\App\Models\BPEmpAssignment::class, 'employee_num', 'employee_num')
            ->latestOfMany(['effdt', 'effseq']);
    }

    // Attribute accessor for current assignment instance
    public function getCurrentAssignmentAttribute()
    {
        // Use the loaded relation if available, otherwise query
        return $this->getRelationValue('currentAssignment') ?? $this->currentAssignment()->first();
    }

    public function getCurrentPositionAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('position') || isset($assignment->position))) {
            return $assignment->position;
        }
        return null;
    }

    public function getCurrentDepartmentAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('department') || isset($assignment->department))) {
            return $assignment->department;
        }
        return null;
    }

    public function getCurrentFacilityAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('facility') || isset($assignment->facility))) {
            return $assignment->facility;
        }
        return null;
    }

    public function getCurrentUnionStatusAttribute()
    {
        $assignment = $this->current_assignment;
        return $assignment && $assignment->bargaining_unit_id ? true : false;
    }


    /**
     * Get the current union status via the latest assignment
     * Returns true if the assignment has a bargaining_unit_id, false otherwise
     */
    public function currentUnionStatus()
    {
        $assignment = $this->currentAssignment;
        return $assignment && $assignment->bargaining_unit_id ? true : false;
    }

    // Relationship: Employee has many phones
    public function phones()
    {
        return $this->hasMany(\App\Models\BPEmpPhone::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee has many addresses
    public function addresses()
    {
        return $this->hasMany(\App\Models\BPEmpAddress::class, 'employee_num', 'employee_num');
    }
}
