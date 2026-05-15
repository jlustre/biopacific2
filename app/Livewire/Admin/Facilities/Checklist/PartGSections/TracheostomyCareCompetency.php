<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use Livewire\Component;

class TracheostomyCareCompetency extends Component
{
    use ManagesPartGSectionExclusion;
    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    /** @var list<array<string, mixed>> */
    public array $renderItems = [];

    /** @var list<array<string, mixed>> */
    public array $procedureRows = [];

    /** @var list<string> */
    public array $equipmentChecks = [];

    /** @var array<string, string> */
    public array $procedureReviews = [];

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

    public const SECTION = 'TRACHEOSTOMY CARE COMPETENCY';

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
        $this->reviewerTitle = $user?->title ?? '';

        $this->buildSectionData();
        $this->loadDraftData();
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
        if ($this->assessmentLocked || $this->sectionExcluded) {
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

    public function setProcedureReview(string $procedureKey, string $rating): void
    {
        if ($this->assessmentLocked || $this->sectionExcluded) {
            return;
        }

        if (! in_array($rating, ['E', 'S', 'U'], true)) {
            return;
        }

        $this->procedureReviews[$procedureKey] = $rating;
        $this->persistDraftIfPossible();
        $this->dispatch('trach-procedure-updated', reviews: $this->procedureReviews);
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

        if ($this->assessmentLocked) {
            $this->setDraftSaveFeedback('error', 'This assessment is read-only and cannot be saved.');

            return;
        }

        if (! $this->assessmentPeriodId) {
            $this->setDraftSaveFeedback('error', 'Please select an assessment period before saving a draft.');

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
        if ($this->assessmentLocked || ! $this->assessmentPeriodId) {
            return;
        }

        $this->validate([
            'reviewSignDate' => 'required|date',
        ]);

        if (! $this->sectionExcluded) {
            foreach ($this->procedureRowKeys() as $procedureKey) {
                $rating = $this->procedureReviews[$procedureKey] ?? null;
                if (! in_array($rating, ['E', 'S', 'U'], true)) {
                    $this->addError('procedureReviews', 'Please rate all procedure steps before submitting.');

                    return;
                }
            }
        }

        $this->persistAssessment('submitted');
        session()->flash('success', 'Tracheostomy Care Competency assessment saved.');
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

        $this->procedureRows = [];
        $this->renderItems = [];

        foreach ($rawItems as $index => $item) {
            $raw = (string) $item->item;

            if (preg_match('/^-\d+\./', $raw) === 1) {
                if (preg_match('/^-(\d+)\.\s*(.+)$/', $raw, $matches)) {
                    $segments = array_map('trim', explode('||', $matches[2], 2));
                    $this->procedureRows[] = [
                        'key' => (string) $matches[1],
                        'text' => ltrim($segments[0] ?? '', '-'),
                        'note' => $segments[1] ?? null,
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
        $this->procedureReviews = $this->normalizeProcedureReviews(
            $snapshot['tracheostomy_procedure_reviews'] ?? []
        );

        $this->loadSectionExcludedFromAssessment($assessment);

        $this->summaryComments = (string) ($assessment->comments ?? '');
        $this->employeeComments = (string) ($assessment->employee_comments ?? '');
        $this->reviewSignDate = $assessment->review_date?->format('Y-m-d') ?? '';
        $this->employeeSignDate = $assessment->employee_signed_at?->format('Y-m-d') ?? '';
    }

    protected function setDraftSaveFeedback(string $type, string $message): void
    {
        $this->draftSaveType = $type;
        $this->draftSaveMessage = $message;
    }

    protected function persistDraftIfPossible(): void
    {
        if ($this->assessmentLocked || ! $this->assessmentPeriodId) {
            return;
        }

        $this->persistAssessment('draft');
    }

    protected function persistAssessment(string $status): void
    {
        $row = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        $snapshot['tracheostomy_equipment_checks'] = $this->normalizeEquipmentChecks($this->equipmentChecks);
        $snapshot['tracheostomy_procedure_reviews'] = $this->normalizeProcedureReviews($this->procedureReviews);
        $snapshot = $this->applySectionExclusionToSnapshot($snapshot);

        $scores = $this->calculateScores();

        EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            [
                'snapshot_json' => $snapshot,
                'comments' => $this->summaryComments,
                'employee_comments' => $this->employeeComments,
                'reviewer_name' => $this->reviewerName,
                'reviewer_title' => $this->reviewerTitle,
                'review_date' => $this->reviewSignDate ?: null,
                'employee_name' => $this->employeeName,
                'employee_title' => $this->employeeTitle,
                'employee_signed_at' => filled($this->employeeSignDate) ? $this->employeeSignDate : null,
                'total_score' => $scores['totalPoints'],
                'average_score' => $scores['average'],
                'overall_rating' => $scores['overallRating'],
                'submitted_by' => Auth::id(),
                'submitted_at' => $status === 'submitted' ? now() : null,
                'status' => $status === 'submitted' ? 'submitted' : 'draft',
            ]
        );
    }

    protected function calculateScores(): array
    {
        if ($this->sectionExcluded) {
            return $this->sectionExcludedScores();
        }

        $ratedCount = 0;
        $points = 0;

        foreach ($this->procedureRowKeys() as $procedureKey) {
            $response = $this->procedureReviews[$procedureKey] ?? null;
            if (! in_array($response, ['E', 'S', 'U'], true)) {
                continue;
            }

            $ratedCount++;
            $points += match ($response) {
                'E' => 3,
                'S' => 2,
                'U' => 1,
                default => 0,
            };
        }

        $average = $ratedCount > 0 ? round($points / $ratedCount, 2) : 0;

        return [
            'totalPoints' => $points,
            'average' => $average,
            'overallRating' => match (true) {
                $ratedCount === 0 => '—',
                $average >= 2.5 => 'Excellent',
                $average >= 1.5 => 'Satisfactory',
                $average > 0 => 'Unsatisfactory',
                default => 'Needs Improvement',
            },
        ];
    }

    /**
     * @return list<string>
     */
    protected function procedureRowKeys(): array
    {
        return collect($this->procedureRows)
            ->pluck('key')
            ->map(fn ($key) => (string) $key)
            ->all();
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
    protected function normalizeProcedureReviews(mixed $reviews): array
    {
        return collect(is_array($reviews) ? $reviews : [])
            ->mapWithKeys(function ($rating, $procedureKey) {
                $normalizedKey = trim((string) $procedureKey);
                $normalizedRating = strtoupper(trim((string) $rating));

                if ($normalizedKey === '' || ! in_array($normalizedRating, ['E', 'S', 'U'], true)) {
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
