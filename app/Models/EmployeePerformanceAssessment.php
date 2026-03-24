<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceAssessment extends Model
{
    protected $table = 'employee_performance_assessments';
    protected $fillable = [
        'emp_id',
        'assessment_period_id',
        'items',
        'assessment_date',
        'review_dt',
        'acknowledge_dt',
        'assessed_by',
        'comments',
    ];

    public function period()
    {
        return $this->belongsTo(EmployeeAssessmentPeriod::class, 'assessment_period_id');
    }
}
