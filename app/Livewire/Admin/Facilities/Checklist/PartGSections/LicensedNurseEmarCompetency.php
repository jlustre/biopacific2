<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Models\User;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ClearsPartGCompetencySectionProgress;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGItemReviews;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionWorkflowUi;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ResolvesPartGReviewerIdentity;
use App\Livewire\Concerns\GuardsAgainstSelfAssessment;
use App\Services\AssessmentConfirmationNotificationService;
use App\Support\AssessmentWorkflowStatus;
use App\Support\PartGCompetencyScoring;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Clean-slate Livewire component for the LICENSED NURSE eMAR COMPETENCY section.
 *
 * Design rules (kept narrow to avoid Alpine / window-event conflicts):
 *  - Ratings use wire:model.live on responses.{id} (reliable Livewire binding).
 *  - Section + global summaries are #[Computed] so every render reflects current responses.
 *  - Persistence and re-render happen entirely on the server; no JS bridges.
 */
class LicensedNurseEmarCompetency extends Component
{
    use GuardsAgainstSelfAssessment;
    use ClearsPartGCompetencySectionProgress;
    use ManagesPartGItemReviews;
    use ManagesPartGSectionWorkflowUi;
    use ResolvesPartGReviewerIdentity;

    public const SECTION = 'LICENSED NURSE eMAR COMPETENCY';

    public string $employeeNum = '';

    public ?int $assessmentPeriodId = null;

    public bool $assessmentLocked = false;

    public bool $sectionExcluded = false;

    /** @var list<array{id:int,item:string,rawItem:string,indentLevel:int,isParent:bool}> */
    public array $items = [];


    /** @var array<int,string> source_item_id => 'E'|'M'|'B' */
    public array $responses = [];

    // For draft save feedback
    public ?string $draftSaveMessage = null;

    public string $draftSaveType = '';

    public string $summaryComments = '';

    public string $employeeComments = '';

    public string $reviewSignDate = '';

    public string $employeeSignDate = '';

    public string $employeeName = '';

    public string $employeeTitle = '';

    public string $reviewerName = '';

    public string $reviewerTitle = '';

    public function mount(
        string $employeeNum,
        ?int $assessmentPeriodId = null,
        bool $assessmentLocked = false,
    ): void {
        $this->employeeNum = $employeeNum;
        $this->assessmentPeriodId = $assessmentPeriodId;
        $this->assessmentLocked = $assessmentLocked;

        $employee = BPEmployee::with('currentAssignment.position')
            ->where('employee_num', $employeeNum)
            ->first();

        if ($employee) {
            $this->employeeName = $employee->formattedFullName();
            $this->employeeTitle = $employee->currentAssignment?->position?->title
                ?? ($employee->position ?? '');
        }

        $user = Auth::user();
        $this->reviewerName = $user?->name ?? '';
        $this->reviewerTitle = $this->resolveAuthenticatedReviewerTitle($user);

        $this->items = $this->buildCompetencyItems();
        $this->loadResponsesFromStorage();
        $this->loadReviewerIdentityFromStorage();
        $this->loadSectionCommentsFromStorage();
        $this->loadExclusionFromStorage();
        $this->normalizeResponseKeys();
        $this->initializeReturnedSectionResubmitState();
    }

    #[Computed]
    public function displayReviewSignDate(): string
    {
        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return '';
        }

        $timestamp = $assessment->reviewer_signed_at ?? $assessment->review_date;
        if (blank($timestamp)) {
            return '';
        }

        return \Illuminate\Support\Carbon::parse($timestamp)->format('M j, Y g:i A');
    }

    #[Computed]
    public function displayEmployeeSignDate(): string
    {
        $assessment = $this->loadAssessment();
        if (! $assessment?->employee_signed_at) {
            return '';
        }

        return $assessment->employee_signed_at->format('M j, Y g:i A');
    }

    public function getSectionLabelProperty(): string
    {
        return self::SECTION;
    }

    #[Computed]
    public function reviewerCanApproveWorkflow(): bool
    {
        return AssessmentWorkflowStatus::reviewerCanApprove($this->assessmentWorkflowStatus);
    }

    #[Computed]
    public function contentChangedSinceEmployeeConfirmation(): bool
    {
        $assessment = $this->loadAssessment();
        if (! $assessment || ! $this->reviewerCanApproveWorkflow) {
            return false;
        }

        return app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionHasChangedSinceEmployeeConfirmation($assessment, self::SECTION);
    }

    #[Computed]
    public function canCompleteAssessment(): bool
    {
        if ($this->evaluatorActionsDisabled || ! $this->reviewerCanApproveWorkflow) {
            return false;
        }

        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return false;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
        if (blank($sectionWorkflow->sectionEmployeeSignaturePath($assessment, self::SECTION))) {
            return false;
        }

        return ! $this->contentChangedSinceEmployeeConfirmation;
    }

    #[Computed]
    public function storedEmployeeName(): string
    {
        return trim((string) ($this->loadAssessment()?->employee_name ?? '')) ?: $this->employeeName;
    }

    #[Computed]
    public function storedReviewerName(): string
    {
        return trim((string) ($this->loadAssessment()?->reviewer_name ?? '')) ?: $this->reviewerName;
    }

    #[Computed]
    public function sectionReadyForReviewerCompletion(): bool
    {
        if ($this->sectionExcluded) {
            return true;
        }

        $itemIds = $this->scorableItemIds();
        if ($itemIds === []) {
            return false;
        }

        foreach ($itemIds as $itemId) {
            if (! PartGCompetencyScoring::isValidItemRating($this->responses[$itemId] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function updatedSummaryComments(): void
    {
        $this->persistDraftIfPossible();
    }

    public function updatedEmployeeComments(): void
    {
        $this->persistDraftIfPossible();
    }

    public function saveDraft(): void
    {
        $this->draftSaveMessage = null;
        $this->draftSaveType = '';

        if (! $this->guardPartGManualDraftSave()) {
            return;
        }

        try {
            $this->persistResponses('draft');
            $this->setDraftSaveFeedback('success', 'Draft saved successfully!');
        } catch (\Throwable $e) {
            report($e);
            $this->setDraftSaveFeedback('error', 'Failed to save draft. Please try again.');
        }
    }

    public function submitAssessment(): void
    {
        $this->draftSaveMessage = null;
        $this->draftSaveType = '';

        if (! $this->guardPartGSectionSubmit()) {
            return;
        }

        if ($this->sectionIsSubmitted()) {
            $assessment = $this->loadAssessment();
            $returned = $assessment
                && app(\App\Services\CompetencySectionWorkflowService::class)
                    ->sectionWasReturnedToReviewer($assessment, self::SECTION);

            if (! $returned && $this->assessmentWorkflowStatus !== AssessmentWorkflowStatus::DRAFT) {
                $this->setDraftSaveFeedback('error', 'This section has already been submitted.');

                return;
            }
        }

        $this->validate([
            'responses' => 'required|array',
        ]);

        if (! $this->sectionExcluded) {
            foreach ($this->scorableItemIds() as $itemId) {
                $response = $this->responses[$itemId] ?? null;
                if (! PartGCompetencyScoring::isValidItemRating($response)) {
                    $this->addError('responses', 'Please rate all competency items before submitting.');

                    return;
                }
            }
        }

        try {
            $this->persistResponses('section_submit');

            $employee = BPEmployee::query()
                ->where('employee_num', $this->employeeNum)
                ->first();

            $message = self::SECTION.' submitted and sent to the employee for signature.';
            if ($employee) {
                $email = app(AssessmentConfirmationNotificationService::class)->resolveEmployeeEmail($employee);
                $message .= $email
                    ? ' The employee has been notified by email and will see a task on their dashboard.'
                    : ' No employee email is on file, so no notification was sent.';
            }

            $this->setDraftSaveFeedback('success', $message);
        } catch (\Throwable $e) {
            report($e);
            $this->setDraftSaveFeedback('error', 'Failed to submit this section. Please try again.');
        }
    }

    /**
     * @return array{totalItems:int,checkedOfTotal:string,totalPoints:int|float,average:string,overallRating:string}
     */
    #[Computed]
    public function sectionSummaryMetrics(): array
    {
        return $this->buildSectionSummaryMetrics();
    }

    /**
     * @return array{total_score:int,average_score:float,overall_rating:string,average_score_formatted:string}
     */
    #[Computed]
    public function globalSummaryMetrics(): array
    {
        $global = $this->buildGlobalSummaryMetrics();
        $global['average_score_formatted'] = number_format((float) $global['average_score'], 2, '.', '');

        return $global;
    }

    public function updatedResponses(mixed $value, string $key): void
    {
        if ($this->evaluatorActionsDisabled) {
            $this->denyEvaluatorAction();
            unset($this->responses[$key]);

            return;
        }

        if ($this->cannotRate()) {
            unset($this->responses[$key]);

            return;
        }

        $itemId = (int) $key;
        $rating = PartGCompetencyScoring::normalizeItemRating((string) $value);

        if ($rating === null) {
            unset($this->responses[$itemId]);

            return;
        }

        $this->responses[$itemId] = $rating;
        $this->normalizeResponseKeys();
        $this->persistRating($itemId, $rating);
    }

    public function setRating(int $itemId, string $rating): void
    {
        if ($this->evaluatorActionsDisabled) {
            $this->denyEvaluatorAction();

            return;
        }

        if ($this->cannotRate()) {
            return;
        }

        $rating = PartGCompetencyScoring::normalizeItemRating($rating);
        if ($rating === null) {
            return;
        }

        $this->responses[$itemId] = $rating;
        $this->normalizeResponseKeys();
        $this->persistRating($itemId, $rating);
    }

    public function updatedSectionExcluded(): void
    {
        if (! $this->canPersist()) {
            $this->sectionExcluded = ! $this->sectionExcluded;

            return;
        }

        $this->persistResponses('draft');
    }

    protected function persistDraftIfPossible(): void
    {
        if (! $this->assessmentPeriodId || $this->evaluatorActionsDisabled) {
            return;
        }

        if ($this->sectionItemReviewsLocked() && $this->reviewerSummaryCommentsLocked()) {
            return;
        }

        $this->persistResponses('draft');
    }

    protected function guardPartGManualDraftSave(): bool
    {
        if ($this->sectionItemReviewsLocked() && $this->reviewerSummaryCommentsLocked()) {
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

        if ($this->reviewerSummaryCommentsLocked()) {
            $this->setDraftSaveFeedback('error', 'This assessment cannot be saved as a draft at the current workflow stage.');

            return false;
        }

        return true;
    }

    protected function guardPartGSectionSubmit(): bool
    {
        if ($this->sectionItemReviewsLocked()) {
            $this->setDraftSaveFeedback('error', 'This assessment is read-only and cannot be submitted.');

            return false;
        }

        if (! $this->assessmentPeriodId) {
            $this->setDraftSaveFeedback('error', 'Please select an assessment period before submitting this section.');

            return false;
        }

        if ($this->denyEvaluatorAction()) {
            return false;
        }

        if ($this->assessmentWorkflowStatus !== AssessmentWorkflowStatus::DRAFT) {
            $this->setDraftSaveFeedback('error', 'This section cannot be submitted again at the current workflow stage.');

            return false;
        }

        return true;
    }

    protected function setDraftSaveFeedback(string $type, string $message): void
    {
        $this->draftSaveType = $type;
        $this->draftSaveMessage = $message;
    }

    /**
     * @return list<int>
     */
    protected function scorableItemIds(): array
    {
        return collect($this->items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{totalPoints:int|float,average:float|string,overallRating:string}
     */
    protected function calculateScores(): array
    {
        if ($this->sectionExcluded) {
            return [
                'totalPoints' => 0,
                'average' => 0,
                'overallRating' => 'Excluded',
            ];
        }

        $metrics = $this->buildSectionSummaryMetrics();
        $average = $metrics['average'] === '—' ? 0.0 : (float) $metrics['average'];

        return [
            'totalPoints' => $metrics['totalPoints'],
            'average' => $average,
            'overallRating' => $metrics['overallRating'],
        ];
    }

    protected function cannotRate(): bool
    {
        return $this->sectionItemReviewsLocked();
    }

    protected function persistRating(int $itemId, string $rating): void
    {
        if (! $this->canPersist() || $this->sectionExcluded) {
            return;
        }

        $this->persistResponses();
    }

    protected function normalizeResponseKeys(): void
    {
        $normalized = [];

        foreach ($this->responses as $itemId => $rating) {
            if ($rating === null || $rating === '') {
                continue;
            }

            $normalized[(int) $itemId] = $rating;
        }

        $this->responses = $normalized;
    }

    /**
     * @return array{totalItems:int,checkedOfTotal:string,totalPoints:int|float,average:string,overallRating:string}
     */
    protected function buildSectionSummaryMetrics(): array
    {
        if ($this->sectionExcluded) {
            return [
                'totalItems' => 0,
                'checkedOfTotal' => '',
                'totalPoints' => 0,
                'average' => '0',
                'overallRating' => 'Excluded',
                'pointsOfTotal' => PartGCompetencyScoring::pointsOfTotalLabel(0, 0),
            ];
        }

        $total = 0;
        $rated = 0;

        foreach ($this->items as $item) {
            if ($item['isParent'] ?? false) {
                continue;
            }

            $total++;
        }

        if (! $this->itemReviewsVisibleToCurrentUser()) {
            return $this->hiddenItemReviewsSummaryMetrics($total);
        }

        $rated = 0;
        $points = 0;

        foreach ($this->items as $item) {
            if ($item['isParent'] ?? false) {
                continue;
            }

            $rating = PartGCompetencyScoring::normalizeItemRating($this->responses[$item['id']] ?? null);

            if ($rating === null) {
                continue;
            }

            $rated++;
            $points += PartGCompetencyScoring::numericScore($rating) ?? 0;
        }

        $checkedOfTotal = $rated.' of '.$total.' rated';

        $average = $rated > 0 ? round($points / $rated, 2) : 0.0;

        return [
            'totalItems' => $total,
            'checkedOfTotal' => $checkedOfTotal,
            'totalPoints' => $points,
            'average' => $rated > 0 ? number_format($average, 2, '.', '') : '—',
            'overallRating' => PartGCompetencyScoring::overallLabel($average, $rated),
            'pointsOfTotal' => PartGCompetencyScoring::pointsOfTotalLabel($points, $total),
        ];
    }

    /**
     * @return array{total_score:int,average_score:float,overall_rating:string}
     */
    protected function buildGlobalSummaryMetrics(): array
    {
        $ratings = [];

        if ($this->assessmentPeriodId) {
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

            foreach ($latestEntries as $entry) {
                $sourceItemId = (int) $entry->source_item_id;
                if ($sourceItemId <= 0) {
                    continue;
                }

                $rating = strtoupper(trim((string) $entry->rating));
                if (PartGCompetencyScoring::isValidItemRating($rating)) {
                    $ratings[$sourceItemId] = $rating;
                }
            }
        }

        if (! $this->sectionExcluded) {
            foreach ($this->responses as $itemId => $rating) {
                if (! is_string($rating)) {
                    continue;
                }
                $normalized = strtoupper(trim($rating));
                if (PartGCompetencyScoring::isValidItemRating($normalized)) {
                    $ratings[(int) $itemId] = $normalized;
                }
            }
        } else {
            foreach ($this->items as $item) {
                if ($item['isParent'] ?? false) {
                    continue;
                }
                unset($ratings[(int) $item['id']]);
            }
        }

        return PartGCompetencyScoring::summarize($ratings);
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.licensed-nurse-emar-competency');
    }

    /**
     * @return list<array{id:int,item:string,rawItem:string,indentLevel:int,isParent:bool}>
     */
    protected function buildCompetencyItems(): array
    {
        $rawItems = EmployeeCompetencyItem::query()
            ->where('section', self::SECTION)
            ->orderBy('order')
            ->get();

        $items = [];

        foreach ($rawItems as $index => $item) {
            $raw = (string) $item->item;
            $indentLevel = 0;

            if (preg_match('/^(-+)/', $raw, $matches)) {
                $indentLevel = strlen($matches[1]);
            }

            // Parent only if the immediate next row is a deeper child.
            $isParent = false;
            if (isset($rawItems[$index + 1])) {
                $nextRaw = (string) $rawItems[$index + 1]->item;
                $nextIndent = 0;
                if (preg_match('/^(-+)/', $nextRaw, $nextMatches)) {
                    $nextIndent = strlen($nextMatches[1]);
                }
                $isParent = $nextIndent > $indentLevel;
            }

            $items[] = [
                'id' => (int) $item->id,
                'item' => ltrim($raw, '-'),
                'rawItem' => $raw,
                'indentLevel' => $indentLevel,
                'isParent' => $isParent,
            ];
        }

        return $items;
    }

    protected function loadResponsesFromStorage(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $sectionItemIds = collect($this->items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($sectionItemIds === []) {
            return;
        }

        $assessment = $this->loadAssessment();

        if ($assessment) {
            $decoded = $this->decodeResponses($assessment->responses);
            $this->hydrateItemReviewMetaFromPayload($decoded);

            foreach ($decoded as $itemKey => $entry) {
                $sourceItemId = $this->normalizeKey($itemKey);
                if (! in_array($sourceItemId, $sectionItemIds, true)) {
                    continue;
                }

                $rating = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                $normalized = PartGCompetencyScoring::normalizeItemRating(is_string($rating) ? $rating : null);
                if ($normalized !== null) {
                    $this->responses[$sourceItemId] = $normalized;
                }
            }
        }

        // Latest item entries trump the JSON blob when both are present.
        $latestEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereIn('source_item_id', $sectionItemIds)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (EmployeeAssessmentItemEntry $entry) => (int) $entry->source_item_id)
            ->map(fn ($entries) => $entries->first());

        foreach ($latestEntries as $entry) {
            $sourceItemId = (int) $entry->source_item_id;
            if ($sourceItemId <= 0) {
                continue;
            }

            $normalized = PartGCompetencyScoring::normalizeItemRating((string) $entry->rating);
            if ($normalized !== null) {
                $this->responses[$sourceItemId] = $normalized;
            }

            $this->itemReviewMeta[$sourceItemId] = [
                'review_date' => $entry->assessment_date?->format('Y-m-d'),
                'reviewer_id' => $entry->assessed_by,
                'reviewer_name' => $entry->assessed_by
                    ? (\App\Models\User::query()->find($entry->assessed_by)?->name ?? '')
                    : '',
                'comments' => $entry->comments,
            ];
        }
    }

    protected function loadSectionCommentsFromStorage(): void
    {
        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return;
        }

        $comments = app(\App\Services\CompetencySectionWorkflowService::class)
            ->resolveSectionComments($assessment, self::SECTION);

        $this->summaryComments = $comments['reviewer_comments'];
        $this->employeeComments = $comments['employee_comments'];

        $workflow = app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionWorkflow($assessment, self::SECTION);

        $storedEmployeeName = trim((string) ($assessment->employee_name ?? ''));
        if ($storedEmployeeName !== '') {
            $this->employeeName = $storedEmployeeName;
        }

        $this->reviewSignDate = $this->formatSectionSignDate($workflow['reviewer_signed_at'] ?? null);
        $this->employeeSignDate = $this->formatSectionSignDate($workflow['employee_signed_at'] ?? null);
    }

    protected function formatSectionSignDate(mixed $value): string
    {
        if (! filled($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    public function sectionIsSubmitted(): bool
    {
        if (! $this->assessmentPeriodId) {
            return false;
        }

        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return false;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submittedLabels = $snapshot['submitted_section_labels'] ?? [];

        return in_array(self::SECTION, $submittedLabels, true);
    }

    protected function loadExclusionFromStorage(): void
    {
        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $excluded = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => (string) $label)
            ->all();

        $this->sectionExcluded = in_array(self::SECTION, $excluded, true);
    }

    protected function loadAssessment(): ?EmployeeCompetencyAssessment
    {
        if (! $this->assessmentPeriodId) {
            return null;
        }

        return EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();
    }

    protected function canPersist(): bool
    {
        if ($this->sectionItemReviewsLocked()) {
            return false;
        }

        if (! $this->assessmentPeriodId) {
            return false;
        }

        if ($this->evaluatorActionsDisabled) {
            return false;
        }

        return true;
    }

    /**
     * Persist current section state:
     *  - upsert the EmployeeCompetencyAssessment row (responses JSON + snapshot exclusion + reviewer info)
     *  - create a new EmployeeAssessmentItemEntry for any rating that actually changed
     *  - refresh the assessment's denormalized total/average/overall columns
     *
     * @param  'auto'|'draft'|'section_submit'  $intent
     */
    protected function persistResponses(string $intent = 'auto'): void
    {
        if (! $this->canPersist()) {
            return;
        }

        $row = $this->loadAssessment();
        $existing = $this->decodeResponses($row?->responses);

        $payload = $this->mergeItemReviewMetaIntoResponsesPayload($existing);

        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        $snapshot = $this->applyExclusionToSnapshot($snapshot);

        $this->refreshReviewerIdentityForPersist();

        $updateData = [
            'responses' => $payload,
            'snapshot_json' => $snapshot,
            'reviewer_name' => $this->reviewerName,
            'reviewer_title' => $this->reviewerTitle,
            'employee_name' => $this->employeeName,
            'employee_title' => $this->employeeTitle,
            'submitted_by' => Auth::id(),
        ];

        $updateData = $this->applySectionScopedFormFields($updateData, $row);
        $updateData = $this->upsertSectionSummarySnapshot(
            $updateData,
            $row,
            $intent === 'section_submit'
        );

        if ($intent === 'section_submit') {
            $updateData = $this->withSectionSubmissionSnapshot($updateData, $row);
        } else {
            $updateData['status'] = $intent === 'draft'
                ? $this->resolveAssessmentStatusForDraft($row)
                : $this->resolveStatus($row);
            $updateData['submitted_at'] = $row?->submitted_at;
        }

        $row = EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );

        // Append entries only for this section's items (not the entire payload).
        $sectionItemIds = collect($this->items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($sectionItemIds as $itemId) {
            $rating = $this->responses[$itemId] ?? null;
            if (! is_string($rating) || $rating === '') {
                continue;
            }
            $this->upsertItemEntry($itemId, $rating);
        }

        $this->syncAssessmentSummaryColumns($row);

        if ($intent === 'section_submit') {
            $this->finalizeIndependentSectionSubmission($row->fresh());
        } else {
            $this->maybeResetCompetencyAssessmentForEmployeeReconfirmation();

            if ($intent === 'draft') {
                $this->finalizeReturnedSectionDraftSave($row->fresh());
            }
        }

        $this->dispatchPartGSummaryUpdated();
    }

    protected function finalizeIndependentSectionSubmission(?EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment) {
            return;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
        $wasResubmit = $sectionWorkflow->submitSectionForEmployeeConfirmation(
            $assessment,
            self::SECTION,
            Auth::id(),
        );

        $employee = BPEmployee::query()
            ->where('employee_num', $this->employeeNum)
            ->first();

        if (! $employee) {
            return;
        }

        $notificationService = app(AssessmentConfirmationNotificationService::class);
        if ($wasResubmit) {
            $notificationService->notifyCompetencySectionResubmittedToEmployee($assessment->fresh(), $employee, self::SECTION);
        } else {
            $notificationService->notifyCompetencySectionSubmittedToEmployee($assessment->fresh(), $employee, self::SECTION);
        }

        $this->regenerateCompetencySectionPdf($assessment->fresh(), self::SECTION);
    }

    protected function regenerateCompetencySectionPdf(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): void {
        try {
            app(\App\Http\Controllers\EmployeePerformanceAssessmentController::class)
                ->persistCompetencySectionPdf($assessment, $sectionLabel);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function applySectionScopedFormFields(array $updateData, ?EmployeeCompetencyAssessment $row): array
    {
        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        if (isset($updateData['snapshot_json']) && is_array($updateData['snapshot_json'])) {
            $snapshot = $updateData['snapshot_json'];
        }

        $snapshot['section_comments'] ??= [];

        $existingComments = $row
            ? app(\App\Services\CompetencySectionWorkflowService::class)
                ->resolveSectionComments($row, self::SECTION)
            : ['reviewer_comments' => '', 'employee_comments' => ''];

        $employeeComments = (string) ($existingComments['employee_comments'] ?? '');

        $snapshot['section_comments'][self::SECTION] = [
            'reviewer_comments' => $this->summaryComments,
            'employee_comments' => $employeeComments,
            'review_date' => optional($row?->reviewer_signed_at ?? $row?->review_date)->format('Y-m-d'),
            'employee_signed_at' => optional($row?->employee_signed_at)->format('Y-m-d'),
        ];

        $updateData['snapshot_json'] = $snapshot;

        return $updateData;
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function upsertSectionSummarySnapshot(
        array $updateData,
        ?EmployeeCompetencyAssessment $row,
        bool $markSubmittedAt = false,
    ): array {
        $snapshot = is_array($updateData['snapshot_json'] ?? null)
            ? $updateData['snapshot_json']
            : (is_array($row?->snapshot_json) ? $row->snapshot_json : []);

        $scores = $this->calculateScores();
        $existing = is_array($snapshot['section_summaries'][self::SECTION] ?? null)
            ? $snapshot['section_summaries'][self::SECTION]
            : [];

        $snapshot['section_summaries'] ??= [];
        $snapshot['section_summaries'][self::SECTION] = array_merge($existing, [
            'total_score' => $scores['totalPoints'],
            'average_score' => $scores['average'],
            'overall_rating' => $scores['overallRating'],
            'review_date' => optional($row?->reviewer_signed_at ?? $row?->review_date)->format('Y-m-d'),
        ]);

        if ($markSubmittedAt || empty($snapshot['section_summaries'][self::SECTION]['submitted_at'])) {
            $snapshot['section_summaries'][self::SECTION]['submitted_at'] = now()->toDateTimeString();
        }

        $updateData['snapshot_json'] = $snapshot;

        return $updateData;
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function withSectionSubmissionSnapshot(array $updateData, ?EmployeeCompetencyAssessment $row): array
    {
        $snapshot = is_array($updateData['snapshot_json'] ?? null)
            ? $updateData['snapshot_json']
            : (is_array($row?->snapshot_json) ? $row->snapshot_json : []);

        $updateData['snapshot_json'] = $snapshot;
        $updateData = $this->upsertSectionSummarySnapshot($updateData, $row, true);
        $snapshot = is_array($updateData['snapshot_json'] ?? null) ? $updateData['snapshot_json'] : [];

        $submittedLabels = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn ($label) => $label !== '')
            ->values()
            ->all();

        if (! in_array(self::SECTION, $submittedLabels, true)) {
            $submittedLabels[] = self::SECTION;
        }

        $snapshot['submitted_section_labels'] = array_values(array_unique($submittedLabels));
        $updateData['snapshot_json'] = $snapshot;
        $updateData['status'] = $this->resolveAssessmentStatusForDraft($row);
        $updateData['submitted_at'] = $row?->submitted_at;

        return $updateData;
    }

    protected function resolveAssessmentStatusForDraft(?EmployeeCompetencyAssessment $row): string
    {
        $status = (string) ($row?->status ?? 'draft');

        if (in_array($status, [
            AssessmentWorkflowStatus::COMPLETED,
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
        ], true)) {
            return $status;
        }

        return 'draft';
    }

    protected function maybeResetCompetencyAssessmentForEmployeeReconfirmation(): void
    {
        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        if (! $sectionWorkflow->sectionHasChangedSinceEmployeeConfirmation($assessment, self::SECTION)) {
            return;
        }

        $wasResubmit = $sectionWorkflow->reviewerResubmitSection($assessment, self::SECTION);

        $employee = BPEmployee::query()
            ->where('employee_num', $this->employeeNum)
            ->first();

        if ($employee && $wasResubmit) {
            app(AssessmentConfirmationNotificationService::class)
                ->notifyCompetencySectionResubmittedToEmployee($assessment->fresh(), $employee, self::SECTION);
        }
    }

    protected function upsertItemEntry(int $itemId, string $rating): void
    {
        $latest = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->where('source_item_id', $itemId)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->first();

        $meta = $this->itemReviewMeta[$itemId] ?? [];
        $assessmentDate = (string) ($meta['review_date'] ?? now()->toDateString());
        $assessedBy = isset($meta['reviewer_id']) ? (int) $meta['reviewer_id'] : Auth::id();
        $comments = filled($meta['comments'] ?? null) ? (string) $meta['comments'] : null;

        if (
            $latest
            && PartGCompetencyScoring::normalizeItemRating((string) $latest->rating) === PartGCompetencyScoring::normalizeItemRating($rating)
            && (string) ($latest->comments ?? '') === (string) ($comments ?? '')
            && $latest->assessment_date?->format('Y-m-d') === substr($assessmentDate, 0, 10)
        ) {
            return;
        }

        EmployeeAssessmentItemEntry::create([
            'employee_num' => $this->employeeNum,
            'assessment_period_id' => $this->assessmentPeriodId,
            'assessment_type' => 'competency',
            'item_key' => 'G_'.$itemId,
            'item_label' => $this->labelForItem($itemId),
            'source_item_id' => $itemId,
            'rating' => $rating,
            'assessment_date' => $assessmentDate,
            'assessed_by' => $assessedBy,
            'comments' => $comments,
        ]);
    }

    protected function dispatchPartGSummaryUpdated(): void
    {
        $summary = $this->buildGlobalSummaryMetrics();

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

        $this->js('window.updatePartGSummaryScores && window.updatePartGSummaryScores('.json_encode($payload).')');
    }

    protected function syncAssessmentSummaryColumns(EmployeeCompetencyAssessment $row): void
    {
        // Pull fresh ratings straight from the DB so the denormalized columns
        // never drift from the source-of-truth item-entry table.
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
            $rating = strtoupper(trim((string) $entry->rating));
            if (PartGCompetencyScoring::isValidItemRating($rating)) {
                $ratings[$sourceItemId] = $rating;
            }
        }

        // Excluded sections should not contribute to the totals.
        if ($this->sectionExcluded) {
            foreach ($this->items as $item) {
                if ($item['isParent'] ?? false) {
                    continue;
                }
                unset($ratings[(int) $item['id']]);
            }
        }

        $summary = PartGCompetencyScoring::summarize($ratings);

        $row->update([
            'total_score' => $summary['total_score'],
            'average_score' => $summary['average_score'],
            'overall_rating' => $summary['overall_rating'],
        ]);
    }

    /**
     * @param  array<string,mixed>  $snapshot
     * @return array<string,mixed>
     */
    protected function applyExclusionToSnapshot(array $snapshot): array
    {
        $labels = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn ($label) => $label !== '' && $label !== self::SECTION)
            ->values()
            ->all();

        if ($this->sectionExcluded) {
            $labels[] = self::SECTION;
        }

        $snapshot['excluded_section_labels'] = array_values(array_unique($labels));

        return $snapshot;
    }

    protected function resolveStatus(?EmployeeCompetencyAssessment $row): string
    {
        $status = (string) ($row?->status ?? 'draft');

        if (in_array($status, [
            \App\Support\AssessmentWorkflowStatus::COMPLETED,
            \App\Support\AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            \App\Support\AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
        ], true)) {
            return $status;
        }

        return 'draft';
    }

    protected function labelForItem(int $itemId): ?string
    {
        foreach ($this->items as $item) {
            if ((int) $item['id'] === $itemId) {
                return ltrim((string) $item['item'], '-');
            }
        }

        return null;
    }

    /**
     * @return array<int|string,mixed>
     */
    protected function decodeResponses(mixed $raw): array
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

    protected function normalizeKey(mixed $key): int
    {
        if (is_int($key)) {
            return $key;
        }

        $value = trim((string) $key);
        if ($value === '') {
            return 0;
        }

        if (preg_match('/^G[_-]?(\d+)$/i', $value, $matches)) {
            return (int) $matches[1];
        }

        return (int) $value;
    }

}
