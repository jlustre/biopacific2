<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PreEmploymentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'first_name',
        'middle_name',
        'last_name',
        'current_address',
        'phone_number',
        'email',
        'city',
        'state',
        'zip_code',
        'county',
        'position_applied_for',
        'employment_type',
        'employment_type_other',
        'shift_preference',
        'date_available',
        'wage_salary_expected',
        'worked_here_before',
        'worked_here_when_where',
        'applied_here_before',
        'applied_here_when_where',
        'relatives_work_here',
        'relatives_details',
        'has_drivers_license',
        'drivers_license_number',
        'drivers_license_state',
        'drivers_license_expiration',
        'how_heard_about_us',
        'how_heard_other',
        'authorized_to_work_usa',
        'contact_current_employer',
        'work_experience',
        'previous_addresses',
        'education',
        'work_history_description',
        'additional_references',
        'professional_affiliations',
        'license_suspended',
        'license_suspended_explanation',
        'special_skills',
        'hired_at',
        'hired_date',
        'position_id',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_available' => 'date',
        'worked_here_before' => 'boolean',
        'applied_here_before' => 'boolean',
        'relatives_work_here' => 'boolean',
        'has_drivers_license' => 'boolean',
        'drivers_license_expiration' => 'date',
        'authorized_to_work_usa' => 'boolean',
        'contact_current_employer' => 'boolean',
        'license_suspended' => 'boolean',
        'work_experience' => 'array',
        'previous_addresses' => 'array',
        'education' => 'array',
        'hired_at' => 'datetime',
        'hired_date' => 'date',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Scope to get applications by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Copy application data to employee table when hired
     */
    public function copyToEmployee($hireData = [])
    {
        $hireDate = $hireData['hire_date'] ?? now()->toDateString();
        $positionId = $hireData['position_id'] ?? null;

        // Create or update employee record
        $employee = Employee::updateOrCreate(
            ['user_id' => $this->user_id],
            [
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'position_applied_for' => $this->position_applied_for,
                'position_id' => $positionId,
                'employment_type' => $this->employment_type,
                'employment_type_other' => $this->employment_type_other,
                'shift_preference' => $this->shift_preference,
                'date_available' => $this->date_available,
                'wage_salary_expected' => $this->wage_salary_expected,
                'worked_here_before' => $this->worked_here_before,
                'worked_here_when_where' => $this->worked_here_when_where,
                'relatives_work_here' => $this->relatives_work_here,
                'relatives_details' => $this->relatives_details,
                'has_drivers_license' => $this->has_drivers_license,
                'drivers_license_number' => $this->drivers_license_number,
                'how_heard_about_us' => $this->how_heard_about_us,
                'how_heard_other' => $this->how_heard_other,
                'authorized_to_work_usa' => $this->authorized_to_work_usa,
                'hire_date' => $hireDate,
                'status' => 'active',
            ]
        );

        // Create initial address record with effective dating
        EmployeeAddress::create([
            'employee_id' => $employee->id,
            'current_address' => $this->current_address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'county' => $this->county,
            'effective_date' => $hireDate,
            'effective_sequence' => 1,
        ]);

        // Create initial phone record with effective dating
        EmployeePhone::create([
            'employee_id' => $employee->id,
            'phone_number' => $this->phone_number,
            'phone_type' => 'primary',
            'effective_date' => $hireDate,
            'effective_sequence' => 1,
        ]);

        return $employee;
    }
}

