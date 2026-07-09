<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Support\PartGCompetencyScoring;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TracheostomyCareCompetency extends Component
{
    use ManagesPartGSectionExclusion;

    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    /** @var list<array<string, mixed>> */
    public array $renderItems = [];

    /** @var list<array<string, mixed>> */
    public array $procedureCompetencyItems = [];

    /** @var list<string> */
    public array $equipmentChecks = [];

    /** @var array<int, string> */
    public array $responses = [];

    public string $summaryComments = '';

    public string $employeeComments = '';

    public string $reviewerName = '';

    public string $reviewerTitle = '';

    public string $employeeName = '';

    public string $employeeTitle = '';

    public string $reviewSignDate = '';

    public string $employeeSignDate = '';

    public bool $assessmentLocked = false;

    public ?string $draftSaveMessage = null;

    public string $draftSaveType = '';

    public const SECTION = 'TRACHEOSTOMY CARE';

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
            $this->employeeName = trim(($employee->last_name ?? '').', '.($employee->first_name ?? ''), ', ');
            $this->employeeTitle = $employee->currentAssignment?->position?->title
                ?? ($employee->position ?? '');
        }

        $user = Auth::user();
        $this->reviewerName = $user?->name ?? '';
        $this->reviewerTitle = $this->resolveAuthenticatedReviewerTitle($user);

        $this->buildSectionData();
        $this->loadDraftData();
        $this->normalizeResponseKeys();
    }

    protected function persistDraftIfPossible(): void
    {
        if ($this->sectionItemReviewsLocked()) {
            return;
        }

        $this->persistAssessment('draft');
    }

    public function toggleEquipmentCheckById(int $itemId): void
    {
        $rawItem = collect($this->renderItems)
            ->firstWhere('id', $itemId)['rawItem'] ?? null;

        if (! is_string($rawItem) || $rawItem === '') {
            return;
        }

        $this->toggleEquipmentCheck($rawItem);
    }

    public function toggleEquipmentCheck(string $rawItem): void
    {
        if ($this->sectionItemReviewsLocked()) {
            return;
        }

        if (in_array($rawItem, $this->equipmentChecks, true)) {
            $this->equipmentChecks = array_values(array_filter(
                $this->equipmentChecks,
                fn (string $label) => $label !== $rawItem
            ));
        } else {
            $this->equipmentChecks[] = $rawItem;
        }

        $this->persistDraftIfPossible();
    }

    public function updatedSummaryComments(): void
    {
        $this->persistDraftIfPossible();
    }

    public function updatedEmployeeComments(): void
    {
        $this->persistDraftIfPossible();
    }

    public function updatedReviewSignDate(): void
    {
        $this->persistDraftIfPossible();
    }

    public function updatedEmployeeSignDate(): void
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
            $this->persistAssessment('draft');
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

        if (! $this->validatePartGSectionRatingsBeforeSubmit()) {
            return;
        }

        try {
            $this->persistAssessment('section_submit');
            $this->setDraftSaveFeedback('success', self::SECTION.' submitted successfully.');
        } catch (\Throwable $e) {
            report($e);
            $this->setDraftSaveFeedback('error', 'Failed to submit this section. Please try again.');
        }
    }

    public function isEquipmentChecked(string $rawItem): bool
    {
        return in_array($rawItem, $this->equipmentChecks, true);
    }

    protected function buildSectionData(): void
    {
        $rawItems = EmployeeCompetencyItem::query()
            ->where('section', self::SECTION)
            ->orderBy('order')
            ->get();

        $this->procedureCompetencyItems = [];
        $this->renderItems = [];

        foreach ($rawItems as $index => $item) {
            $raw = (string) $item->item;

            if (preg_match('/^-\d+\./', $raw) === 1) {
                if (preg_match('/^-(\d+)\.\s*(.+)$/', $raw, $matches)) {
                    $segments = array_map('trim', explode('||', $matches[2], 2));
                    $label = ltrim($segments[0] ?? '', '-');
                    $note = $segments[1] ?? null;
                    $display = $matches[1].'. '.$label;

                    $this->procedureCompetencyItems[] = [
                        'id' => $item->id,
                        'item' => $display,
                        'procedureKey' => (string) $matches[1],
                        'note' => $note,
                        'indentLevel' => 1,
                        'isParent' => false,
                    ];
                }

                continue;
            }

            $indentLevel = 0;
            if (preg_match('/^(-+)/', $raw, $indentMatches)) {
                $indentLevel = strlen($indentMatches[1]);
            }

            $hasChildItems = false;
            if (isset($rawItems[$index + 1])) {
                $nextRaw = (string) $rawItems[$index + 1]->item;
                if (preg_match('/^(-+)/', $nextRaw, $nextMatches)) {
                    $hasChildItems = strlen($nextMatches[1]) > $indentLevel;
                }
            }

            $isEquipmentHeader = $indentLevel === 1 && $hasChildItems;
            $isEquipmentItem = $indentLevel >= 2;

            $this->renderItems[] = [
                'id' => $item->id,
                'item' => ltrim($raw, '-'),
                'rawItem' => $raw,
                'indentLevel' => $indentLevel,
                'type' => $isEquipmentHeader ? 'equipment_header' : ($isEquipmentItem ? 'equipment_item' : 'row'),
            ];
        }
    }

    protected function loadDraftData(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if (! $assessment) {
            return;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];

        $this->equipmentChecks = $this->normalizeEquipmentChecks(
            $snapshot['tracheostomy_equipment_checks'] ?? []
        );

        $legacyProcedureReviews = $this->normalizeProcedureReviewsFromSnapshot(
            $snapshot['tracheostomy_procedure_reviews'] ?? []
        );

        $this->hydrateSectionResponsesFromStorage($this->procedureCompetencyItems);

        foreach ($this->procedureCompetencyItems as $procedureItem) {
            $itemId = (int) ($procedureItem['id'] ?? 0);
            $procedureKey = (string) ($procedureItem['procedureKey'] ?? '');

            if ($itemId <= 0 || $procedureKey === '') {
                continue;
            }

            if (isset($this->responses[$itemId])) {
                continue;
            }

            $legacyRating = $legacyProcedureReviews[$procedureKey] ?? null;
            if (PartGCompetencyScoring::isValidItemRating($legacyRating)) {
                $this->responses[$itemId] = $legacyRating;
            }
        }
    }

    protected function persistAssessment(string $intent): void
    {
        if ($this->abortPersistIfSelfAssessment()) {
            return;
        }

        $this->refreshReviewerIdentityForPersist();

        $row = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        $existing = $this->decodeResponses($row?->responses);
        $payload = $this->mergeItemReviewMetaIntoResponsesPayload($existing);

        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        $snapshot['tracheostomy_equipment_checks'] = $this->normalizeEquipmentChecks($this->equipmentChecks);
        $snapshot['tracheostomy_procedure_reviews'] = $this->buildProcedureReviewsFromResponses();
        $snapshot = $this->applySectionExclusionToSnapshot($snapshot);

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
        $updateData = $this->upsertSectionSummarySnapshot($updateData, $row, $intent === 'section_submit');

        if ($intent === 'section_submit') {
            $updateData = $this->withSectionSubmissionSnapshot($updateData, $row);
        } else {
            $updateData['status'] = $this->resolveAssessmentStatusForDraft($row);
            $updateData['submitted_at'] = $row?->submitted_at;
        }

        EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );

        $this->syncCompetencyItemEntriesFromResponses($payload);
        $this->dispatchPartGSummaryUpdated();
    }

    protected function calculateScores(): array
    {
        return $this->calculateScoresFromSectionItems($this->procedureCompetencyItems);
    }

    /**
     * @return list<int>
     */
    protected function scorableItemIds(): array
    {
        return collect($this->procedureCompetencyItems)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function buildProcedureReviewsFromResponses(): array
    {
        $reviews = [];

        foreach ($this->procedureCompetencyItems as $item) {
            $procedureKey = trim((string) ($item['procedureKey'] ?? ''));
            $itemId = (int) ($item['id'] ?? 0);

            if ($procedureKey === '' || $itemId <= 0) {
                continue;
            }

            $rating = strtoupper(trim((string) ($this->responses[$itemId] ?? '')));
            if (PartGCompetencyScoring::numericScore($rating) !== null) {
                $reviews[$procedureKey] = $rating;
            }
        }

        return $reviews;
    }

    /**
     * @param  mixed  $checks
     * @return list<string>
     */
    protected function normalizeEquipmentChecks(mixed $checks): array
    {
        return collect(is_array($checks) ? $checks : [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $reviews
     * @return array<string, string>
     */
    protected function normalizeProcedureReviewsFromSnapshot(mixed $reviews): array
    {
        return collect(is_array($reviews) ? $reviews : [])
            ->mapWithKeys(function ($rating, $procedureKey) {
                $normalizedKey = trim((string) $procedureKey);
                $normalizedRating = strtoupper(trim((string) $rating));

                if ($normalizedKey === '' || ! PartGCompetencyScoring::isValidItemRating($normalizedRating)) {
                    return [];
                }

                return [$normalizedKey => $normalizedRating];
            })
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.tracheostomy-care-competency');
    }
}
