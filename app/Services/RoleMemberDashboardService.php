<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\BPEmpChecklist;
use App\Models\BPEmpCredential;
use App\Models\ChecklistItem;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\Facility;
use App\Models\User;
use App\Support\AssessmentWorkflowStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RoleMemberDashboardService
{
    public function __construct(
        protected MemberDashboardService $memberDashboard
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position.department',
            'currentAssignment.facility',
        ]);

        $positionTitle = $bpEmployee?->currentAssignment?->position?->title;
        $persona = $this->resolvePersona($user, $positionTitle);
        $personaLabel = $this->personaLabel($persona, $user);

        if ($this->usesLeadershipDashboard($persona, $user)) {
            return $this->buildLeadershipDashboard($user, $bpEmployee, $persona, $personaLabel);
        }

        return $this->buildStaffDashboard($user, $bpEmployee, $persona, $personaLabel);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildLeadershipDashboard(
        User $user,
        ?BPEmployee $bpEmployee,
        string $persona,
        string $personaLabel
    ): array {
        $scope = $this->resolveScope($user, $bpEmployee, $persona);
        $excludeSelf = $scope['type'] === 'facility' ? null : $bpEmployee?->employee_num;
        $team = $this->loadTeamEmployees($scope['facility'], $scope['department_id'], $excludeSelf);
        $teamScopeLabel = $this->teamScopeLabel($scope);
        $operations = $this->summarizeTeamOperations($team, $teamScopeLabel);
        $expiringDocuments = $this->buildExpiringDocumentsPanel($team);
        $assessmentsDue = $this->buildAssessmentsDuePanel($team);
        $personalKpis = $this->buildPersonalKpis($user);

        $trainingRoute = \Illuminate\Support\Facades\Route::has('admin.training-management.index')
            ? route('admin.training-management.index', array_filter([
                'department_id' => $scope['department_id'],
                'facility_id' => $scope['facility']?->id,
            ]))
            : route('member.trainings');

        return [
            'roleDashboardMode' => 'leadership',
            'dashboardPersona' => $persona,
            'dashboardPersonaLabel' => $personaLabel,
            'dashboardScopeType' => $scope['type'],
            'dashboardScopeLabel' => $scope['label'],
            'dashboardFacilityName' => $scope['facility']?->name ?? ($scope['type'] === 'organization' ? 'All facilities' : '—'),
            'dashboardIntro' => $scope['intro'],
            'dashboardKpis' => $personalKpis,
            'dashboardPersonalKpis' => $personalKpis,
            'dashboardTeamKpis' => $operations['kpis'],
            'dashboardPersonalStatsTitle' => 'My stats',
            'dashboardTeamStatsTitle' => $this->teamStatsTitle($scope),
            'dashboardTeamStatsDescription' => $this->teamStatsDescription($scope),
            'dashboardActionQueue' => $operations['action_queue'],
            'dashboardAwareness' => $operations['awareness'],
            'dashboardQuickActions' => $this->leadershipQuickActions($persona, $trainingRoute),
            'dashboardTeamCount' => $operations['kpis'][0]['value'] ?? 0,
            'dashboardDepartmentId' => $scope['department_id'],
            'dashboardFacilityId' => $scope['facility']?->id,
            'dashboardExpiringDocuments' => $expiringDocuments['items'],
            'dashboardExpiringDocumentsTotal' => $expiringDocuments['total'],
            'dashboardAssessmentsDue' => $assessmentsDue['items'],
            'dashboardFacilityDocumentsUrl' => $this->facilityDocumentsUrl($scope['facility']),
        ];
    }

    protected function facilityDocumentsUrl(?Facility $facility): ?string
    {
        if (!$facility || !\Illuminate\Support\Facades\Route::has('admin.facility.documents')) {
            return null;
        }

        $key = $facility->slug ?? $facility->id;

        return route('admin.facility.documents', ['facility' => $key]);
    }

    /**
     * HR operations panels for a facility (or department) scope — used by the Facility Dashboard.
     *
     * @return array{
     *     team_count: int,
     *     kpis: list<array<string, mixed>>,
     *     action_queue: list<array<string, mixed>>,
     *     awareness: list<array<string, mixed>>,
     *     expiring_documents: list<array<string, mixed>>,
     *     expiring_documents_total: int,
     *     assessments_due: list<array<string, mixed>>,
     *     facility_documents_url: ?string
     * }
     */
    public function buildFacilityOperationsPanels(
        Facility $facility,
        ?int $departmentId = null,
        ?string $excludeEmployeeNum = null
    ): array {
        $team = $this->loadTeamEmployees($facility, $departmentId, $excludeEmployeeNum);
        $operations = $this->summarizeTeamOperations($team, $departmentId ? 'department team' : 'facility team');
        $expiring = $this->buildExpiringDocumentsPanel($team);

        return [
            'team_count' => $team->count(),
            'kpis' => $operations['kpis'],
            'action_queue' => $operations['action_queue'],
            'awareness' => $operations['awareness'],
            'expiring_documents' => $expiring['items'],
            'expiring_documents_total' => $expiring['total'],
            'assessments_due' => $this->buildAssessmentsDuePanel($team)['items'],
            'facility_documents_url' => $this->facilityDocumentsUrl($facility),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildStaffDashboard(
        User $user,
        ?BPEmployee $bpEmployee,
        string $persona,
        string $personaLabel
    ): array {
        $payload = $this->memberDashboard->build($user);
        $stats = $payload['stats'] ?? [];
        $todos = collect($payload['todos'] ?? [])->where('done', false)->take(6)->values()->all();

        return [
            'roleDashboardMode' => 'staff',
            'dashboardPersona' => $persona,
            'dashboardPersonaLabel' => $personaLabel,
            'dashboardScopeLabel' => $payload['facilityName'] ?? 'Your workplace',
            'dashboardIntro' => 'Your work queue — account and personal details live under My Profile.',
            'dashboardKpis' => $this->buildPersonalKpis($user, $stats),
            'dashboardActionQueue' => [],
            'dashboardMyTasks' => $todos,
            'dashboardQuickActions' => $this->staffQuickActions($persona),
            'stats' => $stats,
        ];
    }

    /**
     * Personal compliance KPIs for the member work-queue dashboard (/dashboard).
     *
     * @param  array<string, mixed>|null  $stats
     * @return list<array<string, mixed>>
     */
    protected function buildPersonalKpis(User $user, ?array $stats = null): array
    {
        $stats ??= ($this->memberDashboard->build($user)['stats'] ?? []);

        $myActions = (int) ($stats['documents_needed'] ?? 0)
            + (int) ($stats['trainings_needs_action'] ?? 0)
            + (int) (($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0));

        $credentialCount = (int) (($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0));
        if ($credentialCount === 0 && ($stats['certifications_needs_attention'] ?? 0) > 0) {
            $credentialCount = (int) $stats['certifications_needs_attention'];
        }

        return [
            [
                'label' => 'My open tasks',
                'value' => $myActions,
                'hint' => 'Documents, training, credentials',
                'route' => route('member.documents'),
                'tone' => 'brand',
                'icon' => 'fa-list-check',
            ],
            [
                'label' => 'My Trainings',
                'value' => (int) ($stats['trainings_needs_action'] ?? 0),
                'hint' => ($stats['trainings_pending_signature'] ?? 0) > 0
                    ? ($stats['trainings_pending_signature'] . ' need signature')
                    : (($stats['trainings_total'] ?? 0) > 0
                        ? ($stats['trainings_total'] . ' assigned')
                        : 'Required items'),
                'route' => route('member.trainings'),
                'tone' => 'amber',
                'icon' => 'fa-graduation-cap',
            ],
            [
                'label' => 'My Credentials',
                'value' => $credentialCount,
                'hint' => ($stats['certifications_expired'] ?? 0) > 0
                    ? ($stats['certifications_expired'] . ' expired')
                    : 'Expiring or expired',
                'route' => route('member.certifications'),
                'tone' => 'rose',
                'icon' => 'fa-id-card',
            ],
            [
                'label' => 'My Documents',
                'value' => (int) ($stats['documents_needed'] ?? 0),
                'hint' => ($stats['signatures_needed'] ?? 0) > 0
                    ? ($stats['signatures_needed'] . ' need signature')
                    : 'Checklist gaps',
                'route' => route('member.documents'),
                'tone' => 'teal',
                'icon' => 'fa-folder-open',
            ],
        ];
    }

    protected function usesLeadershipDashboard(string $persona, User $user): bool
    {
        if (in_array($persona, ['facility-admin', 'facility-dsd', 'don', 'rdhr', 'ssd', 'activities-director', 'department-leader'], true)) {
            return true;
        }

        $leaderRoles = config('member-portal.facility_manager_roles', []);

        return method_exists($user, 'hasRole') && $user->hasRole($leaderRoles);
    }

    protected function resolvePersona(User $user, ?string $positionTitle): string
    {
        if ($this->memberDashboard->userHasRole($user, 'facility-admin')) {
            return 'facility-admin';
        }

        if ($this->memberDashboard->userHasRole($user, 'facility-dsd')) {
            return 'facility-dsd';
        }

        if ($this->memberDashboard->userHasRole($user, 'don')) {
            return 'don';
        }

        if ($this->memberDashboard->userHasRole($user, 'rdhr')) {
            return 'rdhr';
        }

        $normalizedTitle = strtolower(trim((string) $positionTitle));

        if ($normalizedTitle !== '') {
            if (preg_match('/director of nursing|\bdon\b/', $normalizedTitle) === 1) {
                return 'don';
            }

            if (preg_match('/social services director|\bssd\b/', $normalizedTitle) === 1) {
                return 'ssd';
            }

            if (preg_match('/activities director/', $normalizedTitle) === 1) {
                return 'activities-director';
            }
        }

        if ($this->memberDashboard->userHasRole($user, ['ssd', 'activities-director', 'facility-ssd'])) {
            return 'department-leader';
        }

        return 'employee-default';
    }

    protected function personaLabel(string $persona, User $user): string
    {
        if ($persona === 'employee-default' && method_exists($user, 'primaryRoleLabel')) {
            $roleLabel = $user->primaryRoleLabel();

            return $roleLabel !== 'User' ? $roleLabel : 'Team Member';
        }

        return match ($persona) {
            'facility-admin' => 'Facility Administrator',
            'facility-dsd' => 'Director of Staff Development',
            'don' => 'Director of Nursing',
            'rdhr' => 'Regional HR',
            'ssd', 'department-leader' => 'Department Leader',
            'activities-director' => 'Activities Director',
            'licensed-nurse' => 'Licensed Nurse',
            'cna' => 'Certified Nursing Assistant',
            default => 'Team Member',
        };
    }

    /**
     * @return array{type: string, label: string, intro: string, facility: ?Facility, department_id: ?int, department_name: ?string}
     */
    protected function resolveScope(User $user, ?BPEmployee $bpEmployee, string $persona): array
    {
        if ($persona === 'rdhr' || $this->memberDashboard->userHasRole($user, 'rdhr')) {
            return [
                'type' => 'organization',
                'label' => 'All employees',
                'intro' => 'Organization-wide dashboard - personal items for you, plus staff priorities across all facilities.',
                'facility' => null,
                'department_id' => null,
                'department_name' => null,
            ];
        }

        $facility = $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }
        if (!$facility) {
            $facility = $bpEmployee?->currentAssignment?->facility;
        }

        $departmentId = $bpEmployee?->currentAssignment?->position?->department_id;
        $departmentName = $bpEmployee?->currentAssignment?->position?->department?->name;

        $facilityWide = in_array($persona, ['facility-admin', 'facility-dsd', 'rdhr'], true)
            || $this->memberDashboard->userHasRole($user, ['facility-admin', 'facility-dsd', 'rdhr']);

        if (!$facilityWide && $departmentId && filled($departmentName)) {
            return [
                'type' => 'department',
                'label' => $departmentName,
                'intro' => "Department dashboard for {$departmentName} — staff items that need your attention.",
                'facility' => $facility,
                'department_id' => (int) $departmentId,
                'department_name' => $departmentName,
            ];
        }

        $facilityLabel = $facility?->name ?? 'your facility';

        return [
            'type' => 'facility',
            'label' => $facilityLabel,
            'intro' => "Facility-wide dashboard — priorities and staff actions for {$facilityLabel}.",
            'facility' => $facility,
            'department_id' => null,
            'department_name' => null,
        ];
    }

    /**
     * @param  array{type: string, label: string, intro: string, facility: ?Facility, department_id: ?int, department_name: ?string}  $scope
     */
    protected function teamScopeLabel(array $scope): string
    {
        return match ($scope['type']) {
            'organization' => 'all employees',
            'department' => 'your department',
            default => 'your facility',
        };
    }

    /**
     * @param  array{type: string, label: string, intro: string, facility: ?Facility, department_id: ?int, department_name: ?string}  $scope
     */
    protected function teamStatsTitle(array $scope): string
    {
        return match ($scope['type']) {
            'organization' => 'All employee stats',
            'department' => 'Team stats',
            default => 'Facility employee stats',
        };
    }

    /**
     * @param  array{type: string, label: string, intro: string, facility: ?Facility, department_id: ?int, department_name: ?string}  $scope
     */
    protected function teamStatsDescription(array $scope): string
    {
        return match ($scope['type']) {
            'organization' => 'Counts and risk indicators across every facility.',
            'department' => 'Counts and risk indicators for employees in your department.',
            default => 'Counts and risk indicators for employees under your facility.',
        };
    }

    /**
     * @return Collection<int, BPEmployee>
     */
    protected function loadTeamEmployees(?Facility $facility, ?int $departmentId, ?string $excludeEmployeeNum): Collection
    {
        $query = BPEmployee::query()
            ->with(['currentAssignment.position.department'])
            ->whereHas('currentAssignment', function ($q) use ($facility) {
                if ($facility) {
                    $q->where('facility_id', $facility->id);
                }
            })
            ->when($departmentId, function ($q, $departmentId) {
                $q->whereHas('currentAssignment.position', fn ($pq) => $pq->where('department_id', $departmentId));
            })
            ->when($excludeEmployeeNum, fn ($q, $num) => $q->where('employee_num', '!=', $num))
            ->orderBy('last_name')
            ->orderBy('first_name');

        return $query->get();
    }

    /**
     * @param  Collection<int, BPEmployee>  $team
     * @return array{kpis: list<array<string, mixed>>, action_queue: list<array<string, mixed>>, awareness: list<array<string, mixed>>}
     */
    protected function summarizeTeamOperations(Collection $team, string $scopeLabel = 'your team'): array
    {
        if ($team->isEmpty()) {
            return [
                'kpis' => $this->emptyKpis($scopeLabel),
                'action_queue' => [],
                'awareness' => [
                    [
                        'tone' => 'slate',
                        'icon' => 'fa-users',
                        'message' => 'No staff records are assigned to this scope yet.',
                    ],
                ],
            ];
        }

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $team->pluck('employee_num')->filter()->all())
            ->get()
            ->keyBy('employee_num');

        $needsAttention = 0;
        $overdueTraining = 0;
        $credentialRisk = 0;
        $documentGaps = 0;
        $actionQueue = [];

        foreach ($team as $employee) {
            $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $empItems = is_array($items) ? $items : [];

            $training = $this->memberDashboard->summarizeEmployeeTraining($employee, $empItems);
            $compliance = $this->memberDashboard->evaluateComplianceForEmployee($employee, $empItems);
            $missingDocs = count($compliance['missing'] ?? []);

            $certRisk = 0;
            foreach ($this->memberDashboard->evaluateCertificationItemsForEmployee($employee, $empItems) as $cert) {
                $status = $cert['status'] ?? '';
                if (in_array($status, ['expired', 'expiring_soon', 'expiring_urgent', 'expires_today'], true)) {
                    $certRisk++;
                } elseif (!in_array($status, ['valid'], true)) {
                    $certRisk++;
                }
            }

            $hasIssues = $training['issue_count'] > 0 || $missingDocs > 0 || $certRisk > 0;

            if ($hasIssues) {
                $needsAttention++;
            }

            $overdueTraining += (int) ($training['overdue_count'] ?? 0);
            $credentialRisk += $certRisk;
            $documentGaps += $missingDocs;

            if (!$hasIssues) {
                continue;
            }

            $reasons = array_merge(
                $training['top_issues'] ?? [],
                $missingDocs > 0 ? [$missingDocs . ' document gap(s)'] : [],
                $certRisk > 0 ? [$certRisk . ' credential item(s) at risk'] : []
            );

            $priority = ($training['overdue_count'] ?? 0) > 0 ? 'high' : 'medium';
            $primaryIssue = $reasons[0] ?? null;

            $actionQueue[] = [
                'employee_num' => $employee->employee_num,
                'name' => $training['name'],
                'position' => $training['position'],
                'department' => $training['department'],
                'status' => $training['status'],
                'priority' => $priority,
                'summary' => implode(' · ', array_slice($reasons, 0, 2)),
                'manage_url' => $this->memberDashboard->resolveEmployeeTeamReviewUrl(
                    $employee,
                    $training,
                    $missingDocs,
                    $certRisk,
                    $primaryIssue
                ),
            ];
        }

        usort($actionQueue, function (array $a, array $b) {
            $order = ['high' => 0, 'medium' => 1, 'low' => 2];

            return ($order[$a['priority']] ?? 9) <=> ($order[$b['priority']] ?? 9);
        });

        $actionQueue = array_slice($actionQueue, 0, 10);

        $trainingRoute = \Illuminate\Support\Facades\Route::has('admin.training-management.index')
            ? route('admin.training-management.index')
            : route('member.trainings');

        return [
            'kpis' => [
                [
                    'label' => 'Team size',
                    'value' => $team->count(),
                    'hint' => 'Active in ' . $scopeLabel,
                    'tone' => 'teal',
                    'icon' => 'fa-users',
                    'route' => route('user.hr-portal'),
                ],
                [
                    'label' => 'Need attention',
                    'value' => $needsAttention,
                    'hint' => 'Staff with open issues',
                    'tone' => 'brand',
                    'icon' => 'fa-user-clock',
                    'route' => $trainingRoute,
                ],
                [
                    'label' => 'Overdue training',
                    'value' => $overdueTraining,
                    'hint' => 'Across ' . $scopeLabel,
                    'tone' => 'amber',
                    'icon' => 'fa-graduation-cap',
                    'route' => $trainingRoute,
                ],
                [
                    'label' => 'Credential risks',
                    'value' => $credentialRisk,
                    'hint' => 'Licenses & certifications',
                    'tone' => 'rose',
                    'icon' => 'fa-id-card',
                    'route' => route('member.certifications'),
                ],
            ],
            'action_queue' => $actionQueue,
            'awareness' => array_values(array_filter([
                $needsAttention > 0 ? [
                    'tone' => 'amber',
                    'icon' => 'fa-triangle-exclamation',
                    'message' => "{$needsAttention} team " . \Illuminate\Support\Str::plural('member', $needsAttention) . ' have compliance or training items needing review.',
                ] : null,
                $documentGaps > 0 ? [
                    'tone' => 'brand',
                    'icon' => 'fa-folder-open',
                    'message' => "{$documentGaps} document " . \Illuminate\Support\Str::plural('gap', $documentGaps) . ' across the team file checklist.',
                ] : null,
                $needsAttention === 0 ? [
                    'tone' => 'emerald',
                    'icon' => 'fa-circle-check',
                    'message' => 'No staff in this scope currently flagged for follow-up.',
                ] : null,
            ])),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function emptyKpis(string $scopeLabel = 'this scope'): array
    {
        return [
            ['label' => 'Team size', 'value' => 0, 'hint' => 'Active in ' . $scopeLabel, 'tone' => 'teal', 'icon' => 'fa-users', 'route' => route('user.hr-portal')],
            ['label' => 'Need attention', 'value' => 0, 'hint' => 'Staff with open issues', 'tone' => 'brand', 'icon' => 'fa-user-clock', 'route' => route('user.hr-portal')],
            ['label' => 'Overdue training', 'value' => 0, 'hint' => 'Across ' . $scopeLabel, 'tone' => 'amber', 'icon' => 'fa-graduation-cap', 'route' => route('member.trainings')],
            ['label' => 'Credential risks', 'value' => 0, 'hint' => 'Licenses & certifications', 'tone' => 'rose', 'icon' => 'fa-id-card', 'route' => route('member.certifications')],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function leadershipQuickActions(string $persona, string $trainingRoute): array
    {
        $actions = [
            [
                'title' => 'HR Management',
                'subtitle' => 'Roster, hiring, employee records',
                'route' => route('user.hr-portal'),
                'icon' => 'fa-building-user',
            ],
            [
                'title' => 'Training Management',
                'subtitle' => 'Team training & competency queue',
                'route' => $trainingRoute,
                'icon' => 'fa-list-check',
            ],
            [
                'title' => 'Team credentials',
                'subtitle' => 'Licenses and expirations',
                'route' => route('member.certifications'),
                'icon' => 'fa-shield-heart',
            ],
            [
                'title' => 'Team documents',
                'subtitle' => 'File checklist gaps',
                'route' => route('member.documents'),
                'icon' => 'fa-folder-tree',
            ],
        ];

        return $actions;
    }

    /**
     * @return list<array<string, string>>
     */
    protected function staffQuickActions(string $persona): array
    {
        return [
            ['title' => 'My documents', 'subtitle' => 'Uploads & checklist', 'route' => route('member.documents'), 'icon' => 'fa-file-lines'],
            ['title' => 'My trainings', 'subtitle' => 'Required completion', 'route' => route('member.trainings'), 'icon' => 'fa-graduation-cap'],
            ['title' => 'My profile', 'subtitle' => 'Contact & account', 'route' => route('settings.profile'), 'icon' => 'fa-user'],
        ];
    }

    /**
     * @param  Collection<int, BPEmployee>  $team
     * @return array{items: list<array<string, mixed>>}
     */
    protected function buildExpiringDocumentsPanel(Collection $team): array
    {
        if ($team->isEmpty()) {
            return ['items' => [], 'total' => 0];
        }

        $today = Carbon::today();
        $rows = [];
        $complianceService = app(DocumentComplianceService::class);
        $employeeNums = $team->pluck('employee_num')->filter()->values()->all();

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $employeeNums)
            ->get()
            ->keyBy('employee_num');

        $credentialsByEmployee = BPEmpCredential::query()
            ->whereIn('employee_num', $employeeNums)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(60))
            ->orderBy('expiry_date')
            ->get()
            ->groupBy('employee_num');

        foreach ($team as $employee) {
            $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: $employee->employee_num;
            $position = $employee->currentAssignment?->position?->title ?? '—';
            $manageUrl = $this->memberDashboard->buildAdminEmployeeEditUrl($employee->id, 'documents');

            $compliance = $complianceService->forEmployee($employee);
            foreach ($compliance['items'] as $item) {
                if (empty($item['requires_expiry'])) {
                    continue;
                }

                $daysUntil = isset($item['days_to_expiry']) ? (int) $item['days_to_expiry'] : null;
                if ($daysUntil === null && ($item['status'] ?? '') === 'expired') {
                    $expiry = !empty($item['latest_expires_at'])
                        ? Carbon::parse($item['latest_expires_at'])->startOfDay()
                        : null;
                    $daysUntil = $expiry ? (int) $today->diffInDays($expiry, false) : -1;
                }

                if ($daysUntil === null || $daysUntil > 60) {
                    continue;
                }

                $expiryDate = !empty($item['latest_expires_at'])
                    ? Carbon::parse($item['latest_expires_at'])->startOfDay()
                    : null;

                $rows[] = array_merge([
                    'employee_num' => $employee->employee_num,
                    'employee_name' => $employeeName,
                    'position' => $position,
                    'document' => (string) ($item['name'] ?? 'Document'),
                    'source' => 'Required upload',
                    'expires_on' => $expiryDate?->format('M j, Y') ?? '—',
                    'expires_on_sort' => $expiryDate?->toDateString() ?? '9999-12-31',
                    'manage_url' => $manageUrl,
                ], $this->documentExpiryPresentation($daysUntil));
            }

            $empItems = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $empItems = is_array($empItems) ? $empItems : [];
            $positionId = $employee->currentAssignment?->position_id
                ?? $employee->currentAssignment?->position?->id;

            $expiringChecklistItems = ChecklistItem::query()
                ->applicableToPosition($positionId)
                ->where('isExpiring', true)
                ->get();

            foreach ($expiringChecklistItems as $checklistItem) {
                $key = 'item_' . $checklistItem->id;
                $stored = $empItems[$key] ?? $empItems[$checklistItem->name] ?? null;
                if (!is_array($stored) || empty($stored['exp_dt']) || !empty($stored['exp_dt_not_required'])) {
                    continue;
                }

                try {
                    $expiryDate = Carbon::parse($stored['exp_dt'])->startOfDay();
                } catch (\Throwable) {
                    continue;
                }

                $daysUntil = (int) $today->diffInDays($expiryDate, false);
                if ($daysUntil > 60) {
                    continue;
                }

                $rows[] = array_merge([
                    'employee_num' => $employee->employee_num,
                    'employee_name' => $employeeName,
                    'position' => $position,
                    'document' => (string) $checklistItem->name,
                    'source' => 'Employee file checklist',
                    'expires_on' => $expiryDate->format('M j, Y'),
                    'expires_on_sort' => $expiryDate->toDateString(),
                    'manage_url' => $this->memberDashboard->buildAdminEmployeeEditUrl($employee->id, 'checklist'),
                ], $this->documentExpiryPresentation($daysUntil));
            }

            foreach ($this->memberDashboard->evaluateCertificationItemsForEmployee($employee, $empItems) as $cert) {
                $status = (string) ($cert['status'] ?? '');
                if (!in_array($status, ['expired', 'expires_today', 'expiring_urgent', 'expiring_soon'], true)) {
                    continue;
                }

                $daysUntil = isset($cert['days_until']) ? (int) $cert['days_until'] : null;
                if ($daysUntil !== null && $daysUntil > 60) {
                    continue;
                }

                if ($daysUntil === null && $status !== 'expired') {
                    continue;
                }

                $expiryLabel = (string) ($cert['exp_dt_formatted'] ?? $cert['status_label'] ?? '—');
                $expirySort = (string) ($cert['exp_dt'] ?? '9999-12-31');

                $rows[] = array_merge([
                    'employee_num' => $employee->employee_num,
                    'employee_name' => $employeeName,
                    'position' => $position,
                    'document' => (string) ($cert['title'] ?? 'License / certification'),
                    'source' => 'License or certification (upload)',
                    'expires_on' => $expiryLabel,
                    'expires_on_sort' => $expirySort,
                    'manage_url' => $this->memberDashboard->buildAdminEmployeeEditUrl($employee->id, 'documents'),
                ], $this->documentExpiryPresentation($daysUntil ?? -1));
            }

            foreach ($credentialsByEmployee->get($employee->employee_num, collect()) as $credential) {
                try {
                    $expiryDate = Carbon::parse($credential->expiry_date)->startOfDay();
                } catch (\Throwable) {
                    continue;
                }

                $daysUntil = (int) $today->diffInDays($expiryDate, false);

                $credentialLabel = trim((string) ($credential->credential_type ?? ''));
                if ($credentialLabel === '') {
                    $credentialLabel = 'Credential';
                }
                if (filled($credential->credential_number)) {
                    $credentialLabel .= ' #' . $credential->credential_number;
                }

                $rows[] = array_merge([
                    'employee_num' => $employee->employee_num,
                    'employee_name' => $employeeName,
                    'position' => $position,
                    'document' => $credentialLabel,
                    'source' => 'Employee credential',
                    'expires_on' => $expiryDate->format('M j, Y'),
                    'expires_on_sort' => $expiryDate->toDateString(),
                    'manage_url' => $this->memberDashboard->buildAdminEmployeeEditUrl($employee->id, 'documents'),
                ], $this->documentExpiryPresentation($daysUntil));
            }
        }

        usort($rows, function (array $a, array $b) {
            return ($a['days_until'] ?? 999) <=> ($b['days_until'] ?? 999);
        });

        return ['items' => array_slice($rows, 0, 50), 'total' => count($rows)];
    }

    /**
     * @param  Collection<int, BPEmployee>  $team
     * @return array{items: list<array<string, mixed>>}
     */
    protected function buildAssessmentsDuePanel(Collection $team): array
    {
        $employeeNums = $team->pluck('employee_num')->filter()->values()->all();
        if ($employeeNums === []) {
            return ['items' => []];
        }

        $today = Carbon::today();
        $periodsDueSoon = EmployeeAssessmentPeriod::query()
            ->with(['employee.currentAssignment.position'])
            ->whereIn('employee_num', $employeeNums)
            ->whereDate('date_to', '>=', $today)
            ->whereDate('date_to', '<=', $today->copy()->addDays(30))
            ->orderBy('date_to')
            ->get();

        if ($periodsDueSoon->isEmpty()) {
            return ['items' => []];
        }

        $periodIds = $periodsDueSoon->pluck('id')->unique()->values();

        $completedCompetencyKeys = EmployeeCompetencyAssessment::query()
            ->whereIn('assessment_period_id', $periodIds)
            ->whereIn('employee_num', $employeeNums)
            ->get(['employee_num', 'assessment_period_id', 'status'])
            ->filter(fn ($assessment) => AssessmentWorkflowStatus::isCompleted($assessment->status))
            ->map(fn ($assessment) => $assessment->employee_num . '|' . $assessment->assessment_period_id)
            ->flip();

        $completedPerformanceKeys = EmployeePerformanceAssessment::query()
            ->whereIn('assessment_period_id', $periodIds)
            ->whereIn('employee_num', $employeeNums)
            ->get(['employee_num', 'assessment_period_id', 'status', 'finalized'])
            ->filter(fn ($assessment) => !empty($assessment->finalized) || AssessmentWorkflowStatus::isCompleted($assessment->status))
            ->map(fn ($assessment) => $assessment->employee_num . '|' . $assessment->assessment_period_id)
            ->flip();

        $rows = [];

        foreach ($periodsDueSoon as $period) {
            $key = $period->employee_num . '|' . $period->id;
            $employee = $period->employee;
            $employeeName = $employee
                ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                : $period->employee_num;
            $position = $employee?->currentAssignment?->position?->title ?? '—';
            $dueDate = $period->date_to ? Carbon::parse($period->date_to)->startOfDay() : null;
            $daysUntil = $dueDate ? (int) $today->diffInDays($dueDate, false) : null;
            $dueLabel = $dueDate?->format('M j, Y') ?? '—';

            if (!$completedPerformanceKeys->has($key)) {
                $rows[] = [
                    'employee_num' => $period->employee_num,
                    'employee_name' => $employeeName ?: $period->employee_num,
                    'position' => $position,
                    'type' => 'Performance appraisal',
                    'type_key' => 'performance',
                    'due_on' => $dueLabel,
                    'due_on_sort' => $dueDate?->toDateString() ?? '9999-12-31',
                    'days_until' => $daysUntil,
                    'status_label' => $this->assessmentDueLabel($daysUntil),
                    'row_class' => $this->assessmentDueRowClass($daysUntil),
                    'badge_class' => $this->assessmentDueBadgeClass($daysUntil),
                    'manage_url' => $this->memberDashboard->buildAdminEmployeeEditUrl(
                        $employee?->id ?? $period->employee_num,
                        'checklist',
                        'partF'
                    ),
                ];
            }

            if (!$completedCompetencyKeys->has($key)) {
                $rows[] = [
                    'employee_num' => $period->employee_num,
                    'employee_name' => $employeeName ?: $period->employee_num,
                    'position' => $position,
                    'type' => 'Competency assessment',
                    'type_key' => 'competency',
                    'due_on' => $dueLabel,
                    'due_on_sort' => $dueDate?->toDateString() ?? '9999-12-31',
                    'days_until' => $daysUntil,
                    'status_label' => $this->assessmentDueLabel($daysUntil),
                    'row_class' => $this->assessmentDueRowClass($daysUntil),
                    'badge_class' => $this->assessmentDueBadgeClass($daysUntil),
                    'manage_url' => $this->memberDashboard->buildAdminEmployeeEditUrl(
                        $employee?->id ?? $period->employee_num,
                        'checklist',
                        'partG'
                    ),
                ];
            }
        }

        usort($rows, function (array $a, array $b) {
            $days = ($a['days_until'] ?? 999) <=> ($b['days_until'] ?? 999);
            if ($days !== 0) {
                return $days;
            }

            return strcmp((string) ($a['employee_name'] ?? ''), (string) ($b['employee_name'] ?? ''));
        });

        return ['items' => array_slice($rows, 0, 30)];
    }

    /**
     * @return array{days_until: int, status_label: string, row_class: string, badge_class: string, urgency: string}
     */
    protected function documentExpiryPresentation(int $daysUntil): array
    {
        if ($daysUntil < 0) {
            return [
                'days_until' => $daysUntil,
                'urgency' => 'expired',
                'status_label' => 'Expired ' . abs($daysUntil) . ' day(s) ago',
                'row_class' => 'bg-rose-100 border-l-4 border-rose-600',
                'badge_class' => 'bg-rose-700 text-white',
            ];
        }

        if ($daysUntil === 0) {
            return [
                'days_until' => 0,
                'urgency' => 'today',
                'status_label' => 'Expires today',
                'row_class' => 'bg-rose-50 border-l-4 border-rose-500',
                'badge_class' => 'bg-rose-600 text-white',
            ];
        }

        if ($daysUntil <= 14) {
            return [
                'days_until' => $daysUntil,
                'urgency' => 'critical',
                'status_label' => "Expires in {$daysUntil} day(s)",
                'row_class' => 'bg-rose-50 border-l-4 border-rose-400',
                'badge_class' => 'bg-rose-100 text-rose-900',
            ];
        }

        if ($daysUntil <= 30) {
            return [
                'days_until' => $daysUntil,
                'urgency' => 'urgent',
                'status_label' => "Expires in {$daysUntil} day(s)",
                'row_class' => 'bg-amber-50 border-l-4 border-amber-400',
                'badge_class' => 'bg-amber-100 text-amber-900',
            ];
        }

        return [
            'days_until' => $daysUntil,
            'urgency' => 'soon',
            'status_label' => "Expires in {$daysUntil} day(s)",
            'row_class' => 'bg-yellow-50 border-l-4 border-yellow-400',
            'badge_class' => 'bg-yellow-100 text-yellow-900',
        ];
    }

    protected function assessmentDueLabel(?int $daysUntil): string
    {
        if ($daysUntil === null) {
            return 'Due soon';
        }

        if ($daysUntil === 0) {
            return 'Due today';
        }

        if ($daysUntil === 1) {
            return 'Due tomorrow';
        }

        return "Due in {$daysUntil} day(s)";
    }

    protected function assessmentDueRowClass(?int $daysUntil): string
    {
        if ($daysUntil === null) {
            return 'bg-slate-50 border-l-4 border-slate-300';
        }

        if ($daysUntil <= 7) {
            return 'bg-rose-50 border-l-4 border-rose-500';
        }

        if ($daysUntil <= 14) {
            return 'bg-amber-50 border-l-4 border-amber-400';
        }

        return 'bg-teal-50 border-l-4 border-teal-400';
    }

    protected function assessmentDueBadgeClass(?int $daysUntil): string
    {
        if ($daysUntil === null) {
            return 'bg-slate-200 text-slate-800';
        }

        if ($daysUntil <= 7) {
            return 'bg-rose-100 text-rose-900';
        }

        if ($daysUntil <= 14) {
            return 'bg-amber-100 text-amber-900';
        }

        return 'bg-teal-100 text-teal-900';
    }
}
