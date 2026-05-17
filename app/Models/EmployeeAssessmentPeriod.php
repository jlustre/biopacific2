<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    public function competencyAssessments()
    {
        return $this->hasMany(EmployeeCompetencyAssessment::class, 'assessment_period_id');
    }

    /**
     * Distinct employee numbers with any performance, competency, item, or section data for this period.
     */
    public function assignedEmployeeNums(): Collection
    {
        $periodId = $this->id;

        return $this->assessments()
            ->pluck('employee_num')
            ->merge($this->competencyAssessments()->pluck('employee_num'))
            ->merge(
                EmployeeAssessmentItemEntry::query()
                    ->where('assessment_period_id', $periodId)
                    ->distinct()
                    ->pluck('employee_num')
            )
            ->merge(
                EmployeePerformanceSectionComment::query()
                    ->where('assessment_period_id', $periodId)
                    ->distinct()
                    ->pluck('employee_num')
            )
            ->filter()
            ->unique()
            ->values();
    }

    public function hasAssignedEmployees(): bool
    {
        return $this->assignedEmployeeNums()->isNotEmpty();
    }
}
