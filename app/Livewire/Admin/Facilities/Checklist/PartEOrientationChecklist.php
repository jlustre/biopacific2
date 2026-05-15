<?php

namespace App\Livewire\Admin\Facilities\Checklist;

use App\Models\BPEmployee;
use App\Models\BPEmpChecklist;
use App\Models\ChecklistItem;
use App\Models\User;
use App\Orientation\OrientationChecklistSource;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PartEOrientationChecklist extends Component
{
    public const WORKFLOW_DRAFT = 'draft';

    public const WORKFLOW_EMPLOYEE_SIGNATURE = 'employee_signature_needed';

    public const WORKFLOW_REVIEWER_SIGNATURE = 'reviewer_signature_needed';

    public const WORKFLOW_COMPLETED = 'completed';

    /** @var list<string> Names that were historically indented without dash prefix in seeded data */
    private const LEGACY_CHILD_NAMES = [
        'Morning Meeting/Ambassador Rounds',
        'Mini/Thorough Utilization Review',
        'Risk Meetings',
        'Computer software and available reports',
        'Voicemail',
        'Paging system',
        'E-mail',
        'Electronic Risk Management Assistant (ERMA)',
        'Omniview',
        'MatrixCare/POC',
        'Resident Care Manuals (RC 1, RC 2, RC 3)',
        'Infection Control',
        'Updated Clinical Manual',
        'Lippincott Nursing Procedures Book',
        'Dietary P&P and Manual',
        'Operations Manuals (Ops 1, Ops 2, Ops 3, Ops 4, Ops 5)',
        'Anderson\'s Medical Records Manual',
        'Omnicare Policy and Procedure Manual for Skilled Nursing',
        'Omnicare IV Policy and Procedure Manual',
        'Run for Restorative Manual',
        'Annual',
        'Title 22',
        'Life Safety Code',
        'PPD/Staffing Audit',
        'Risk-at least weekly',
        'Falling Stars Program-weekly',
        'Pharmacy/Psychotropics-monthly',
        'Infection Control-monthly, with sub-committee at least quarterly',
        'QA&A/QAPI-monthly',
        'Policy and Procedure-annually and whenever necessary',
        'Safety Committee-monthly',
        'Administrative/Staff Meetings-monthly',
        'Daily Morning Meeting with mini-UR',
        'Weekly thorough UR',
        'Restorative Nursing Program Meeting-weekly/monthly',
    ];

    public string $employeeNum;

    public ?int $jobCodeId = null;

    /** @var list<array<string, mixed>> */
    public array $orientationRows = [];

    /**
     * Stored under {@see summaryStorageKey()} in bp_emp_checklists.items.
     *
     * @var array{reviewer_name?: string, reviewer_title?: string, review_sign_date?: string, employee_name?: string, employee_title?: string, employee_sign_date?: string, reviewer_comments?: string, employee_comments?: string, notes?: string}
     */
    public array $orientationSummary = [];

    /** @var array<string, mixed> */
    public array $empChecklistItems = [];

    /** @var array<int|string, string> */
    public array $userNamesById = [];

    public string $positionTitle = '';

    public ?string $summaryFeedback = null;

    public ?string $summaryError = null;

    public string $reviewerNameDisplay = '';

    public string $reviewerTitleDisplay = '';

    public string $employeeNameDisplay = '';

    public string $employeeTitleDisplay = '';

    /** @var self::WORKFLOW_* */
    public string $orientationWorkflowStatus = self::WORKFLOW_DRAFT;

    /**
     * @var list<array{status: string, recorded_at: string, actor_user_id: ?int, actor_label: string, detail: string}>
     */
    public array $orientationStatusHistory = [];

    public function mount(string $employeeNum, ?int $jobCodeId = null): void
    {
        $this->employeeNum = $employeeNum;
        $this->jobCodeId = $jobCodeId;
        $this->userNamesById = User::query()->pluck('name', 'id')->all();

        $employee = BPEmployee::with('currentAssignment.position')
            ->where('employee_num', $employeeNum)
            ->first();

        if ($employee) {
            $this->positionTitle = $employee->currentAssignment?->position?->title
                ?? ($employee->position ?? 'No Position Assigned');
        } else {
            $this->positionTitle = 'No Position Assigned';
        }

        $this->empChecklistItems = optional(BPEmpChecklist::query()
            ->where('employee_num', $this->employeeNum)
            ->first())
            ->items ?? [];

        $items = OrientationChecklistSource::checklistItemsForPosition($this->jobCodeId);
        $this->orientationRows = $this->buildRows($items);

        $stored = [];
        $key = $this->summaryStorageKey();
        if (isset($this->empChecklistItems[$key]) && is_array($this->empChecklistItems[$key])) {
            $stored = $this->empChecklistItems[$key];
        }

        $this->orientationSummary = array_merge([
            'reviewer_name' => (string) ($stored['reviewer_name'] ?? ''),
            'reviewer_title' => (string) ($stored['reviewer_title'] ?? ''),
            'review_sign_date' => (string) ($stored['review_sign_date'] ?? $stored['review_date'] ?? ''),
            'employee_name' => (string) ($stored['employee_name'] ?? ''),
            'employee_title' => (string) ($stored['employee_title'] ?? ''),
            'employee_sign_date' => (string) ($stored['employee_sign_date'] ?? ''),
            'reviewer_comments' => (string) ($stored['reviewer_comments'] ?? ''),
            'employee_comments' => (string) ($stored['employee_comments'] ?? ''),
            'notes' => (string) ($stored['notes'] ?? ''),
        ], []);

        $storedStatus = (string) ($stored['workflow_status'] ?? '');
        $this->orientationWorkflowStatus = in_array($storedStatus, [
            self::WORKFLOW_DRAFT,
            self::WORKFLOW_EMPLOYEE_SIGNATURE,
            self::WORKFLOW_REVIEWER_SIGNATURE,
            self::WORKFLOW_COMPLETED,
        ], true) ? $storedStatus : self::WORKFLOW_DRAFT;

        $hist = $stored['status_history'] ?? [];
        $this->orientationStatusHistory = is_array($hist) ? array_values($hist) : [];

        $user = auth()->user();
        if ($user) {
            $this->reviewerNameDisplay = (string) ($user->name ?? '');
            $this->reviewerTitleDisplay = (string) (data_get($user, 'title') ?? '');
            if (($this->orientationSummary['reviewer_name'] ?? '') === '') {
                $this->orientationSummary['reviewer_name'] = $this->reviewerNameDisplay;
            }
            if (($this->orientationSummary['reviewer_title'] ?? '') === '') {
                $this->orientationSummary['reviewer_title'] = $this->reviewerTitleDisplay;
            }
        }

        if ($employee) {
            $empDisplay = trim(($employee->last_name ?? '').', '.($employee->first_name ?? ''), ', ');
            $this->employeeNameDisplay = $empDisplay;
            $this->employeeTitleDisplay = (string) (
                $employee->currentAssignment?->position?->title ?? ($employee->position ?? '')
            );
            if ($empDisplay !== '' && ($this->orientationSummary['employee_name'] ?? '') === '') {
                $this->orientationSummary['employee_name'] = $empDisplay;
            }
            if (($this->orientationSummary['employee_title'] ?? '') === '') {
                $this->orientationSummary['employee_title'] = $this->employeeTitleDisplay;
            }
        }
    }

    public function workflowStatusLabel(): string
    {
        return match ($this->orientationWorkflowStatus) {
            self::WORKFLOW_DRAFT => 'Draft',
            self::WORKFLOW_EMPLOYEE_SIGNATURE => 'Employee Signature Needed',
            self::WORKFLOW_REVIEWER_SIGNATURE => 'Reviewer Signature Needed',
            self::WORKFLOW_COMPLETED => 'Completed',
            default => $this->orientationWorkflowStatus,
        };
    }

    public function workflowHistoryStatusLabel(string $status): string
    {
        return match ($status) {
            self::WORKFLOW_DRAFT => 'Draft',
            self::WORKFLOW_EMPLOYEE_SIGNATURE => 'Employee Signature Needed',
            self::WORKFLOW_REVIEWER_SIGNATURE => 'Reviewer Signature Needed',
            self::WORKFLOW_COMPLETED => 'Completed',
            default => $status,
        };
    }

    public function workflowStatusBadgeClass(): string
    {
        return match ($this->orientationWorkflowStatus) {
            self::WORKFLOW_DRAFT => 'bg-slate-200 text-slate-900',
            self::WORKFLOW_EMPLOYEE_SIGNATURE => 'bg-sky-100 text-sky-900',
            self::WORKFLOW_REVIEWER_SIGNATURE => 'bg-amber-100 text-amber-900',
            self::WORKFLOW_COMPLETED => 'bg-emerald-100 text-emerald-900',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function saveOrientationDraft(): void
    {
        $this->resetValidation();
        $this->summaryFeedback = null;
        $this->summaryError = null;

        if ($this->orientationWorkflowStatus !== self::WORKFLOW_DRAFT) {
            $this->summaryError = 'Draft saving is only available while the checklist is in Draft status.';

            return;
        }

        $this->persistWorkflow(self::WORKFLOW_DRAFT, 'Saved as draft (reviewer comments and form updated).');
        $this->summaryFeedback = 'Draft saved.';
    }

    public function submitOrientationChecklist(): void
    {
        $this->resetValidation();
        $this->summaryFeedback = null;
        $this->summaryError = null;

        if ($this->orientationWorkflowStatus !== self::WORKFLOW_DRAFT) {
            $this->summaryError = 'This checklist has already been submitted.';

            return;
        }

        $this->reloadEmpChecklistItems();

        $stats = $this->orientationStats;
        $total = (int) ($stats['total'] ?? 0);
        $confirmed = (int) ($stats['confirmed'] ?? 0);

        if ($total < 1) {
            $this->summaryError = 'Cannot submit: there are no orientation items that require confirmation for this position.';

            return;
        }

        if ($confirmed < $total) {
            $this->summaryError = sprintf(
                'Cannot submit: all orientation items must be confirmed first (%d of %d confirmed). Complete the checklist, then try again.',
                $confirmed,
                $total
            );

            return;
        }

        $this->persistWorkflow(
            self::WORKFLOW_EMPLOYEE_SIGNATURE,
            'Checklist submitted. Employee signature required.'
        );
        $this->summaryFeedback = 'Checklist submitted. Status is now Employee Signature Needed.';
    }

    public function completeEmployeeSignatureStep(): void
    {
        $this->resetValidation();
        $this->summaryFeedback = null;
        $this->summaryError = null;

        if ($this->orientationWorkflowStatus !== self::WORKFLOW_EMPLOYEE_SIGNATURE) {
            $this->summaryError = 'Employee signature is not required at this step.';

            return;
        }

        $this->validate([
            'orientationSummary.employee_sign_date' => 'required|date',
        ], attributes: [
            'orientationSummary.employee_sign_date' => 'employee sign date',
        ]);

        $this->persistWorkflow(
            self::WORKFLOW_REVIEWER_SIGNATURE,
            'Employee signature recorded.'
        );
        $this->summaryFeedback = 'Employee signature saved. Status is now Reviewer Signature Needed.';
    }

    public function completeReviewerSignatureStep(): void
    {
        $this->resetValidation();
        $this->summaryFeedback = null;
        $this->summaryError = null;

        if ($this->orientationWorkflowStatus !== self::WORKFLOW_REVIEWER_SIGNATURE) {
            $this->summaryError = 'Reviewer signature is not required at this step.';

            return;
        }

        $this->validate([
            'orientationSummary.review_sign_date' => 'required|date',
        ], attributes: [
            'orientationSummary.review_sign_date' => 'reviewer sign date',
        ]);

        $this->persistWorkflow(
            self::WORKFLOW_COMPLETED,
            'Reviewer signature recorded. Orientation checklist completed.'
        );
        $this->summaryFeedback = 'Orientation checklist completed.';
    }

    /**
     * @param  self::WORKFLOW_*  $newStatus
     */
    private function persistWorkflow(string $newStatus, string $historyDetail): void
    {
        $row = BPEmpChecklist::query()->firstOrCreate(
            ['employee_num' => $this->employeeNum],
            ['items' => []]
        );

        $items = is_array($row->items) ? $row->items : [];
        $key = $this->summaryStorageKey();
        $existing = (isset($items[$key]) && is_array($items[$key])) ? $items[$key] : [];

        $history = $existing['status_history'] ?? [];
        if (! is_array($history)) {
            $history = [];
        }

        $user = auth()->user();
        $history[] = [
            'status' => $newStatus,
            'recorded_at' => now()->toIso8601String(),
            'actor_user_id' => $user?->id,
            'actor_label' => $user instanceof User ? (string) ($user->name ?? 'User') : 'System',
            'detail' => $historyDetail,
        ];

        $payload = $this->orientationSummary;

        $items[$key] = array_merge($existing, [
            'reviewer_name' => $this->reviewerNameDisplay !== '' ? $this->reviewerNameDisplay : (string) ($payload['reviewer_name'] ?? ''),
            'reviewer_title' => $this->reviewerTitleDisplay !== '' ? $this->reviewerTitleDisplay : (string) ($payload['reviewer_title'] ?? ''),
            'review_sign_date' => (string) ($payload['review_sign_date'] ?? ''),
            'employee_name' => $this->employeeNameDisplay !== '' ? $this->employeeNameDisplay : (string) ($payload['employee_name'] ?? ''),
            'employee_title' => $this->employeeTitleDisplay !== '' ? $this->employeeTitleDisplay : (string) ($payload['employee_title'] ?? ''),
            'employee_sign_date' => (string) ($payload['employee_sign_date'] ?? ''),
            'reviewer_comments' => (string) ($payload['reviewer_comments'] ?? ''),
            'employee_comments' => (string) ($payload['employee_comments'] ?? ''),
            'notes' => (string) ($payload['notes'] ?? ''),
            'workflow_status' => $newStatus,
            'status_history' => $history,
            'saved_at' => now()->toIso8601String(),
        ]);

        $row->items = $items;
        $row->save();
        $row->refresh();

        $this->empChecklistItems = is_array($row->items) ? $row->items : [];
        $this->orientationWorkflowStatus = $newStatus;
        $this->orientationStatusHistory = $history;
    }

    protected function summaryStorageKey(): string
    {
        $suffix = $this->jobCodeId !== null ? (string) $this->jobCodeId : 'none';

        return 'part_e_orientation_summary_'.$suffix;
    }

    private function reloadEmpChecklistItems(): void
    {
        $this->empChecklistItems = optional(BPEmpChecklist::query()
            ->where('employee_num', $this->employeeNum)
            ->first())
            ->items ?? [];
    }

    /**
     * @param  Collection<int, ChecklistItem>  $items
     * @return list<array<string, mixed>>
     */
    protected function buildRows(Collection $items): array
    {
        $raw = $items->values()->all();
        $rows = [];

        foreach ($raw as $index => $item) {
            $name = (string) $item->name;
            $indent = $this->parseIndentLevel($name);
            $displayName = preg_replace('/^-+/', '', $name);
            $displayName = is_string($displayName) ? trim($displayName) : trim($name);

            $nextIndent = 0;
            if (isset($raw[$index + 1])) {
                $nextIndent = $this->parseIndentLevel((string) $raw[$index + 1]->name);
            }

            $isParent = isset($raw[$index + 1]) && $nextIndent > $indent;

            $rows[] = [
                'id' => (int) $item->id,
                'name' => $name,
                'displayName' => $displayName,
                'doc_type_id' => (int) ($item->doc_type_id ?? 0),
                'indentLevel' => $indent,
                'isParent' => $isParent,
                'disabled' => (bool) ($item->disabled ?? false),
                'checklistKey' => 'item_'.$item->id,
            ];
        }

        return $rows;
    }

    protected function parseIndentLevel(string $name): int
    {
        if (preg_match('/^(-+)/', $name, $matches)) {
            return strlen($matches[1]);
        }

        if (in_array($name, self::LEGACY_CHILD_NAMES, true)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $itemRow
     */
    public function resolveChecklistEntry(array $itemRow): ?object
    {
        $items = $this->empChecklistItems;
        $itemId = $itemRow['id'] ?? null;
        $name = $itemRow['name'] ?? '';

        if ($itemId && isset($items['item_'.$itemId])) {
            return (object) $items['item_'.$itemId];
        }

        if ($name !== '' && isset($items[$name])) {
            return (object) $items[$name];
        }

        return null;
    }

    public function verifiedByLabel(?object $empChecklist): string
    {
        if (! $empChecklist || ! isset($empChecklist->verified_by)) {
            return '';
        }
        $id = $empChecklist->verified_by;
        if (is_numeric($id) && isset($this->userNamesById[(int) $id])) {
            return (string) $this->userNamesById[(int) $id];
        }

        return (string) $id;
    }

    /**
     * @return array{total: int, confirmed: int}
     */
    #[Computed]
    public function orientationStats(): array
    {
        $total = 0;
        $confirmed = 0;

        foreach ($this->orientationRows as $row) {
            if (! empty($row['isParent']) || ! empty($row['disabled'])) {
                continue;
            }

            $total++;
            $emp = $this->resolveChecklistEntry($row);

            $isDone = $emp && (
                (! empty($emp->verified_by))
                || (! empty($emp->on_file))
            );

            if ($isDone) {
                $confirmed++;
            }
        }

        return [
            'total' => $total,
            'confirmed' => $confirmed,
        ];
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-e-orientation-checklist');
    }
}
