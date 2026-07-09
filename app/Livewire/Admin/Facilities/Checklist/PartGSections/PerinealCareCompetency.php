<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Support\PartGCompetencyScoring;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;
use Livewire\Component;

class PerinealCareCompetency extends Component
{
    use ManagesPartGSectionExclusion;
    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    public array $perinealCareCompetencyItems = [];

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

    public const SECTION = 'PERINEAL CARE';

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

        $this->perinealCareCompetencyItems = $this->buildCompetencyItems();
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
        $this->hydrateSectionResponsesFromStorage($this->perinealCareCompetencyItems);
    }




    protected function calculateScores(): array
    {
        return $this->calculateScoresFromSectionItems($this->perinealCareCompetencyItems);
    }



    /**
     * @return list<int>
     */
    protected function scorableItemIds(): array
    {
        return collect($this->perinealCareCompetencyItems)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.perineal-care-competency');
    }
}
