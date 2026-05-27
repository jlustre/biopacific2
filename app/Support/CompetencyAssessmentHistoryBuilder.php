<?php

namespace App\Support;

use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Collection;

class CompetencyAssessmentHistoryBuilder
{
    /**
     * @param  Collection<int, EmployeeCompetencyItem>  $employeeCompetencyItems
     * @param  Collection<int, EmployeeCompetencyAssessment>  $competencyAssessmentSubmissions
     * @param  Collection<int|string, Collection<int, EmployeeAssessmentItemEntry>>  $competencyEntriesByPeriod
     * @param  Collection<int|string, mixed>  $assessmentPeriodLabels
     * @param  array<int|string, mixed>  $draftResponses
     * @param  Collection<int, \App\Models\User>  $users
     * @return list<array<string, mixed>>
     */
    public static function build(
        Collection $employeeCompetencyItems,
        Collection $competencyAssessmentSubmissions,
        Collection $competencyEntriesByPeriod,
        Collection $assessmentPeriodLabels,
        ?int $selectedAssessmentPeriodId,
        array $draftResponses = [],
        Collection $users = new Collection,
    ): array {
        $itemsById = $employeeCompetencyItems->keyBy('id');
        $itemsBySection = $employeeCompetencyItems->groupBy('section');

        $periodIds = $competencyEntriesByPeriod->keys()
            ->merge($competencyAssessmentSubmissions->keys())
            ->unique()
            ->values();

        $rows = [];

        foreach ($periodIds as $assessmentPeriodId) {
            $assessmentPeriodId = (int) $assessmentPeriodId;
            $submission = $competencyAssessmentSubmissions->get($assessmentPeriodId);
            $snapshot = is_array($submission?->snapshot_json) ? $submission->snapshot_json : [];
            $sectionSummaries = is_array($snapshot['section_summaries'] ?? null) ? $snapshot['section_summaries'] : [];
            $submittedLabels = collect($snapshot['submitted_section_labels'] ?? [])
                ->map(fn ($label) => trim((string) $label))
                ->filter()
                ->values()
                ->all();
            $excludedLabels = collect($snapshot['excluded_section_labels'] ?? [])
                ->map(fn ($label) => trim((string) $label))
                ->filter()
                ->values()
                ->all();

            $entries = $competencyEntriesByPeriod->get($assessmentPeriodId, collect())
                ->filter(fn (EmployeeAssessmentItemEntry $entry) => $entry->revoked_at === null);

            $sectionsWithEntries = $entries
                ->map(fn (EmployeeAssessmentItemEntry $entry) => $itemsById->get((int) $entry->source_item_id)?->section)
                ->filter()
                ->unique()
                ->values();

            $sectionLabels = collect($submittedLabels)
                ->merge($sectionsWithEntries)
                ->merge(array_keys($sectionSummaries));

            if (self::snapshotHasTracheostomyActivity($snapshot)) {
                $sectionLabels->push('TRACHEOSTOMY CARE');
            }

            $sectionLabels = $sectionLabels
                ->map(fn ($label) => trim((string) $label))
                ->filter(fn (string $label) => $label !== '')
                ->unique()
                ->filter(fn (string $label) => ! in_array($label, $excludedLabels, true))
                ->values();

            foreach ($sectionLabels as $sectionLabel) {
                $sectionItems = $itemsBySection->get($sectionLabel, collect());
                $sectionItemIds = $sectionItems->pluck('id')->map(fn ($id) => (int) $id)->all();
                $totalRateableItems = self::countRateableSectionItems($sectionItems);

                if ($sectionItemIds === []) {
                    continue;
                }

                $latestStates = $entries
                    ->filter(fn (EmployeeAssessmentItemEntry $entry) => in_array((int) $entry->source_item_id, $sectionItemIds, true))
                    ->groupBy('source_item_id')
                    ->map(function (Collection $groupedEntries) {
                        return $groupedEntries
                            ->sortByDesc(fn (EmployeeAssessmentItemEntry $entry) => sprintf(
                                '%s-%010d',
                                optional($entry->assessment_date)->toDateString() ?? '',
                                $entry->id
                            ))
                            ->first();
                    })
                    ->filter();

                $useDraft = (int) $assessmentPeriodId === (int) $selectedAssessmentPeriodId && $draftResponses !== [];
                $summary = is_array($sectionSummaries[$sectionLabel] ?? null) ? $sectionSummaries[$sectionLabel] : null;
                $isSubmitted = in_array($sectionLabel, $submittedLabels, true);
                $tracheostomyMetrics = $sectionLabel === 'TRACHEOSTOMY CARE'
                    ? self::tracheostomyMetricsFromSnapshot($snapshot, $sectionItems)
                    : null;

                if ($summary) {
                    $total = (int) ($summary['total_score'] ?? 0);
                    $average = (float) ($summary['average_score'] ?? 0);
                    $overall = (string) ($summary['overall_rating'] ?? 'N/A');
                    $count = self::countRatedItems($latestStates, $draftResponses, $sectionItemIds, $useDraft && ! $isSubmitted);

                    if ($tracheostomyMetrics !== null && $tracheostomyMetrics['count'] > $count) {
                        $count = $tracheostomyMetrics['count'];
                        $total = $tracheostomyMetrics['total'];
                        $average = $tracheostomyMetrics['average'];
                        $overall = $tracheostomyMetrics['overall'];
                    }

                    $assessmentDate = self::normalizeDate($summary['review_date'] ?? null)
                        ?? self::normalizeDate($summary['submitted_at'] ?? null)
                        ?? optional($submission?->submitted_at)->toDateString()
                        ?? optional($submission?->updated_at)->toDateString()
                        ?? optional(optional($latestStates->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))->first())->assessment_date)->toDateString();
                    $status = self::resolveSectionStatus($submission?->status, $isSubmitted);
                } else {
                    if ($tracheostomyMetrics !== null && $tracheostomyMetrics['count'] > 0) {
                        $total = $tracheostomyMetrics['total'];
                        $average = $tracheostomyMetrics['average'];
                        $overall = $tracheostomyMetrics['overall'];
                        $count = $tracheostomyMetrics['count'];
                    } else {
                        $metrics = self::calculateSectionMetrics(
                            $latestStates,
                            $draftResponses,
                            $sectionItemIds,
                            $useDraft
                        );
                        $total = $metrics['total'];
                        $average = $metrics['average'];
                        $overall = $metrics['overall'];
                        $count = $metrics['count'];

                        if ($count === 0) {
                            continue;
                        }
                    }

                    $assessmentDate = optional($submission?->updated_at)->toDateString()
                        ?? optional(optional($latestStates->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))->first())->assessment_date)->toDateString()
                        ?? now()->toDateString();
                    $status = self::resolveSectionStatus($submission?->status, $isSubmitted);
                }

                $period = $assessmentPeriodLabels->get($assessmentPeriodId);
                $reviewerName = self::resolveReviewerName($submission, $snapshot, $latestStates, $users);

                $rows[] = [
                    'assessment_period_id' => $assessmentPeriodId,
                    'period_label' => $period instanceof EmployeeAssessmentPeriod
                        ? $period->displayDateRange()
                        : ('Period #'.$assessmentPeriodId),
                    'competency_name' => $sectionLabel,
                    'competency_section' => $sectionLabel,
                    'assessment_date' => self::normalizeDate($assessmentDate) ?? '',
                    'reviewer_name' => $reviewerName,
                    'items_count' => $count,
                    'total_items' => $tracheostomyMetrics !== null
                        ? ($tracheostomyMetrics['total_items'] ?? $totalRateableItems)
                        : $totalRateableItems,
                    'total_score' => $total,
                    'average_score' => number_format($average, 2, '.', ''),
                    'overall_rating' => $overall,
                    'status' => $status,
                    'competency_assessment_id' => $submission?->id,
                    'can_view_pdf' => $submission !== null && $count > 0,
                ];
            }
        }

        return collect($rows)
            ->groupBy(fn (array $row) => sprintf(
                '%s|%s',
                (int) ($row['assessment_period_id'] ?? 0),
                trim((string) ($row['competency_section'] ?? $row['competency_name'] ?? ''))
            ))
            ->map(function (Collection $groupedRows) {
                return $groupedRows
                    ->sortByDesc(fn (array $row) => sprintf(
                        '%010d-%s',
                        (int) ($row['items_count'] ?? 0),
                        $row['assessment_date'] ?? ''
                    ))
                    ->first();
            })
            ->sortByDesc(fn (array $row) => sprintf('%s-%s', $row['assessment_date'] ?? '', $row['competency_name'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, EmployeeCompetencyItem>  $sectionItems
     */
    protected static function countRateableSectionItems(Collection $sectionItems): int
    {
        $ordered = $sectionItems->values();
        $rateable = 0;

        foreach ($ordered as $index => $item) {
            $indentLevel = 0;
            if (preg_match('/^(-+)/', (string) $item->item, $matches)) {
                $indentLevel = strlen($matches[1]);
            }

            $isParent = false;
            if (isset($ordered[$index + 1])) {
                $next = $ordered[$index + 1];
                if (preg_match('/^(-+)/', (string) $next->item, $nextMatches)) {
                    $isParent = strlen($nextMatches[1]) > $indentLevel;
                }
            }

            if (! $isParent) {
                $rateable++;
            }
        }

        return $rateable;
    }

    /**
     * @param  Collection<int|string, EmployeeAssessmentItemEntry>  $latestStates
     * @param  array<int|string, mixed>  $draftResponses
     * @param  list<int>  $sectionItemIds
     */
    protected static function calculateSectionMetrics(
        Collection $latestStates,
        array $draftResponses,
        array $sectionItemIds,
        bool $useDraft,
    ): array {
        $total = 0;
        $count = 0;

        foreach ($sectionItemIds as $itemId) {
            $rating = null;

            if ($useDraft) {
                $draftRating = $draftResponses[$itemId] ?? $draftResponses[(string) $itemId] ?? null;
                if (is_array($draftRating)) {
                    $draftRating = $draftRating['response'] ?? null;
                }
                $rating = $draftRating;
            }

            if (($rating === null || $rating === '') && $latestStates->has($itemId)) {
                $rating = $latestStates->get($itemId)?->rating;
            }

            $score = self::ratingToScore($rating);
            if ($score === null) {
                continue;
            }

            $total += $score;
            $count++;
        }

        $average = $count > 0 ? round($total / $count, 2) : 0;
        $overall = $count === 0
            ? 'N/A'
            : ($average >= 2.5
                ? 'Excellent'
                : ($average >= 1.5 ? 'Satisfactory' : 'Unsatisfactory'));

        return [
            'total' => $total,
            'average' => $average,
            'overall' => $overall,
            'count' => $count,
        ];
    }

    /**
     * @param  Collection<int|string, EmployeeAssessmentItemEntry>  $latestStates
     * @param  array<int|string, mixed>  $draftResponses
     * @param  list<int>  $sectionItemIds
     */
    protected static function countRatedItems(
        Collection $latestStates,
        array $draftResponses,
        array $sectionItemIds,
        bool $useDraft,
    ): int {
        $count = 0;

        foreach ($sectionItemIds as $itemId) {
            $rating = null;

            if ($useDraft) {
                $draftRating = $draftResponses[$itemId] ?? $draftResponses[(string) $itemId] ?? null;
                if (is_array($draftRating)) {
                    $draftRating = $draftRating['response'] ?? null;
                }
                $rating = $draftRating;
            }

            if (($rating === null || $rating === '') && $latestStates->has($itemId)) {
                $rating = $latestStates->get($itemId)?->rating;
            }

            if (self::ratingToScore($rating) !== null || strtoupper(trim((string) $rating)) === 'N') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected static function snapshotHasTracheostomyActivity(array $snapshot): bool
    {
        $equipment = $snapshot['tracheostomy_equipment_checks'] ?? [];
        if (is_array($equipment) && count($equipment) > 0) {
            return true;
        }

        foreach (is_array($snapshot['tracheostomy_procedure_reviews'] ?? null) ? $snapshot['tracheostomy_procedure_reviews'] : [] as $rating) {
            $normalized = strtoupper(trim((string) $rating));
            if (in_array($normalized, ['E', 'S', 'U', 'N'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  Collection<int, EmployeeCompetencyItem>  $sectionItems
     * @return array{count: int, total: int, average: float, overall: string, total_items: int}
     */
    protected static function tracheostomyMetricsFromSnapshot(array $snapshot, Collection $sectionItems): array
    {
        $procedures = is_array($snapshot['tracheostomy_procedure_reviews'] ?? null)
            ? $snapshot['tracheostomy_procedure_reviews']
            : [];
        $equipment = is_array($snapshot['tracheostomy_equipment_checks'] ?? null)
            ? $snapshot['tracheostomy_equipment_checks']
            : [];

        $total = 0;
        $procedureRated = 0;

        foreach ($procedures as $rating) {
            $score = self::ratingToScore($rating);
            if ($score === null) {
                continue;
            }

            $total += $score;
            $procedureRated++;
        }

        $equipmentRated = count($equipment);
        $procedureStepTotal = self::countTracheostomyProcedureSteps($sectionItems);

        // Match the on-screen summary: rated includes equipment checks + procedure ratings; total steps = procedures only.
        $count = $procedureRated + $equipmentRated;
        $totalItems = $procedureStepTotal > 0 ? $procedureStepTotal : count($procedures);

        $average = $procedureRated > 0 ? round($total / $procedureRated, 2) : 0.0;
        $overall = match (true) {
            $procedureRated === 0 => $equipmentRated > 0 ? 'In Progress' : 'N/A',
            $average >= 2.5 => 'Excellent',
            $average >= 1.5 => 'Satisfactory',
            default => 'Unsatisfactory',
        };

        return [
            'count' => $count,
            'total' => $total,
            'average' => $average,
            'overall' => $overall,
            'total_items' => $totalItems,
        ];
    }

    /**
     * @param  Collection<int, EmployeeCompetencyItem>  $sectionItems
     */
    protected static function countTracheostomyProcedureSteps(Collection $sectionItems): int
    {
        return $sectionItems
            ->filter(fn (EmployeeCompetencyItem $item) => preg_match('/^-\d+\./', (string) $item->item) === 1)
            ->count();
    }

    protected static function ratingToScore(mixed $rating): ?int
    {
        $rating = strtoupper(trim((string) $rating));

        return match ($rating) {
            'E' => 3,
            'S' => 2,
            'U' => 1,
            default => null,
        };
    }

    protected static function resolveSectionStatus(?string $assessmentStatus, bool $isSubmitted): string
    {
        if ($isSubmitted) {
            return match ((string) ($assessmentStatus ?? 'draft')) {
                'completed' => 'Completed',
                'for_employee_signature' => 'For Employee Signature',
                'for_reviewer_signature' => 'For Reviewer Signature',
                'section_submit', 'draft' => 'Submitted',
                default => ucwords(str_replace('_', ' ', (string) $assessmentStatus)),
            };
        }

        return 'In Progress';
    }

    protected static function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return strlen($value) >= 10 ? substr($value, 0, 10) : $value;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  Collection<int|string, EmployeeAssessmentItemEntry>  $latestStates
     * @param  Collection<int, \App\Models\User>  $users
     */
    protected static function resolveReviewerName(
        ?EmployeeCompetencyAssessment $submission,
        array $snapshot,
        Collection $latestStates,
        Collection $users,
    ): string {
        if (filled($submission?->reviewer_name)) {
            return trim((string) $submission->reviewer_name);
        }

        $formReviewer = trim((string) ($snapshot['form']['reviewer_name'] ?? ''));
        if ($formReviewer !== '') {
            return $formReviewer;
        }

        $latestEntry = $latestStates
            ->sortByDesc(fn (EmployeeAssessmentItemEntry $entry) => sprintf(
                '%s-%010d',
                optional($entry->assessment_date)->toDateString() ?? '',
                $entry->id
            ))
            ->first();

        if ($latestEntry?->assessed_by) {
            $reviewer = $users->firstWhere('id', $latestEntry->assessed_by);

            if (filled($reviewer?->name)) {
                return trim((string) $reviewer->name);
            }
        }

        return '';
    }
}
