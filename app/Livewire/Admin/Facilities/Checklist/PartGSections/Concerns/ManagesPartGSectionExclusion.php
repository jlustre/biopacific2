<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Livewire\Concerns\GuardsAgainstSelfAssessment;
use App\Models\EmployeeCompetencyAssessment;

trait ManagesPartGSectionExclusion
{
    use GuardsAgainstSelfAssessment;
    use ManagesPartGItemReviews;
    use PersistsPartGCompetencyItemEntries;
    use PersistsPartGCompetencySectionResponses;

    public bool $sectionExcluded = false;

    protected function abortPersistIfSelfAssessment(): bool
    {
        if (! property_exists($this, 'employeeNum')) {
            return false;
        }

        return $this->evaluatorActionsDisabled;
    }

    protected function guardPartGManualDraftSave(): bool
    {
        if ($this->assessmentLocked) {
            $this->setDraftSaveFeedback('error', 'This assessment is read-only and cannot be saved.');

            return false;
        }

        if (! $this->assessmentPeriodId) {
            $this->setDraftSaveFeedback('error', 'Please select an assessment period before saving a draft.');

            return false;
        }

        if ($this->denyEvaluatorAction()) {
            return false;
        }

        return true;
    }

    public function updatedSectionExcluded(): void
    {
        if ($this->denyEvaluatorAction()) {
            $this->sectionExcluded = ! $this->sectionExcluded;

            return;
        }

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
        $snapshot = is_array($updateData['snapshot_json'] ?? null)
            ? $updateData['snapshot_json']
            : (is_array($row?->snapshot_json) ? $row->snapshot_json : []);
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

    /**
     * @param  list<array<string, mixed>>  $sectionItems
     * @return array{totalItems: int, checkedOfTotal: string, totalPoints: int|float, average: string, overallRating: string}
     */
    protected function sectionSummaryMetrics(array $sectionItems): array
    {
        if (! method_exists($this, 'calculateScores')) {
            return [
                'totalItems' => 0,
                'checkedOfTotal' => '0 of 0 rated',
                'totalPoints' => 0,
                'average' => '—',
                'overallRating' => '—',
            ];
        }

        $scores = $this->calculateScores();

        if ($this->sectionExcluded ?? false) {
            return [
                'totalItems' => 0,
                'checkedOfTotal' => '',
                'totalPoints' => $scores['totalPoints'],
                'average' => '0',
                'overallRating' => $scores['overallRating'],
            ];
        }

        $total = 0;
        $rated = 0;
        $notApplicable = 0;

        foreach ($sectionItems as $item) {
            if ($item['isParent'] ?? false) {
                continue;
            }

            $total++;
            $responses = property_exists($this, 'responses') && is_array($this->responses)
                ? $this->responses
                : [];
            $response = $responses[$item['id']] ?? null;

            if ($response === null || $response === '') {
                continue;
            }

            if ($response === 'N') {
                $notApplicable++;

                continue;
            }

            if (in_array($response, ['E', 'S', 'U'], true)) {
                $rated++;
            }
        }

        $checkedOfTotal = $notApplicable > 0
            ? $rated.' of '.$total.' rated ('.$notApplicable.' N/A)'
            : $rated.' of '.$total.' rated';

        $average = (float) ($scores['average'] ?? 0);

        return [
            'totalItems' => $total,
            'checkedOfTotal' => $checkedOfTotal,
            'totalPoints' => $scores['totalPoints'] ?? 0,
            'average' => $rated > 0 ? number_format($average, 2, '.', '') : '—',
            'overallRating' => (string) ($scores['overallRating'] ?? '—'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function resolveCompetencySectionItems(): array
    {
        foreach ([
            'emarCompetencyItems',
            'competencyItems',
            'handHygieneCompetencyItems',
            'licensedNurseCompetencyItems',
            'lnCompetencyItems',
            'pointOfCareCompetencyItems',
            'matrixcareCompetencyItems',
            'bloodAdministrationCompetencyItems',
            'bloodGlucoseCompetencyItems',
            'nurseTreatmentCompetencyItems',
            'ventilatorCompetencyItems',
            'ppeCompetencyItems',
            'medicationAdministrationCompetencyItems',
            'cnaSkillsCompetencyItems',
            'perinealCareCompetencyItems',
            'hoyerLiftCompetencyItems',
            'dsdCompetencyItems',
        ] as $property) {
            if (! property_exists($this, $property)) {
                continue;
            }

            $items = $this->{$property};
            if (is_array($items) && $items !== []) {
                return $items;
            }
        }

        return [];
    }

    protected function refreshPublishedSummaryState(): void
    {
        $sectionItems = $this->resolveCompetencySectionItems();

        if (property_exists($this, 'publishedSectionSummary') && $sectionItems !== []) {
            $this->publishedSectionSummary = $this->sectionSummaryMetrics($sectionItems);
        }

        if (! property_exists($this, 'globalSummaryTotalScore')) {
            return;
        }

        $global = $this->buildGlobalCompetencyAssessmentSummary();

        $this->globalSummaryTotalScore = (int) $global['total_score'];
        $this->globalSummaryAverageScore = number_format((float) $global['average_score'], 2, '.', '');
        $this->globalSummaryOverallRating = (string) $global['overall_rating'];
    }
}
