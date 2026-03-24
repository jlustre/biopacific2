<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAssessmentPeriod extends Model
{
    protected $table = 'employee_assessment_periods';
    protected $fillable = [
        'period_year',
        'period_sequence',
        'date_from',
        'date_to',
        'created_by',
        'review_type',
    ];

    public function assessments()
    {
        return $this->hasMany(EmployeePerformanceAssessment::class, 'assessment_period_id');
    }
}
