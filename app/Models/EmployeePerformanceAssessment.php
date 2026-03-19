<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceAssessment extends Model
{
    protected $table = 'employee_performance_assessments';
    protected $fillable = [
        'emp_id',
        'items',
        'assessment_date',
        'assessed_by',
        'comments',
        'eff_date', // Effective date for assessment period/history
    ];
}
