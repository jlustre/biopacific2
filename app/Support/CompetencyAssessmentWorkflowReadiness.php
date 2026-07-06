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
        $employee->loadMissing('currentAssignment');

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $applicableSections = EmployeeCompetencyItem::query()
            ->applicableToPosition($positionId ? (int) $positionId : null)
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->map(fn ($section) => trim((string) $section))
            ->filter(fn (string $section) => $section !== '')
            ->unique()
            ->values()
            ->all();

        if ($applicableSections === []) {
            return false;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submittedSections = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '');
        $excludedSections = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '');

        foreach ($applicableSections as $sectionLabel) {
            if ($submittedSections->contains($sectionLabel) || $excludedSections->contains($sectionLabel)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
