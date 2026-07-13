<?php

namespace App\Models;

use App\Support\EmployeeAssessmentPeriodCalculator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmployeeAssessmentPeriod extends Model
{
    protected $table = 'employee_assessment_periods';

    protected $fillable = [
        'employee_num',
        'period_year',
        'period_sequence',
        'date_from',
        'date_to',
        'created_by',
        'review_type',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }

    public function assessments()
    {
        return $this->hasMany(EmployeePerformanceAssessment::class, 'assessment_period_id');
    }

    public function competencyAssessments()
    {
        return $this->hasMany(EmployeeCompetencyAssessment::class, 'assessment_period_id');
    }

    public function displayDateRange(): string
    {
        return self::formatDateForDisplay($this->date_from).' to '.self::formatDateForDisplay($this->date_to);
    }

    public static function formatDateForDisplay(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    public function canBeDeleted(): bool
    {
        if (! EmployeeAssessmentPeriodCalculator::isPeriodLoadable($this)) {
            return ! $this->employeeHasSavedSubmissions();
        }

        return ! $this->employeeHasAssessmentData();
    }

    public function employeeHasSavedSubmissions(): bool
    {
        $employeeNum = (string) ($this->employee_num ?? '');
        if ($employeeNum === '') {
            return $this->assignedEmployeeNums()->isNotEmpty();
        }

        if (EmployeePerformanceAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $this->id)
            ->exists()) {
            return true;
        }

        return EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $this->id)
            ->exists()
            || EmployeeTrainingCompletion::query()
                ->where('employee_num', $employeeNum)
                ->where('assessment_period_id', $this->id)
                ->exists();
    }

    public function employeeHasAssessmentData(): bool
    {
        if ($this->employeeHasSavedSubmissions()) {
            return true;
        }

        $employeeNum = (string) ($this->employee_num ?? '');
        if ($employeeNum === '') {
            return $this->assignedEmployeeNums()->isNotEmpty();
        }

        if (EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $this->id)
            ->whereNull('revoked_at')
            ->exists()) {
            return true;
        }

        if (EmployeeTrainingCompletion::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $this->id)
            ->exists()) {
            return true;
        }

        return EmployeePerformanceSectionComment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $this->id)
            ->exists();
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
            ->merge(
                EmployeeTrainingCompletion::query()
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
