<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeCompetencyAssessment;

trait ManagesPartGSectionExclusion
{
    public bool $sectionExcluded = false;

    public function updatedSectionExcluded(): void
    {
        if (method_exists($this, 'persistDraftIfPossible')) {
            $this->persistDraftIfPossible();

            return;
        }

        if (method_exists($this, 'persistAssessment') && ! $this->assessmentLocked && $this->assessmentPeriodId) {
            $this->persistAssessment('draft');
        }
    }

    protected function loadSectionExcludedFromAssessment(?EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment) {
            return;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $excluded = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => (string) $label)
            ->all();

        $this->sectionExcluded = in_array(static::SECTION, $excluded, true);
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function withExcludedSnapshot(array $updateData, ?EmployeeCompetencyAssessment $row): array
    {
        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        $updateData['snapshot_json'] = $this->applySectionExclusionToSnapshot($snapshot);

        return $updateData;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    protected function applySectionExclusionToSnapshot(array $snapshot): array
    {
        $snapshot['excluded_section_labels'] = $this->buildExcludedSectionLabels($snapshot);

        return $snapshot;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return list<string>
     */
    protected function buildExcludedSectionLabels(array $snapshot): array
    {
        $labels = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn ($label) => $label !== '' && $label !== static::SECTION)
            ->values()
            ->all();

        if ($this->sectionExcluded) {
            $labels[] = static::SECTION;
        }

        return array_values(array_unique($labels));
    }

    /**
     * @return array{totalPoints: int|float, average: int|float, overallRating: string}
     */
    protected function sectionExcludedScores(): array
    {
        return [
            'totalPoints' => 0,
            'average' => 0,
            'overallRating' => 'Excluded',
        ];
    }
}
