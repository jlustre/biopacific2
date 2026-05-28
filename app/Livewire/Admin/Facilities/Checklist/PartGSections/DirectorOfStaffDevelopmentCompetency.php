<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Support\PartGCompetencyScoring;

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
    public const SECTION = 'DIRECTOR OF STAFF DEVELOPMENT';

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

    public function updatedResponses(): void
    {
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

        $this->validate([
            'reviewSignDate' => 'required|date',
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
            $this->setDraftSaveFeedback('success', self::SECTION.' submitted successfully.');
        } catch (\Throwable $e) {
            report($e);
            $this->setDraftSaveFeedback('error', 'Failed to submit this section. Please try again.');
        }
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
        $items = [];
        foreach ($this->dsdCompetencySections as $block) {
            foreach ($block['items'] ?? [] as $item) {
                $items[] = $item;
            }
        }

        $this->hydrateSectionResponsesFromStorage($items);
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
                if ($response === null || $response === '' || ! PartGCompetencyScoring::isValidItemRating($response)) {
                    continue;
                }

                if (! PartGCompetencyScoring::numericScore($response) !== null) {
                    continue;
                }

                $ratedCount++;
                $points += PartGCompetencyScoring::numericScore($response) ?? 0;
            }
        }

        $average = $ratedCount > 0 ? round($points / $ratedCount, 2) : 0;

        return [
            'totalPoints' => $points,
            'average' => $average,
            'overallRating' => PartGCompetencyScoring::overallLabel($average, $ratedCount),
        ];
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
