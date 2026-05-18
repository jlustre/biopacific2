<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use Livewire\Component;

class LicensedNurseCompetencySkills extends Component
{
    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    public array $lnCompetencyItems = [];

    public array $responses = [];

    public bool $assessmentLocked = false;

    public bool $sectionExcluded = false;

    public string $reviewerName = '';

    public string $reviewerTitle = '';
    
    public string $employeeName = '';

    public string $employeeTitle = '';

    public ?string $draftSaveMessage = null;

    public string $draftSaveType = '';

    public const SECTION = 'LICENSED NURSE COMPETENCY SKILLS';

    public function mount(
        string $employeeNum,
        ?int $assessmentPeriodId = null,
        bool $assessmentLocked = false,
    ): void {
        $this->employeeNum = $employeeNum;
        $this->assessmentPeriodId = $assessmentPeriodId;
        $this->assessmentLocked = $assessmentLocked;

        // Load competency items
        $this->lnCompetencyItems = $this->buildCompetencyItems();
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
                if (! in_array($response, ['E', 'S', 'U', 'N'], true)) {
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

    protected function buildCompetencyItems(): array
    {
        $rawItems = EmployeeCompetencyItem::query()
            ->where('section', self::SECTION)
            ->orderBy('order')
            ->get();

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
        $this->hydrateSectionResponsesFromStorage($this->lnCompetencyItems);
    }

    protected function calculateScores(): array
    {
        if ($this->sectionExcluded) {
            return $this->sectionExcludedScores();
        }

        $ratedCount = 0;
        $points = 0;

        foreach ($this->lnCompetencyItems as $item) {
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
     * @return list<int>
     */
    protected function scorableItemIds(): array
    {
        return collect($this->lnCompetencyItems)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.licensed-nurse-competency-skills', [
            'lnCompetencyItems' => $this->lnCompetencyItems,
            'responses' => $this->responses,
            'assessmentLocked' => $this->assessmentLocked,
            'sectionExcluded' => $this->sectionExcluded,
            'reviewerName' => $this->reviewerName,
            'reviewerTitle' => $this->reviewerTitle,
            'employeeName' => $this->employeeName,
            'employeeTitle' => $this->employeeTitle,
            'draftSaveMessage' => $this->draftSaveMessage,
        ]);
    }
}
