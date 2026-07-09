<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;

class CompetencyAssessmentWorkflowReadiness
{
    public static function isReadyForEmployeeConfirmation(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        return self::missingSectionLabels($assessment, $employee) === [];
    }

    /**
     * @return list<string>
     */
    public static function applicableSectionLabels(?int $positionId): array
    {
        return EmployeeCompetencyItem::query()
            ->applicableToPosition($positionId ? (int) $positionId : null)
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->map(fn ($section) => trim((string) $section))
            ->filter(fn (string $section) => $section !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function missingSectionLabels(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): array {
        $employee->loadMissing('currentAssignment');

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $applicableSections = self::applicableSectionLabels($positionId ? (int) $positionId : null);
        if ($applicableSections === []) {
            return [];
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submittedSections = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '');
        $excludedSections = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '');

        return collect($applicableSections)
            ->filter(fn (string $sectionLabel) => ! $submittedSections->contains($sectionLabel)
                && ! $excludedSections->contains($sectionLabel))
            ->values()
            ->all();
    }
}
