<?php

namespace App\Models;

use App\Support\AssessmentWorkflowStatus;
use Illuminate\Database\Eloquent\Model;

class EmployeeCompetencyAssessment extends Model
{
    protected $table = 'employee_competency_assessments';

    protected $fillable = [
        'employee_num',
        'assessment_period_id',
        'status',
        'submitted_by',
        'submitted_at',
        'total_score',
        'average_score',
        'overall_rating',
        'comments',
        'further_action_required',
        'reviewer_name',
        'reviewer_title',
        'review_date',
        'employee_name',
        'employee_title',
        'employee_signed_at',
        'reviewer_signed_at',
        'pdf_path',
        'pdf_generated_at',
        'snapshot_json',
        'completed_at',
        'responses', // <-- allow mass assignment for draft responses
        'employee_comments',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'average_score' => 'decimal:2',
        'review_date' => 'date',
        'employee_signed_at' => 'datetime',
        'reviewer_signed_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'snapshot_json' => 'array',
        'responses' => 'array',
        'completed_at' => 'datetime',
    ];

    public function period()
    {
        return $this->belongsTo(EmployeeAssessmentPeriod::class, 'assessment_period_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function workflowStatus(): string
    {
        return AssessmentWorkflowStatus::normalize((string) ($this->status ?? AssessmentWorkflowStatus::DRAFT));
    }
}