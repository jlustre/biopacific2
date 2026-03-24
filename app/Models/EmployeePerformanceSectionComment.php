<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceSectionComment extends Model
{
    protected $table = 'employee_performance_section_comments';
    protected $fillable = [
        'emp_id',
        'assessment_period_id',
        'doc_type_id',
        'comment',
    ];
}
