<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Support\PartGCompetencyScoring;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use Livewire\Component;

class MedicationAdministrationCompetency extends Component
{
    use ManagesPartGSectionExclusion;
    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    public array $medicationAdministrationCompetencyItems = [];

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

    public const SECTION = 'MEDICATION ADMINISTRATION';

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

        $this->medicationAdministrationCompetencyItems = $this->buildCompetencyItems();
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

            // Parent only if the immediate next row is a child (deeper dash-indent). No child → scorable row with radios.
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
                'id' => $item->id,
                'item' => ltrim($raw, '-'),
                'rawItem' => $raw,
                'indentLevel' => $indentLevel,
                'isParent' => $isParent,
            ];
        }

        return $items;
    }

    protected function loadDraftResponses(): void
    {
        $this->hydrateSectionResponsesFromStorage($this->medicationAdministrationCompetencyItems);
    }




    protected function calculateScores(): array
    {
        if ($this->sectionExcluded) {
            return $this->sectionExcludedScores();
        }

        $ratedCount = 0;
        $points = 0;

        foreach ($this->medicationAdministrationCompetencyItems as $item) {
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
        return collect($this->medicationAdministrationCompetencyItems)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.medication-administration-competency');
    }
}
