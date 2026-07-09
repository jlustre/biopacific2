<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Support\PartGCompetencyScoring;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

trait PersistsPartGCompetencyItemEntries
{
    /**
     * @param  array<int|string, mixed>  $responses
     */
    protected function syncCompetencyItemEntriesFromResponses(array $responses): void
    {
        if (! $this->assessmentPeriodId || $responses === []) {
            return;
        }

        $assessmentDate = now()->toDateString();
        $assessedBy = Auth::id();

        foreach ($responses as $itemId => $response) {
            $rating = is_array($response)
                ? ($response['response'] ?? null)
                : $response;

            if (! is_string($rating) || $rating === '') {
                continue;
            }

            $rating = PartGCompetencyScoring::normalizeItemRating($rating);
            if ($rating === null) {
                continue;
            }

            $sourceItemId = $this->normalizeCompetencyResponseKey($itemId);
            if ($sourceItemId <= 0) {
                continue;
            }

            $this->createCompetencyItemEntryIfChanged(
                $sourceItemId,
                $rating,
                $assessmentDate,
                $assessedBy
            );
        }
    }

    protected function createCompetencyItemEntryIfChanged(
        int $sourceItemId,
        string $rating,
        string $assessmentDate,
        ?int $assessedBy,
    ): void {
        $latest = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->where('source_item_id', $sourceItemId)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->first();

        if ($latest && PartGCompetencyScoring::normalizeItemRating((string) $latest->rating) === PartGCompetencyScoring::normalizeItemRating($rating)) {
            return;
        }

        EmployeeAssessmentItemEntry::query()->create([
            'employee_num' => $this->employeeNum,
            'assessment_period_id' => $this->assessmentPeriodId,
            'assessment_type' => 'competency',
            'item_key' => 'G_'.$sourceItemId,
            'item_label' => $this->competencyItemLabelFor($sourceItemId),
            'source_item_id' => $sourceItemId,
            'rating' => $rating,
            'assessment_date' => $assessmentDate,
            'assessed_by' => $assessedBy,
            'comments' => null,
        ]);
    }

    protected function competencyItemLabelFor(int $sourceItemId): ?string
    {
        $item = EmployeeCompetencyItem::query()->find($sourceItemId);

        if (! $item) {
            return null;
        }

        return ltrim((string) $item->item, '-');
    }

    /**
     * @param  list<array<string, mixed>>  $sectionItems
     */
    protected function hydrateSectionResponsesFromStorage(array $sectionItems): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $sectionItemIds = collect($sectionItems)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($sectionItemIds === []) {
            return;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if ($assessment) {
            if (method_exists($this, 'hydrateReviewerIdentityFromAssessment')) {
                $this->hydrateReviewerIdentityFromAssessment($assessment);
            }

            $decodedResponses = $this->decodeCompetencyResponses($assessment->responses ?? null);

            if (method_exists($this, 'hydrateItemReviewMetaFromPayload')) {
                $this->hydrateItemReviewMetaFromPayload($decodedResponses);
            }

            foreach ($decodedResponses as $itemId => $data) {
                $sourceItemId = $this->normalizeCompetencyResponseKey($itemId);
                if (! in_array($sourceItemId, $sectionItemIds, true)) {
                    continue;
                }

                $response = is_array($data)
                    ? ($data['response'] ?? null)
                    : $data;

                if ($response !== null && $response !== '') {
                    $this->responses[$sourceItemId] = strtoupper((string) $response);
                }
            }

            if (method_exists($this, 'loadSectionCommentsFromAssessment')) {
                $this->loadSectionCommentsFromAssessment($assessment);
            } else {
                $comments = app(\App\Services\CompetencySectionWorkflowService::class)
                    ->resolveSectionComments($assessment, static::SECTION);
                $this->summaryComments = $comments['reviewer_comments'];
                $this->employeeComments = $comments['employee_comments'];
                $this->reviewSignDate = '';
                $this->employeeSignDate = '';
            }

            if (method_exists($this, 'loadSectionExcludedFromAssessment')) {
                $this->loadSectionExcludedFromAssessment($assessment);
            }
        }

        $latestEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereIn('source_item_id', $sectionItemIds)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('source_item_id')
            ->map(fn ($entries) => $entries->first());

        foreach ($latestEntries as $entry) {
            $sourceItemId = (int) $entry->source_item_id;
            if ($sourceItemId <= 0) {
                continue;
            }

            $rating = strtoupper(trim((string) $entry->rating));
            if (PartGCompetencyScoring::isValidItemRating($rating)) {
                $this->responses[$sourceItemId] = $rating;
            }
        }

        if (method_exists($this, 'initializeReturnedSectionResubmitState')) {
            $this->initializeReturnedSectionResubmitState();
        }
    }

    /**
     * @param  array<int|string, mixed>|null  $responseOverlay
     * @return array{total_score: int, average_score: float, overall_rating: string}
     */
    protected function buildGlobalCompetencyAssessmentSummary(?array $responseOverlay = null): array
    {
        if ($responseOverlay === null && property_exists($this, 'responses') && is_array($this->responses)) {
            $responseOverlay = $this->responses;
        }

        if (! $this->assessmentPeriodId) {
            return PartGCompetencyScoring::summarize($responseOverlay ?? []);
        }

        $latestEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (EmployeeAssessmentItemEntry $entry) => (int) $entry->source_item_id)
            ->map(fn ($entries) => $entries->first());

        $ratings = [];
        foreach ($latestEntries as $entry) {
            $sourceItemId = (int) $entry->source_item_id;
            if ($sourceItemId <= 0) {
                continue;
            }

            $ratings[$sourceItemId] = strtoupper((string) $entry->rating);
        }

        if ($responseOverlay !== null && $responseOverlay !== []) {
            $ratings = $this->mergeCompetencyRatingsOverlay($ratings, $responseOverlay);
        }

        return PartGCompetencyScoring::summarize($ratings);
    }

    /**
     * @param  array<int|string, string>  $ratings
     * @param  array<int|string, mixed>  $overlay
     * @return array<int|string, string>
     */
    protected function mergeCompetencyRatingsOverlay(array $ratings, array $overlay): array
    {
        foreach ($overlay as $itemId => $response) {
            $rating = is_array($response)
                ? ($response['response'] ?? null)
                : $response;

            if (! is_string($rating) || $rating === '') {
                continue;
            }

            $rating = PartGCompetencyScoring::normalizeItemRating($rating);
            if ($rating === null) {
                continue;
            }

            $sourceItemId = $this->normalizeCompetencyResponseKey($itemId);
            if ($sourceItemId <= 0) {
                continue;
            }

            $ratings[$sourceItemId] = $rating;
        }

        return $ratings;
    }

    protected function syncGlobalCompetencyAssessmentSummary(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $summary = $this->buildGlobalCompetencyAssessmentSummary();

        EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->update([
                'total_score' => $summary['total_score'],
                'average_score' => $summary['average_score'],
                'overall_rating' => $summary['overall_rating'],
            ]);
    }

    protected function normalizeCompetencyResponseKey(mixed $itemId): int
    {
        if (is_int($itemId)) {
            return $itemId;
        }

        $key = trim((string) $itemId);
        if ($key === '') {
            return 0;
        }

        if (preg_match('/^G[_-]?(\d+)$/i', $key, $matches)) {
            return (int) $matches[1];
        }

        return (int) $key;
    }

    protected function dispatchPartGSummaryUpdated(): void
    {
        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        if ($this->assessmentPeriodId) {
            $this->syncGlobalCompetencyAssessmentSummary();
        }

        $summary = $this->buildGlobalCompetencyAssessmentSummary();

        $payload = [
            'totalScore' => $summary['total_score'],
            'averageScore' => $summary['average_score'],
            'overallRating' => $summary['overall_rating'],
        ];

        $this->dispatch(
            'partg-summary-updated',
            totalScore: $payload['totalScore'],
            averageScore: $payload['averageScore'],
            overallRating: $payload['overallRating'],
        );

        $this->js('window.updatePartGSummaryScores && window.updatePartGSummaryScores('.Js::from($payload).')');
    }

    public function updatedResponses(mixed $value = null, ?string $key = null): void
    {
        if ($this->sectionItemReviewsLocked()) {
            return;
        }

        if ($this->denyEvaluatorAction()) {
            return;
        }

        if ($key !== null && $value !== null && $value !== '') {
            $normalizedRating = strtoupper(trim((string) $value));
            if (PartGCompetencyScoring::isValidItemRating($normalizedRating)) {
                $this->responses[(int) $key] = $normalizedRating;
            }
        }

        if (method_exists($this, 'normalizeResponseKeys')) {
            $this->normalizeResponseKeys();
        }

        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        $this->dispatchSectionResponsesUpdated();
        $this->dispatchPartGSummaryUpdated();

        if (method_exists($this, 'persistDraftIfPossible')) {
            $this->persistDraftIfPossible();
        }
    }

    public function setResponse(int $itemId, string $rating): void
    {
        if ($this->sectionItemReviewsLocked()) {
            return;
        }

        if ($this->denyEvaluatorAction()) {
            return;
        }

        $rating = strtoupper(trim($rating));
        if (! PartGCompetencyScoring::isValidItemRating($rating)) {
            return;
        }

        $this->responses[$itemId] = $rating;

        if (method_exists($this, 'normalizeResponseKeys')) {
            $this->normalizeResponseKeys();
        }

        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        $this->dispatchSectionResponsesUpdated();
        $this->dispatchPartGSummaryUpdated();

        if (method_exists($this, 'persistDraftIfPossible')) {
            $this->persistDraftIfPossible();
        }
    }

    protected function dispatchSectionResponsesUpdated(): void
    {
        if (! property_exists($this, 'responses')) {
            return;
        }

        $eventMap = [
            'HandHygieneCompetencySkills' => 'hhc-responses-updated',
            'LicensedNurseCompetencySkills' => 'lnc-responses-updated',
            'LicensedNurseEmarCompetency' => 'lnemar-responses-updated',
            'LicensedNursePointOfCareCompetency' => 'lnpoc-responses-updated',
            'MatrixcarePhysicianOrderDocumentationCompetency' => 'mcpd-responses-updated',
            'BloodAdministrationCompetency' => 'bac-responses-updated',
            'BloodGlucoseSystemSkillsCompetency' => 'bgs-responses-updated',
            'NurseTreatmentSkillsCompetency' => 'nts-responses-updated',
            'VentilatorManagementSkillsCompetency' => 'vmc-responses-updated',
            'PersonalProtectiveEquipmentCompetency' => 'ppe-responses-updated',
            'MedicationAdministrationCompetency' => 'mac-responses-updated',
            'CnaSkillsChecklistCompetency' => 'csc-responses-updated',
            'PerinealCareCompetency' => 'pcc-responses-updated',
            'UseOfHoyerLiftTrainingCompetency' => 'hlt-responses-updated',
            'DirectorOfStaffDevelopmentCompetency' => 'dsd-responses-updated',
            'TracheostomyCareCompetency' => 'trach-responses-updated',
        ];

        $classBase = class_basename(static::class);
        if (! isset($eventMap[$classBase])) {
            return;
        }

        $this->dispatch(
            $eventMap[$classBase],
            responses: (method_exists($this, 'itemReviewsVisibleToCurrentUser') && ! $this->itemReviewsVisibleToCurrentUser())
                ? []
                : $this->responses,
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function decodeCompetencyResponses(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
