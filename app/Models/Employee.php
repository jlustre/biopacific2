<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'position_applied_for',
        'employment_type',
        'employment_type_other',
        'shift_preference',
        'date_available',
        'wage_salary_expected',
        'worked_here_before',
        'worked_here_when_where',
        'relatives_work_here',
        'relatives_details',
        'has_drivers_license',
        'drivers_license_number',
        'how_heard_about_us',
        'how_heard_other',
        'authorized_to_work_usa',
    ];

    protected $casts = [
        'worked_here_before' => 'boolean',
        'relatives_work_here' => 'boolean',
        'has_drivers_license' => 'boolean',
        'authorized_to_work_usa' => 'boolean',
        'date_available' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(EmployeeAddress::class)
            ->orderBy('effective_date', 'desc')
            ->orderBy('effective_sequence', 'desc');
    }

    public function phones()
    {
        return $this->hasMany(EmployeePhone::class)
            ->orderBy('effective_date', 'desc')
            ->orderBy('effective_sequence', 'desc');
    }

    /**
     * Get the current (most recent) address
     */
    public function currentAddress()
    {
        return $this->hasOne(EmployeeAddress::class)
            ->latestOfMany(['effective_date', 'effective_sequence']);
    }

    /**
     * Get the current (most recent) phone
     */
    public function currentPhone()
    {
        return $this->hasOne(EmployeePhone::class)
            ->latestOfMany(['effective_date', 'effective_sequence']);
    }

    public function positions()
    {
        return $this->hasMany(EmployeePosition::class)
            ->orderBy('effective_date', 'desc')
            ->orderBy('effective_sequence', 'desc');
    }

    public function pays()
    {
        return $this->hasMany(EmployeePay::class)
            ->orderBy('effective_date', 'desc')
            ->orderBy('effective_sequence', 'desc');
    }

    /**
     * Get the current (most recent) position
     */
    public function currentPosition()
    {
        return $this->hasOne(EmployeePosition::class)
            ->latestOfMany(['effective_date', 'effective_sequence']);
    }

    /**
     * Get the current (most recent) pay
     */
    public function currentPay()
    {
        return $this->hasOne(EmployeePay::class)
            ->latestOfMany(['effective_date', 'effective_sequence']);
    }
}
