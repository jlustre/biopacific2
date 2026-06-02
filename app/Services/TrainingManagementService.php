<?php

namespace App\Services;

use App\Models\BPEmpChecklist;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Models\Department;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\Facility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrainingManagementService
{
    public function __construct(
        protected MemberDashboardService $memberDashboard
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildPage(User $user, Request $request): array
    {
        $facility = $this->resolveFacility($user, $request);
        $facilities = $this->facilitiesForUser($user);

        if (!$facility && $facilities->isNotEmpty()) {
            $facility = $facilities->first();
        }

        if (!$facility) {
            return [
                'facility' => null,
                'facilities' => $facilities,
                'canPickFacility' => $facilities->count() > 1,
                'summary' => $this->emptySummary(),
                'employees' => [],
                'programs' => [],
                'competency_queue' => [],
                'departments' => collect(),
                'filters' => $this->filtersFromRequest($request),
                'generated_at' => now(),
            ];
        }

        $filters = $this->filtersFromRequest($request);
        if ($request->filled('facility_id')) {
            $picked = $facilities->firstWhere('id', (int) $request->facility_id);
            if ($picked) {
                $facility = $picked;
            }
        }

        $employees = BPEmployee::query()
            ->with(['currentAssignment.position.department', 'currentAssignment.facility'])
            ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facility->id))
            ->when($filters['department_id'], function ($q, $departmentId) {
                $q->whereHas('currentAssignment.position', fn ($pq) => $pq->where('department_id', $departmentId));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $employees->pluck('employee_num')->filter()->all())
            ->get()
            ->keyBy('employee_num');

        $employeeRows = [];
        $summary = $this->emptySummary();
        $summary['total_employees'] = $employees->count();

        foreach ($employees as $employee) {
            $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $empItems = is_array($items) ? $items : [];

            $row = $this->memberDashboard->summarizeEmployeeTraining($employee, $empItems);
            $employeeRows[] = $row;

            if ($row['issue_count'] > 0) {
                $summary['employees_with_issues']++;
            }
            $summary['incomplete_orientation'] += $row['incomplete_orientation'];
            $summary['unsigned_competency'] += $row['unsigned_competency'];
            $summary['incomplete_training'] += $row['incomplete_training'];
            $summary['overdue_count'] += $row['overdue_count'];
        }

        $summary['compliant_employees'] = max(0, $summary['total_employees'] - $summary['employees_with_issues']);
        $summary['compliance_rate'] = $summary['total_employees'] > 0
            ? (int) round(($summary['compliant_employees'] / $summary['total_employees']) * 100)
            : 100;

        $employeeRows = $this->filterEmployeeRows($employeeRows, $filters);
        $programs = $this->buildProgramStats($employees, $checklistsByNum);
        $competencyQueue = $this->buildCompetencyQueue($facility->id);

        $departmentIds = $employees
            ->map(fn (BPEmployee $e) => $e->currentAssignment?->position?->department_id)
            ->filter()
            ->unique()
            ->values();

        $departments = $departmentIds->isEmpty()
            ? collect()
            : Department::query()->whereIn('id', $departmentIds)->orderBy('name')->get(['id', 'name']);

        return [
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'key' => $facility->slug ?? (string) $facility->id,
            ],
            'facilities' => $facilities,
            'canPickFacility' => $facilities->count() > 1,
            'summary' => $summary,
            'employees' => $employeeRows,
            'programs' => $programs,
            'competency_queue' => $competencyQueue,
            'departments' => $departments,
            'filters' => $filters,
            'generated_at' => now(),
            'employees_list_url' => route('admin.facility.employees', ['facility' => $facility->slug ?? $facility->id]),
            'checklist_items_url' => route('admin.checklist-items.index'),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function emptySummary(): array
    {
        return [
            'total_employees' => 0,
            'employees_with_issues' => 0,
            'compliant_employees' => 0,
            'compliance_rate' => 0,
            'incomplete_orientation' => 0,
            'unsigned_competency' => 0,
            'incomplete_training' => 0,
            'overdue_count' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'status' => (string) $request->input('status', 'all'),
            'department_id' => $request->filled('department_id') ? (int) $request->department_id : null,
            'facility_id' => $request->filled('facility_id') ? (int) $request->facility_id : null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    protected function filterEmployeeRows(array $rows, array $filters): array
    {
        $search = mb_strtolower($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        $filtered = array_values(array_filter($rows, function (array $row) use ($search, $status) {
            if ($status === 'needs_attention' && ($row['issue_count'] ?? 0) === 0) {
                return false;
            }
            if ($status === 'compliant' && ($row['issue_count'] ?? 0) > 0) {
                return false;
            }
            if ($search === '') {
                return true;
            }

            $haystack = mb_strtolower(implode(' ', [
                $row['name'] ?? '',
                $row['employee_num'] ?? '',
                $row['position'] ?? '',
                $row['department'] ?? '',
            ]));

            return str_contains($haystack, $search);
        }));

        usort($filtered, fn ($a, $b) => ($b['issue_count'] ?? 0) <=> ($a['issue_count'] ?? 0));

        return $filtered;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, BPEmployee>  $employees
     * @param  \Illuminate\Support\Collection<string, BPEmpChecklist>  $checklistsByNum
     * @return list<array<string, mixed>>
     */
    protected function buildProgramStats($employees, $checklistsByNum): array
    {
        $programs = ChecklistItem::query()
            ->whereIn('section', ['PART B', 'PART C', 'PART D', 'PART E'])
            ->orderBy('section')
            ->orderBy('order')
            ->get();

        $stats = [];

        foreach ($programs as $program) {
            $incomplete = 0;
            $complete = 0;
            $notApplicable = 0;

            foreach ($employees as $employee) {
                $positionId = $employee->currentAssignment?->position_id;
                $positionIds = $program->position_ids;
                if (is_array($positionIds) && count($positionIds) > 0 && $positionId && !in_array($positionId, $positionIds, true)) {
                    $notApplicable++;

                    continue;
                }

                $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
                $key = 'item_' . $program->id;
                $state = is_array($items) ? ($items[$key] ?? $items[$program->name] ?? null) : null;

                if ($this->isChecklistItemComplete($state)) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }

            $applicable = $complete + $incomplete;
            if ($applicable === 0) {
                continue;
            }

            $stats[] = [
                'id' => $program->id,
                'name' => $program->name,
                'section' => $program->section,
                'complete' => $complete,
                'incomplete' => $incomplete,
                'completion_rate' => (int) round(($complete / $applicable) * 100),
            ];
        }

        usort($stats, fn ($a, $b) => ($a['completion_rate'] ?? 0) <=> ($b['completion_rate'] ?? 0));

        return array_slice($stats, 0, 12);
    }

    protected function isChecklistItemComplete(mixed $state): bool
    {
        if (!is_array($state)) {
            return false;
        }

        if (!empty($state['on_file']) && !empty($state['verified_dt'])) {
            return true;
        }

        $status = (string) ($state['status'] ?? $state['workflow_status'] ?? '');

        return in_array($status, ['complete', 'completed', 'signed', 'verified'], true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildCompetencyQueue(int $facilityId): array
    {
        $employeeNums = BPEmployee::query()
            ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facilityId))
            ->pluck('employee_num')
            ->filter()
            ->all();

        if ($employeeNums === []) {
            return [];
        }

        return EmployeeCompetencyAssessment::query()
            ->with(['period'])
            ->whereIn('employee_num', $employeeNums)
            ->whereNull('employee_signed_at')
            ->whereIn('status', ['submitted', 'completed'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(function (EmployeeCompetencyAssessment $assessment) {
                $employee = BPEmployee::query()
                    ->where('employee_num', $assessment->employee_num)
                    ->with('currentAssignment.position')
                    ->first();

                return [
                    'id' => $assessment->id,
                    'employee_num' => $assessment->employee_num,
                    'employee_name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: $assessment->employee_num,
                    'position' => $employee?->currentAssignment?->position?->title ?? '—',
                    'period' => $this->formatAssessmentPeriod($assessment),
                    'status' => $assessment->status,
                    'updated_at' => optional($assessment->updated_at)->diffForHumans(),
                    'manage_url' => route('admin.employees.edit', $assessment->employee_num) . '?tab=competency',
                ];
            })
            ->all();
    }

    protected function resolveFacility(User $user, Request $request): ?Facility
    {
        if ($request->filled('facility_id')) {
            $facility = Facility::find((int) $request->facility_id);
            if ($facility && $this->userCanAccessFacility($user, $facility)) {
                return $facility;
            }
        }

        if ($user->facility_id) {
            return Facility::find($user->facility_id);
        }

        $bpEmployee = $user->resolvedBpEmployee(['currentAssignment.facility']);

        return $bpEmployee?->currentAssignment?->facility;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Facility>
     */
    protected function facilitiesForUser(User $user)
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return Facility::query()->orderBy('name')->get(['id', 'name', 'slug']);
        }

        $facility = $this->resolveFacility($user, request());

        return $facility ? collect([$facility]) : collect();
    }

    protected function userCanAccessFacility(User $user, Facility $facility): bool
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return true;
        }

        return (int) $user->facility_id === (int) $facility->id;
    }

    protected function formatAssessmentPeriod(EmployeeCompetencyAssessment $assessment): string
    {
        $period = $assessment->period;
        if (!$period) {
            return 'Current period';
        }

        $from = $period->date_from?->format('M j, Y');
        $to = $period->date_to?->format('M j, Y');

        if ($from && $to) {
            return "{$from} – {$to}";
        }

        return 'Assessment period #' . ($period->period_sequence ?? $period->id);
    }
}
