<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Models\User;
use App\Services\EmployeeAssessmentPeriodService;
use Illuminate\Support\Collection;

class CompetencyAssessmentHistoryResolver
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function resolveForEmployee(string $employeeNum, ?int $selectedAssessmentPeriodId = null): array
    {
        $employee = BPEmployee::with('currentAssignment.position')
            ->where('employee_num', $employeeNum)
            ->first();

        if (! $employee) {
            return [];
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $employeeCompetencyItems = EmployeeCompetencyItem::query()
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->get();

        $competencyAssessmentSubmissions = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->get()
            ->keyBy('assessment_period_id');

        $draftResponses = self::decodeDraftResponses(
            $selectedAssessmentPeriodId
                ? $competencyAssessmentSubmissions->get((int) $selectedAssessmentPeriodId)
                : null
        );

        $assessmentPeriodLabels = app(EmployeeAssessmentPeriodService::class)
            ->periodsForEmployee($employee)
            ->keyBy('id');

        $allAssessmentEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();

        $competencyEntriesByPeriod = $allAssessmentEntries
            ->filter(fn (EmployeeAssessmentItemEntry $entry) => $entry->assessment_type === 'competency')
            ->groupBy('assessment_period_id');

        $reviewerIds = $allAssessmentEntries
            ->pluck('assessed_by')
            ->merge($competencyAssessmentSubmissions->pluck('submitted_by'))
            ->filter()
            ->unique()
            ->values();

        $users = $reviewerIds->isEmpty()
            ? new Collection
            : User::query()->whereIn('id', $reviewerIds)->get();

        return CompetencyAssessmentHistoryBuilder::build(
            $employeeCompetencyItems,
            $competencyAssessmentSubmissions,
            $competencyEntriesByPeriod,
            $assessmentPeriodLabels,
            $selectedAssessmentPeriodId,
            $draftResponses,
            $users,
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    protected static function decodeDraftResponses(?EmployeeCompetencyAssessment $assessment): array
    {
        if (! $assessment?->responses) {
            return [];
        }

        $decoded = is_array($assessment->responses)
            ? $assessment->responses
            : json_decode($assessment->responses, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        if (! is_array($decoded)) {
            return [];
        }

        $draftResponses = [];

        foreach ($decoded as $itemId => $data) {
            $draftResponses[$itemId] = is_array($data)
                ? ($data['response'] ?? null)
                : $data;
        }

        return $draftResponses;
    }
}
