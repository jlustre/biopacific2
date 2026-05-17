<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DirectorOfStaffDevelopmentCompetency extends Component
{
    use ManagesPartGSectionExclusion;

    /** Umbrella label for Exclude / snapshot (not a DB section name). */
    public const SECTION = 'DIRECTOR OF STAFF DEVELOPMENT COMPETENCIES';

    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    public ?int $positionId = null;

    /**
     * @var list<array{section: string, items: list<array<string, mixed>>}>
     */
    public array $dsdCompetencySections = [];

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

    /**
     * Section titles seeded for Director of Staff Development (order preserved).
     *
     * @return list<string>
     */
    public static function dsdSectionKeys(): array
    {
        return [
            'IN-SERVICE EDUCATION PROGRAM MANAGEMENT',
            'ORIENTATION PROGRAM MANAGEMENT',
            'SKILLS COMPETENCY PROGRAM (ALL DEPARTMENTS)',
            'LICENSES, CERTIFICATIONS TRACKING',
            'HUMAN RESOURCES COORDINATION',
            'STAFFING & PPD MONITORING',
            'WORKERS COMP & SAFETY PROGRAM',
            'COMMITTEE PARTICIPATION',
            'CLINICAL OVERSIGHT & EDUCATION ROUNDS',
            'INFECTION PREVENTION SUPPORT',
            'EMPLOYEE HEALTH PROGRAM',
            'POLICIES AND PROCEDURES MANAGEMENT',
            'RESTORATIVE NURSING PROGRAM',
            'EHR / MATRIXCARE COMPETENCY',
            'SURVEY READINESS',
        ];
    }

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
            $this->positionId = $employee->currentAssignment?->position_id
                ?? $employee->currentAssignment?->position?->id;
        }

        $user = Auth::user();
        $this->reviewerName = $user?->name ?? '';
        $this->reviewerTitle = $user?->title ?? '';

        $this->dsdCompetencySections = $this->buildDsdSections();
        $this->loadDraftResponses();
        $this->normalizeResponseKeys();
    }

    public function setResponse(int $itemId, string $rating): void
    {
        if ($this->assessmentLocked || $this->sectionExcluded) {
            return;
        }

        if (! in_array($rating, ['E', 'S', 'U', 'N'], true)) {
            return;
        }

        $this->responses[$itemId] = $rating;
        $this->normalizeResponseKeys();
        $this->persistDraftIfPossible();
        $this->dispatch('dsd-responses-updated', responses: $this->responses);
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
            $this->persistResponses('draft');
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
            'responses' => 'required|array',
        ]);

        if (! $this->sectionExcluded) {
            foreach ($this->scorableItemIds() as $itemId) {
                $response = $this->responses[$itemId] ?? null;
                if (! in_array($response, ['E', 'S', 'U', 'N'], true)) {
                    $this->addError('responses', 'Please rate all competency items before submitting.');

                    return;
                }
            }
        }

        $this->persistResponses('submitted');
        session()->flash('success', 'Director of Staff Development competency assessment saved.');
    }

    /**
     * @return list<array{section: string, items: list<array<string, mixed>>}>
     */
    protected function buildDsdSections(): array
    {
        $query = EmployeeCompetencyItem::query()
            ->whereIn('section', self::dsdSectionKeys())
            ->orderBy('order');

        if ($this->positionId) {
            $query->applicableToPosition($this->positionId);
        }

        $rawBySection = $query->get()->groupBy('section');
        $sections = [];

        foreach (self::dsdSectionKeys() as $sectionName) {
            if (! $rawBySection->has($sectionName)) {
                continue;
            }
            $rows = $rawBySection->get($sectionName);
            if ($rows === null || $rows->isEmpty()) {
                continue;
            }
            $sections[] = [
                'section' => $sectionName,
                'items' => $this->buildItemsForRawRows($rows->values()->all()),
            ];
        }

        return $sections;
    }

    /**
     * @param  list<EmployeeCompetencyItem>  $rawItems
     * @return list<array<string, mixed>>
     */
    protected function buildItemsForRawRows(array $rawItems): array
    {
        $items = [];

        foreach ($rawItems as $index => $item) {
            $indentLevel = 0;
            if (preg_match('/^(-+)/', $item->item, $matches)) {
                $indentLevel = strlen($matches[1]);
            }

            $isParent = false;
            if (isset($rawItems[$index + 1])) {
                $next = $rawItems[$index + 1];
                if (preg_match('/^(-+)/', $next->item, $nextMatches)) {
                    $isParent = strlen($nextMatches[1]) > $indentLevel;
                }
            }

            $items[] = [
                'id' => $item->id,
                'item' => ltrim($item->item, '-'),
                'rawItem' => $item->item,
                'indentLevel' => $indentLevel,
                'isParent' => $isParent,
            ];
        }

        return $items;
    }

    protected function loadDraftResponses(): void
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

        $decoded = $this->decodeResponses($assessment->responses ?? null);

        $ids = $this->scorableItemIds();

        foreach ($decoded as $itemId => $data) {
            if (! in_array((int) $itemId, $ids, true) && ! in_array((string) $itemId, array_map('strval', $ids), true)) {
                continue;
            }

            $response = is_array($data)
                ? ($data['response'] ?? null)
                : $data;

            if ($response !== null && $response !== '') {
                $this->responses[(int) $itemId] = $response;
            }
        }

        $this->summaryComments = (string) ($assessment->comments ?? '');
        $this->employeeComments = (string) ($assessment->employee_comments ?? '');
        $this->reviewSignDate = $assessment->review_date?->format('Y-m-d') ?? '';
        $this->employeeSignDate = $assessment->employee_signed_at?->format('Y-m-d') ?? '';

        $this->loadSectionExcludedFromAssessment($assessment);
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

        $this->persistResponses('draft');
    }

    protected function persistResponses(string $status): void
    {
        if ($this->abortPersistIfSelfAssessment()) {
            return;
        }

        $existing = [];
        $row = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if ($row?->responses) {
            $existing = $this->decodeResponses($row->responses);
        }

        $payload = $existing;
        foreach ($this->responses as $itemId => $response) {
            if ($response === null || $response === '') {
                continue;
            }
            $payload[(int) $itemId] = ['response' => $response];
        }

        $scores = $this->calculateScores();

        $updateData = [
            'responses' => $payload,
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
        ];

        $updateData = $this->withExcludedSnapshot($updateData, $row);

        EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );
    }

    protected function calculateScores(): array
    {
        if ($this->sectionExcluded) {
            return $this->sectionExcludedScores();
        }

        $ratedCount = 0;
        $points = 0;

        foreach ($this->dsdCompetencySections as $block) {
            foreach ($block['items'] as $item) {
                if ($item['isParent'] ?? false) {
                    continue;
                }

                $response = $this->responses[$item['id']] ?? null;
                if ($response === null || $response === '' || $response === 'N') {
                    continue;
                }

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

    protected function normalizeResponseKeys(): void
    {
        $normalized = [];

        foreach ($this->responses as $itemId => $response) {
            if ($response === null || $response === '') {
                continue;
            }

            $normalized[(int) $itemId] = $response;
        }

        $this->responses = $normalized;
    }

    /**
     * @return array<int|string, mixed>
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

    /**
     * @return list<int>
     */
    protected function scorableItemIds(): array
    {
        $ids = [];

        foreach ($this->dsdCompetencySections as $block) {
            foreach ($block['items'] as $item) {
                if (! ($item['isParent'] ?? false)) {
                    $ids[] = (int) $item['id'];
                }
            }
        }

        return $ids;
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.director-of-staff-development-competency');
    }
}
