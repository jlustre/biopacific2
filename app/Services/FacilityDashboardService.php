<?php

namespace App\Services;

use App\Models\BPEmpCredential;
use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\Facility;
use App\Models\JobOpening;
use App\Models\Upload;
use App\Models\User;
use App\Support\AssessmentWorkflowStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FacilityDashboardService
{
    public function __construct(
        protected RoleMemberDashboardService $roleDashboard,
        protected FacilityLeadershipService $facilityLeadership
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, Facility $facility, string $profile = 'operations'): array
    {
        $this->authorizeFacilityAccess($user, $facility);

        $scope = $this->resolveScope($user, $facility);
        $facilityKey = $facility->getRouteKey();
        $sections = $this->resolveSections($profile);

        $operations = ($sections['staff_action_queue'] ?? true)
            || ($sections['expiring_documents'] ?? true)
            || ($sections['assessments_due'] ?? true)
            ? $this->roleDashboard->buildFacilityOperationsPanels(
                $facility,
                $scope['department_id'],
                $scope['exclude_employee_num']
            )
            : [];

        return [
            'facility' => $facility,
            'facilityKey' => $facilityKey,
            'scope' => $scope,
            'sections' => $sections,
            'profile' => ($sections['facility_profile'] ?? true) ? $this->buildProfile($facility) : [],
            'webContentMetrics' => ($sections['web_content_metrics'] ?? true)
                ? $this->buildWebContentMetrics($facility, $facilityKey, $scope)
                : [],
            'hrOperationsMetrics' => ($sections['hr_operations_metrics'] ?? true)
                ? $this->buildHrOperationsMetrics($facility, $facilityKey, $scope, $operations)
                : [],
            'hrManagementCards' => ($sections['hr_management_cards'] ?? true)
                ? $this->buildHrManagementCards($facility, $facilityKey, $scope)
                : [],
            'hrManagementIntro' => $scope['is_don']
                ? 'Department-focused actions for staffing, evaluations, and credential compliance.'
                : 'Manage staffing, hiring, credentials, and facility data.',
            'hrQuickActions' => ($sections['hr_quick_actions'] ?? true)
                ? $this->buildHrQuickActions($facility, $facilityKey, $scope)
                : [],
            'kpis' => $operations['kpis'] ?? [],
            'actionQueue' => array_slice(
                $operations['action_queue'] ?? [],
                0,
                (int) config('facility-dashboard.action_queue_limit', 10)
            ),
            'awareness' => $operations['awareness'] ?? [],
            'facilityLeadership' => ($sections['facility_leadership'] ?? true)
                ? $this->facilityLeadership->rosterForFacility($facility)
                : [],
            'expiringDocuments' => $operations['expiring_documents'] ?? [],
            'expiringDocumentsTotal' => $operations['expiring_documents_total'] ?? 0,
            'assessmentsDue' => $operations['assessments_due'] ?? [],
            'facilityDocumentsUrl' => $operations['facility_documents_url'] ?? null,
            'staffDirectoryByDepartment' => ($sections['staff_directory'] ?? true)
                ? $this->buildStaffDirectoryByDepartment($facility, $scope)
                : [],
            'staffDirectoryCount' => ($sections['staff_directory'] ?? true)
                ? $this->countStaffDirectory($facility, $scope)
                : 0,
            'employeesListUrl' => route('admin.facility.employees', ['facility' => $facilityKey])
                . '?facility=' . $facility->id,
            'dashboardProfile' => $profile,
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function resolveSections(string $profile): array
    {
        $profiles = config('facility-dashboard.profiles', []);
        if (isset($profiles[$profile])) {
            return $profiles[$profile];
        }

        $legacy = config('facility-dashboard.sections', []);
        if ($legacy !== []) {
            return $legacy;
        }

        return $profiles[config('facility-dashboard.default_profile', 'operations')] ?? [];
    }

    protected function authorizeFacilityAccess(User $user, Facility $facility): void
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return;
        }

        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor', 'don'])) {
            if ((int) $user->facility_id !== (int) $facility->id) {
                abort(403, 'Unauthorized: You do not have access to this facility.');
            }

            return;
        }

        abort(403, 'Unauthorized access to this facility.');
    }

    /**
     * @return array{
     *     type: string,
     *     label: string,
     *     intro: string,
     *     department_id: ?int,
     *     department_name: ?string,
     *     is_don: bool,
     *     exclude_employee_num: ?string
     * }
     */
    protected function resolveScope(User $user, Facility $facility): array
    {
        $isDonDashboard = $user->hasRole('don')
            && ! $user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);

        $donDepartmentId = null;
        $donDepartmentName = null;
        $excludeEmployeeNum = null;

        if ($isDonDashboard) {
            $donEmployee = $user->resolvedBpEmployee(['currentAssignment.department']);
            $donDepartmentId = $donEmployee?->currentAssignment?->dept_id;
            $donDepartmentName = $donEmployee?->currentAssignment?->department?->name;
            $excludeEmployeeNum = $donEmployee?->employee_num;
        }

        if ($isDonDashboard && $donDepartmentId && filled($donDepartmentName)) {
            return [
                'type' => 'department',
                'label' => $donDepartmentName,
                'intro' => "Department view for {$donDepartmentName} at {$facility->name}.",
                'department_id' => (int) $donDepartmentId,
                'department_name' => $donDepartmentName,
                'is_don' => true,
                'exclude_employee_num' => $excludeEmployeeNum,
            ];
        }

        return [
            'type' => 'facility',
            'label' => $facility->name,
            'intro' => "Facility-wide overview for {$facility->name} — staff, compliance, and public site content.",
            'department_id' => null,
            'department_name' => null,
            'is_don' => false,
            'exclude_employee_num' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildProfile(Facility $facility): array
    {
        $statusLabel = $facility->status ?? 'Active';

        return [
            'name' => $facility->name,
            'address' => $facility->formattedAddress() ?: null,
            'phone' => $facility->phone,
            'email' => $facility->email,
            'status' => $statusLabel,
            'slug' => $facility->slug,
            'public_url' => $facility->slug
                ? url('/' . $facility->slug)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return list<array<string, mixed>>
     */
    protected function buildWebContentMetrics(Facility $facility, string $facilityKey, array $scope): array
    {
        if ($scope['is_don']) {
            return [];
        }

        return [
            [
                'key' => 'job_openings',
                'label' => 'Job openings',
                'value' => JobOpening::query()->where('facility_id', $facility->id)->count(),
                'hint' => 'Active listings',
                'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]),
                'icon' => 'fa-briefcase',
                'tone' => 'teal',
            ],
            [
                'key' => 'news',
                'label' => 'News & updates',
                'value' => $facility->news()->count(),
                'hint' => 'Published or linked',
                'route' => route('admin.news.index'),
                'icon' => 'fa-newspaper',
                'tone' => 'sky',
            ],
            [
                'key' => 'testimonials',
                'label' => 'Testimonials',
                'value' => $facility->testimonials()->count(),
                'hint' => 'On your public site',
                'route' => route('admin.facilities.webcontents.testimonials'),
                'icon' => 'fa-quote-right',
                'tone' => 'amber',
            ],
            [
                'key' => 'gallery',
                'label' => 'Gallery images',
                'value' => $facility->galleryImages()->count(),
                'hint' => 'Media assets',
                'route' => route('admin.galleries.index'),
                'icon' => 'fa-images',
                'tone' => 'violet',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $operations
     * @return list<array<string, mixed>>
     */
    protected function buildHrOperationsMetrics(
        Facility $facility,
        string $facilityKey,
        array $scope,
        array $operations
    ): array {
        $employeeNums = $this->scopedEmployeeNums($facility, $scope);
        $today = Carbon::today();

        $metrics = [
            [
                'key' => 'staff_count',
                'label' => 'Active staff',
                'value' => $operations['team_count'] ?? $employeeNums->count(),
                'hint' => $scope['type'] === 'department' ? 'In your department' : 'At this facility',
                'route' => route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id,
                'icon' => 'fa-users',
                'tone' => 'emerald',
            ],
            [
                'key' => 'expiring_credentials',
                'label' => 'Expiring licenses',
                'value' => BPEmpCredential::query()
                    ->whereIn('employee_num', $employeeNums)
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', $today->copy()->addDays(30))
                    ->count(),
                'hint' => 'Due within 30 days',
                'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                'icon' => 'fa-id-badge',
                'tone' => 'rose',
            ],
            [
                'key' => 'pending_reviews',
                'label' => 'Pending reviews',
                'value' => Upload::query()
                    ->where('facility_id', $facility->id)
                    ->whereIn('employee_num', $employeeNums)
                    ->where('verification_status', Upload::VERIFICATION_PENDING)
                    ->count(),
                'hint' => 'Documents awaiting verification',
                'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                'icon' => 'fa-tasks',
                'tone' => 'cyan',
            ],
        ];

        if ($scope['is_don']) {
            $metrics[] = [
                'key' => 'competencies_due',
                'label' => 'Competencies due',
                'value' => $this->countAssessmentsDue($employeeNums, 'competency'),
                'hint' => 'Next 30 days',
                'route' => route('admin.facility.employees', ['facility' => $facilityKey, 'checklist' => 'partG'])
                    . '&facility=' . $facility->id,
                'icon' => 'fa-star',
                'tone' => 'amber',
            ];
            $metrics[] = [
                'key' => 'performance_due',
                'label' => 'Appraisals due',
                'value' => $this->countAssessmentsDue($employeeNums, 'performance'),
                'hint' => 'Next 30 days',
                'route' => route('admin.facility.employees', ['facility' => $facilityKey, 'checklist' => 'partF'])
                    . '&facility=' . $facility->id,
                'icon' => 'fa-chart-line',
                'tone' => 'indigo',
            ];
        } else {
            $metrics[] = [
                'key' => 'need_attention',
                'label' => 'Need attention',
                'value' => collect($operations['action_queue'] ?? [])->count(),
                'hint' => 'Staff with open items',
                'route' => route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id,
                'icon' => 'fa-user-clock',
                'tone' => 'brand',
            ];
        }

        return $metrics;
    }

    /**
     * @param  Collection<int, string>  $employeeNums
     */
    protected function countAssessmentsDue(Collection $employeeNums, string $type): int
    {
        if ($employeeNums->isEmpty()) {
            return 0;
        }

        $today = Carbon::today();
        $periodsDueSoon = EmployeeAssessmentPeriod::query()
            ->whereIn('employee_num', $employeeNums)
            ->whereDate('date_to', '>=', $today)
            ->whereDate('date_to', '<=', $today->copy()->addDays(30))
            ->get(['id', 'employee_num']);

        if ($periodsDueSoon->isEmpty()) {
            return 0;
        }

        $periodIds = $periodsDueSoon->pluck('id')->unique()->values();

        if ($type === 'competency') {
            $completedKeys = EmployeeCompetencyAssessment::query()
                ->whereIn('assessment_period_id', $periodIds)
                ->whereIn('employee_num', $employeeNums)
                ->get(['employee_num', 'assessment_period_id', 'status'])
                ->filter(fn ($row) => AssessmentWorkflowStatus::isCompleted($row->status))
                ->map(fn ($row) => $row->employee_num . '|' . $row->assessment_period_id)
                ->flip();
        } else {
            $completedKeys = EmployeePerformanceAssessment::query()
                ->whereIn('assessment_period_id', $periodIds)
                ->whereIn('employee_num', $employeeNums)
                ->get(['employee_num', 'assessment_period_id', 'status', 'finalized'])
                ->filter(fn ($row) => !empty($row->finalized) || AssessmentWorkflowStatus::isCompleted($row->status))
                ->map(fn ($row) => $row->employee_num . '|' . $row->assessment_period_id)
                ->flip();
        }

        return $periodsDueSoon
            ->map(fn ($period) => $period->employee_num . '|' . $period->id)
            ->reject(fn ($key) => $completedKeys->has($key))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return Collection<int, string>
     */
    protected function scopedEmployeeNums(Facility $facility, array $scope): Collection
    {
        return BPEmployee::query()
            ->whereHas('currentAssignment', function ($query) use ($facility, $scope) {
                $query->where('facility_id', $facility->id);
                if ($scope['department_id']) {
                    $query->where('dept_id', $scope['department_id']);
                }
            })
            ->pluck('employee_num');
    }

    /**
     * HR Management gradient cards (Job openings, Hiring, Employees, Documents, Reports, Import).
     *
     * @param  array<string, mixed>  $scope
     * @return list<array<string, mixed>>
     */
    protected function buildHrManagementCards(Facility $facility, string $facilityKey, array $scope): array
    {
        $employeesBase = route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id;

        if ($scope['is_don']) {
            return [
                [
                    'type' => 'link',
                    'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]),
                    'icon' => 'fa-briefcase',
                    'label' => 'Openings',
                    'desc' => 'Track openings relevant to your department',
                    'tone' => 'teal',
                ],
                [
                    'type' => 'link',
                    'route' => route('admin.facility.hiring', ['facility' => $facilityKey]),
                    'icon' => 'fa-user-plus',
                    'label' => 'Hiring',
                    'desc' => 'Review applicants and onboarding for your team',
                    'tone' => 'sky',
                ],
                [
                    'type' => 'link',
                    'route' => $employeesBase,
                    'icon' => 'fa-users',
                    'label' => 'Employees',
                    'desc' => 'View roster plus performance and competency status',
                    'tone' => 'emerald',
                ],
                [
                    'type' => 'link',
                    'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                    'icon' => 'fa-file-alt',
                    'label' => 'Credentials & documents',
                    'desc' => 'Review licenses, certifications, and expiring docs',
                    'tone' => 'cyan',
                ],
                [
                    'type' => 'link',
                    'route' => route('admin.facility.reports', ['facility' => $facilityKey]),
                    'icon' => 'fa-chart-bar',
                    'label' => 'Reports',
                    'desc' => 'Review due items and compliance status',
                    'tone' => 'rose',
                ],
            ];
        }

        return [
            [
                'type' => 'link',
                'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]),
                'icon' => 'fa-briefcase',
                'label' => 'Job listings',
                'desc' => 'Post and manage open roles',
                'tone' => 'teal',
            ],
            [
                'type' => 'link',
                'route' => route('admin.facility.hiring', ['facility' => $facilityKey]),
                'icon' => 'fa-user-plus',
                'label' => 'Hiring',
                'desc' => 'Review applicants and onboarding',
                'tone' => 'sky',
            ],
            [
                'type' => 'link',
                'route' => $employeesBase,
                'icon' => 'fa-users',
                'label' => 'Employees',
                'desc' => 'Roster, orientation, performance & competencies evaluation',
                'tone' => 'emerald',
            ],
            [
                'type' => 'link',
                'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                'icon' => 'fa-file-alt',
                'label' => 'Documents',
                'desc' => 'Credentials and compliance files',
                'tone' => 'cyan',
            ],
            [
                'type' => 'link',
                'route' => route('admin.facility.reports', ['facility' => $facilityKey]),
                'icon' => 'fa-chart-bar',
                'label' => 'Reports',
                'desc' => 'Export and review facility data',
                'tone' => 'rose',
            ],
            [
                'type' => 'import',
                'route' => null,
                'icon' => 'fa-file-import',
                'label' => 'Import files',
                'desc' => 'Upload spreadsheets and map columns',
                'tone' => 'amber',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return list<array<string, mixed>>
     */
    protected function buildHrQuickActions(Facility $facility, string $facilityKey, array $scope): array
    {
        $employeesBase = route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id;

        $actions = [
            ['title' => 'Staff directory', 'subtitle' => 'Full employee roster', 'route' => $employeesBase, 'icon' => 'fa-users'],
            ['title' => 'Hiring', 'subtitle' => 'Applicants & onboarding', 'route' => route('admin.facility.hiring', ['facility' => $facilityKey]), 'icon' => 'fa-user-plus'],
            ['title' => 'Documents', 'subtitle' => 'Credentials & uploads', 'route' => route('admin.facility.documents', ['facility' => $facilityKey]), 'icon' => 'fa-file-alt'],
            ['title' => 'Reports', 'subtitle' => 'Export & compliance', 'route' => route('admin.facility.reports', ['facility' => $facilityKey]), 'icon' => 'fa-chart-bar'],
        ];

        if (!$scope['is_don']) {
            $actions[] = ['title' => 'Job listings', 'subtitle' => 'Post open roles', 'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]), 'icon' => 'fa-briefcase'];
        }

        return $actions;
    }

    /**
     * @param  array<string, mixed>  $scope
     */
    protected function countStaffDirectory(Facility $facility, array $scope): int
    {
        return (int) $this->staffDirectoryQuery($facility, $scope)->count();
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return list<array{key: string, department: string, count: int, members: list<array<string, mixed>>}>
     */
    protected function buildStaffDirectoryByDepartment(Facility $facility, array $scope): array
    {
        $limit = (int) config('facility-dashboard.staff_directory_limit', 200);

        $members = $this->staffDirectoryQuery($facility, $scope)
            ->limit($limit)
            ->get()
            ->map(function (BPEmployee $employee) {
                $department = $employee->currentAssignment?->department?->name
                    ?? $employee->currentAssignment?->position?->department?->name;
                $departmentLabel = filled($department) ? (string) $department : 'Unassigned';

                $name = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: $employee->employee_num;

                return [
                    'id' => $employee->id,
                    'employee_num' => $employee->employee_num,
                    'name' => $name,
                    'position' => $employee->currentAssignment?->position?->title ?? '—',
                    'department' => $departmentLabel,
                    'email' => $employee->email ?: $employee->user?->email,
                    'phone' => $employee->phone?->phone_number,
                    'edit_url' => route('admin.employees.edit', $employee->id),
                ];
            });

        return $members
            ->groupBy('department')
            ->map(function (Collection $group, string $department) {
                $sorted = $group->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

                return [
                    'key' => Str::slug($department) ?: 'department',
                    'department' => $department,
                    'count' => $sorted->count(),
                    'members' => $sorted->all(),
                ];
            })
            ->sortBy(function (array $group) {
                $label = $group['department'];

                return in_array($label, ['Unassigned', '—'], true) ? 'zzz_'.$label : $label;
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $scope
     * @return \Illuminate\Database\Eloquent\Builder<BPEmployee>
     */
    protected function staffDirectoryQuery(Facility $facility, array $scope)
    {
        return BPEmployee::query()
            ->with([
                'currentAssignment.position.department',
                'currentAssignment.department',
                'phone',
                'user',
            ])
            ->whereHas('currentAssignment', function ($query) use ($facility, $scope) {
                $query->where('facility_id', $facility->id);
                if ($scope['department_id']) {
                    $query->where('dept_id', $scope['department_id']);
                }
            })
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    /**
     * Facilities the user may view on the dashboard (for RDHR / admin picker).
     *
     * @return Collection<int, Facility>
     */
    public function facilitiesForUser(User $user): Collection
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return Facility::query()->orderBy('name')->get(['id', 'name', 'slug']);
        }

        if ($user->facility_id) {
            $facility = Facility::find($user->facility_id);

            return $facility ? collect([$facility]) : collect();
        }

        return collect();
    }
}
