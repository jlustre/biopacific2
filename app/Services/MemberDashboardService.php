<?php

namespace App\Services;

use App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist;
use App\Models\BPEmployee;
use App\Models\BPEmpChecklist;
use App\Models\ChecklistItem;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeChecklist;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\WebmasterContact;
use App\Support\AssessmentWorkflowStatus;
use App\Support\CompetencyAssessmentWorkflowReadiness;
use App\Support\MemberPortalLayout;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MemberDashboardService
{
        public function buildDashboardModules(
            User $user,
            array $stats,
            int $newsEventsCount = 0,
            ?string $positionTitle = null
        ): array {
            $persona = $this->resolveDashboardPersona($user, $positionTitle);
            $personaLabel = $this->dashboardPersonaLabel($persona);
            $showFacilityOversight = in_array($persona, ['facility-admin', 'facility-dsd', 'don'], true);

            $overviewCards = $this->buildOverviewCards($persona, $stats, $newsEventsCount);
            $personalActions = $this->buildPersonalActions($persona);
            $facilityActions = $this->buildFacilityActions($persona);
            $helpfulLinks = $this->buildHelpfulLinks($persona);

            return [
                'dashboardPersona' => $persona,
                'dashboardPersonaLabel' => $personaLabel,
                'dashboardIntro' => 'Dashboard focus: ' . $personaLabel,
                'showFacilityOversight' => $showFacilityOversight,
                'overviewCards' => $overviewCards,
                'personalActions' => $personalActions,
                'facilityActions' => $facilityActions,
                'helpfulLinks' => $helpfulLinks,
            ];
        }

        protected function resolveDashboardPersona(User $user, ?string $positionTitle): string
        {
            if ($this->userHasRole($user, 'facility-admin')) {
                return 'facility-admin';
            }

            if ($this->userHasRole($user, 'facility-dsd')) {
                return 'facility-dsd';
            }

            if ($this->userHasRole($user, 'don')) {
                return 'don';
            }

            $normalizedTitle = strtolower(trim((string) $positionTitle));

            if ($normalizedTitle !== '') {
                if (preg_match('/director of nursing|\bdon\b/', $normalizedTitle) === 1) {
                    return 'don';
                }

                if (preg_match('/\bcna\b|certified nursing assistant|nursing assistant/', $normalizedTitle) === 1) {
                    return 'cna';
                }

                if (preg_match('/registered nurse|\brn\b|licensed vocational nurse|\blvn\b|licensed practical nurse|\blpn\b|\bnurse\b/', $normalizedTitle) === 1) {
                    return 'licensed-nurse';
                }
            }

            return 'employee-default';
        }

        protected function dashboardPersonaLabel(string $persona): string
        {
            return match ($persona) {
                'facility-admin' => 'Facility Admin',
                'facility-dsd' => 'Facility DSD',
                'don' => 'Director of Nursing',
                'licensed-nurse' => 'Licensed Nurse',
                'cna' => 'Certified Nursing Assistant',
                default => 'Employee',
            };
        }

        protected function buildOverviewCards(string $persona, array $stats, int $newsEventsCount): array
        {
            $facilityPriorityCount = (int) ($stats['pending_actions'] ?? 0);
            $docsCount = (int) ($stats['documents_needed'] ?? 0);
            $trainingCount = (int) ($stats['trainings_needs_action'] ?? 0);
            $certCount = (int) (($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0));

            if (in_array($persona, ['facility-admin', 'facility-dsd', 'don'], true)) {
                $priorityLabel = match ($persona) {
                    'facility-admin' => 'Admin priority items',
                    'facility-dsd' => 'DSD priority items',
                    'don' => 'Nursing leadership items',
                    default => 'Facility priority items',
                };

                $priorityHint = match ($persona) {
                    'facility-admin' => 'Open facility admin workflow views',
                    'facility-dsd' => 'Open DSD staffing and onboarding queue',
                    'don' => 'Open nursing compliance and workforce queue',
                    default => 'Open facility workflow views',
                };

                return [
                    [
                        'label' => $priorityLabel,
                        'value' => $facilityPriorityCount,
                        'hint' => $priorityHint,
                        'route' => route('user.hr-portal'),
                        'tone' => 'brand',
                        'icon' => 'fa-clipboard-list',
                    ],
                    [
                        'label' => 'Trainings needing action',
                        'value' => $trainingCount,
                        'hint' => 'Review overdue and pending signatures',
                        'route' => \Illuminate\Support\Facades\Route::has('admin.training-management.index')
                            ? route('admin.training-management.index')
                            : route('member.checklists'),
                        'tone' => 'amber',
                        'icon' => 'fa-graduation-cap',
                    ],
                    [
                        'label' => 'Credentials at risk',
                        'value' => $certCount,
                        'hint' => 'Expiring or expired certifications',
                        'route' => route('member.certifications'),
                        'tone' => 'rose',
                        'icon' => 'fa-award',
                    ],
                    [
                        'label' => 'Facility updates',
                        'value' => $newsEventsCount,
                        'hint' => 'Announcements and policy news',
                        'route' => route('member.news-events.index'),
                        'tone' => 'teal',
                        'icon' => 'fa-newspaper',
                    ],
                ];
            }

            return [
                [
                    'label' => 'Documents needing action',
                    'value' => $docsCount,
                    'hint' => 'Review document center items',
                    'route' => route('member.documents'),
                    'tone' => 'brand',
                    'icon' => 'fa-folder-open',
                ],
                [
                    'label' => 'Trainings needing action',
                    'value' => $trainingCount,
                    'hint' => (($stats['trainings_pending_signature'] ?? 0) > 0)
                        ? (($stats['trainings_pending_signature'] ?? 0) . ' pending signatures')
                        : 'Review required trainings',
                    'route' => route('member.checklists'),
                    'tone' => 'amber',
                    'icon' => 'fa-graduation-cap',
                ],
                [
                    'label' => 'Certifications expiring/expired',
                    'value' => $certCount,
                    'hint' => 'Prioritize renewals',
                    'route' => route('member.certifications'),
                    'tone' => 'rose',
                    'icon' => 'fa-award',
                ],
                [
                    'label' => 'Facility news & events',
                    'value' => $newsEventsCount,
                    'hint' => 'Announcements and updates',
                    'route' => route('member.news-events.index'),
                    'tone' => 'teal',
                    'icon' => 'fa-newspaper',
                ],
            ];
        }

        protected function buildPersonalActions(string $persona): array
        {
            if ($persona === 'facility-admin') {
                return [
                    [
                        'title' => 'Facility HR Management',
                        'subtitle' => 'Manage staffing, onboarding, and facility tasks',
                        'route' => route('user.hr-portal'),
                        'icon' => 'fa-building-user',
                    ],
                    [
                        'title' => 'Facility Documents',
                        'subtitle' => 'Review missing and expiring records',
                        'route' => route('member.documents'),
                        'icon' => 'fa-folder-tree',
                    ],
                    [
                        'title' => 'Facility Trainings',
                        'subtitle' => 'Track completion across your facility',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-list-check',
                    ],
                    [
                        'title' => 'Facility Certifications',
                        'subtitle' => 'Monitor credential risks and expirations',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-shield-heart',
                    ],
                ];
            }

            if ($persona === 'facility-dsd') {
                return [
                    [
                        'title' => 'DSD Work Queue',
                        'subtitle' => 'Prioritize staff readiness and follow-ups',
                        'route' => route('user.hr-portal'),
                        'icon' => 'fa-clipboard-list',
                    ],
                    [
                        'title' => 'Training Sign-offs',
                        'subtitle' => 'Close pending signatures and overdue items',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-user-check',
                    ],
                    [
                        'title' => 'Credential Follow-up',
                        'subtitle' => 'Address expiring and expired licenses',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-certificate',
                    ],
                    [
                        'title' => 'Facility File Review',
                        'subtitle' => 'Keep records complete and audit ready',
                        'route' => route('member.documents'),
                        'icon' => 'fa-folder-open',
                    ],
                ];
            }

            if ($persona === 'don') {
                return [
                    [
                        'title' => 'Nursing Compliance Queue',
                        'subtitle' => 'Oversee nursing team readiness',
                        'route' => route('user.hr-portal'),
                        'icon' => 'fa-hospital-user',
                    ],
                    [
                        'title' => 'Clinical Trainings',
                        'subtitle' => 'Review pending and overdue trainings',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-person-chalkboard',
                    ],
                    [
                        'title' => 'Nursing Credentials',
                        'subtitle' => 'Track RN/LVN/LPN expirations',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-user-shield',
                    ],
                    [
                        'title' => 'Clinical Documents',
                        'subtitle' => 'Review facility file gaps',
                        'route' => route('member.documents'),
                        'icon' => 'fa-folder-tree',
                    ],
                ];
            }

            if ($persona === 'licensed-nurse') {
                return [
                    [
                        'title' => 'My Certifications',
                        'subtitle' => 'Track license expirations and renewals',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-certificate',
                    ],
                    [
                        'title' => 'My Checklists',
                        'subtitle' => 'Complete required clinical trainings',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-person-chalkboard',
                    ],
                    [
                        'title' => 'My Documents',
                        'subtitle' => 'Upload and review required files',
                        'route' => route('member.documents'),
                        'icon' => 'fa-file-lines',
                    ],
                    [
                        'title' => 'Facility News',
                        'subtitle' => 'Read current updates and notices',
                        'route' => route('member.news-events.index'),
                        'icon' => 'fa-newspaper',
                    ],
                ];
            }

            if ($persona === 'cna') {
                return [
                    [
                        'title' => 'My Checklists',
                        'subtitle' => 'Stay current on mandatory training items',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-person-chalkboard',
                    ],
                    [
                        'title' => 'My Documents',
                        'subtitle' => 'Review onboarding and compliance files',
                        'route' => route('member.documents'),
                        'icon' => 'fa-file-lines',
                    ],
                    [
                        'title' => 'My Certifications',
                        'subtitle' => 'Monitor expiring credentials',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-certificate',
                    ],
                    [
                        'title' => 'My Profile',
                        'subtitle' => 'Update contact and account details',
                        'route' => route('settings.profile'),
                        'icon' => 'fa-user-gear',
                    ],
                ];
            }

            return [
                [
                    'title' => 'Documents',
                    'subtitle' => 'Upload and review checklist files',
                    'route' => route('member.documents'),
                    'icon' => 'fa-file-lines',
                ],
                [
                    'title' => 'Trainings',
                    'subtitle' => 'Track required training progress',
                    'route' => route('member.checklists'),
                    'icon' => 'fa-person-chalkboard',
                ],
                [
                    'title' => 'Certifications',
                    'subtitle' => 'Monitor expirations and renewals',
                    'route' => route('member.certifications'),
                    'icon' => 'fa-certificate',
                ],
                [
                    'title' => 'My Profile',
                    'subtitle' => 'Update personal and account details',
                    'route' => route('settings.profile'),
                    'icon' => 'fa-user-gear',
                ],
            ];
        }

        protected function buildFacilityActions(string $persona): array
        {
            if (!in_array($persona, ['facility-admin', 'facility-dsd', 'don'], true)) {
                return [];
            }

            if ($persona === 'don') {
                return [
                    [
                        'title' => 'Clinical Workforce Portal',
                        'subtitle' => 'Review care-team checklist completion',
                        'route' => route('user.hr-portal'),
                        'icon' => 'fa-hospital-user',
                    ],
                    [
                        'title' => 'Nursing Credentials',
                        'subtitle' => 'Track RN/LVN/LPN compliance',
                        'route' => route('member.certifications'),
                        'icon' => 'fa-user-shield',
                    ],
                    [
                        'title' => 'Clinical Trainings',
                        'subtitle' => 'Focus on pending signatures and overdue items',
                        'route' => route('member.checklists'),
                        'icon' => 'fa-list-check',
                    ],
                    [
                        'title' => 'Employee Documents',
                        'subtitle' => 'Review missing and returned files',
                        'route' => route('member.documents'),
                        'icon' => 'fa-folder-tree',
                    ],
                ];
            }

            return [
                [
                    'title' => 'Facility HR Management',
                    'subtitle' => 'Oversee employee workflows in your site',
                    'route' => route('user.hr-portal'),
                    'icon' => 'fa-building-user',
                ],
                [
                    'title' => 'Facility Documents',
                    'subtitle' => 'Review missing/expiring files',
                    'route' => route('member.documents'),
                    'icon' => 'fa-folder-tree',
                ],
                [
                    'title' => 'Facility Trainings',
                    'subtitle' => 'Focus on overdue and pending sign-offs',
                    'route' => route('member.checklists'),
                    'icon' => 'fa-list-check',
                ],
                [
                    'title' => 'Facility Certifications',
                    'subtitle' => 'Track license compliance at your facility',
                    'route' => route('member.certifications'),
                    'icon' => 'fa-shield-heart',
                ],
            ];
        }

        protected function buildHelpfulLinks(string $persona): array
        {
            if (in_array($persona, ['facility-admin', 'facility-dsd', 'don'], true)) {
                return [
                    [
                        'label' => 'Open Facility HR Management',
                        'route' => route('user.hr-portal'),
                    ],
                    [
                        'label' => 'View Facility News & Events',
                        'route' => route('member.news-events.index'),
                    ],
                    [
                        'label' => 'Manage Profile',
                        'route' => route('settings.profile'),
                    ],
                ];
            }

            return [
                [
                    'label' => 'Open Document Center',
                    'route' => route('member.documents'),
                ],
                [
                    'label' => 'View Facility News & Events',
                    'route' => route('member.news-events.index'),
                ],
                [
                    'label' => 'Manage Profile',
                    'route' => route('settings.profile'),
                ],
            ];
        }

    public function userHasRole(User $user, string|array $role): bool
    {
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        return (bool) $user->hasRole($role);
    }

    public function evaluateCertificationItemsForEmployee(BPEmployee $employee, array $empChecklistItems): array
    {
        return $this->evaluateCertificationItems($employee, $empChecklistItems);
    }

    public function evaluateComplianceForEmployee(?BPEmployee $employee, array $empChecklistItems): array
    {
        return $this->evaluateEmployeeChecklistCompliance($employee, $empChecklistItems);
    }

    /**
     * Lightweight portal header alerts (notifications bell + sidebar badges).
     *
     * @return array{
     *     count: int,
     *     items: list<array{title: string, message: string, tone: string, route: ?string}>,
     *     documents_needed: int,
     *     trainings_needs_action: int,
     *     credentials_at_risk: int
     * }
     */
    public function buildPortalAlerts(User $user, int $limit = 5): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
            'currentAssignment.facility',
            'uploads',
        ]);

        $jobApplication = $user->jobApplications()->latest()->first();
        $hasPreEmployment = $this->hasActivePreEmployment($jobApplication);
        $preEmploymentChecklists = $hasPreEmployment
            ? $user->employeeChecklists()->orderBy('item_label')->get()
            : collect();

        $empChecklistItems = $bpEmployee
            ? (optional(BPEmpChecklist::query()->where('employee_num', $bpEmployee->employee_num)->first())->items ?? [])
            : [];

        $empChecklistItems = is_array($empChecklistItems) ? $empChecklistItems : [];
        $complianceEvaluation = $this->evaluateEmployeeChecklistCompliance($bpEmployee, $empChecklistItems);
        $documentsNeeded = $complianceEvaluation['missing'] ?? [];
        $signaturesNeeded = $this->buildSignaturesNeeded($bpEmployee, $empChecklistItems);
        $reminders = $this->buildReminders($bpEmployee, $empChecklistItems, $preEmploymentChecklists, $documentsNeeded);

        $trainingsSummary = $this->buildTrainingsCenter(
            $user,
            $bpEmployee,
            $empChecklistItems,
            $preEmploymentChecklists
        )['summary'];

        $credentialCount = 0;
        if ($bpEmployee) {
            foreach ($this->evaluateCertificationItems($bpEmployee, $empChecklistItems) as $cert) {
                $status = $cert['status'] ?? '';
                if (in_array($status, ['expiring_soon', 'expiring_urgent', 'expires_today', 'expired'], true)
                    || !in_array($status, ['valid'], true)) {
                    $credentialCount++;
                }
            }
        }

        $items = [];

        if (!$user->hasVerifiedEmail()) {
            $items[] = [
                'title' => 'Verify your email',
                'message' => 'Confirm your email address to secure your account.',
                'tone' => 'amber',
                'route' => route('verification.notice'),
            ];
        }

        foreach ($preEmploymentChecklists->where('status', 'returned') as $item) {
            $items[] = [
                'title' => 'Pre-employment item returned',
                'message' => ($item->item_label ?? 'Checklist item') . ' needs corrections.',
                'tone' => 'rose',
                'route' => route('pre-employment.portal'),
            ];
        }

        foreach ($signaturesNeeded as $signature) {
            $items[] = [
                'title' => 'Signature required: ' . ($signature['title'] ?? 'Document'),
                'message' => (string) ($signature['description'] ?? 'Your signature is required.'),
                'tone' => 'amber',
                'route' => route('employment.portal'),
            ];
        }

        foreach (array_slice($documentsNeeded, 0, 3) as $document) {
            $items[] = [
                'title' => $document['title'] ?? 'Document needed',
                'message' => trim(($document['section'] ?? 'Employee file') . ' — ' . ($document['status_label'] ?? 'Action required')),
                'tone' => 'brand',
                'route' => route('member.documents'),
            ];
        }

        if ($bpEmployee) {
            foreach ($this->evaluateCertificationItems($bpEmployee, $empChecklistItems) as $cert) {
                $status = $cert['status'] ?? '';
                if (!in_array($status, ['expired', 'expires_today', 'expiring_urgent', 'expiring_soon'], true)) {
                    continue;
                }

                $items[] = [
                    'title' => ($cert['title'] ?? 'Credential') . ' — ' . ($cert['status_label'] ?? 'Attention needed'),
                    'message' => $cert['exp_dt_formatted']
                        ? 'Expires ' . $cert['exp_dt_formatted']
                        : 'Review your licenses and certifications.',
                    'tone' => in_array($status, ['expired', 'expires_today', 'expiring_urgent'], true) ? 'rose' : 'amber',
                    'route' => route('member.certifications'),
                ];
            }
        }

        foreach ($reminders as $reminder) {
            $items[] = [
                'title' => $reminder['title'] ?? 'Reminder',
                'message' => $reminder['message'] ?? '',
                'tone' => match ($reminder['type'] ?? 'info') {
                    'danger' => 'rose',
                    'warning' => 'amber',
                    'info' => 'brand',
                    default => 'slate',
                },
                'route' => route('member.documents'),
            ];
        }

        if (($trainingsSummary['needs_action'] ?? 0) > 0) {
            $pendingSignatures = (int) ($trainingsSummary['pending_signature'] ?? 0);
            $items[] = [
                'title' => 'Training action needed',
                'message' => $pendingSignatures > 0
                    ? "{$pendingSignatures} training item(s) need your signature."
                    : ($trainingsSummary['needs_action'] . ' training item(s) need attention.'),
                'tone' => 'amber',
                'route' => route('member.checklists'),
            ];
        }

        if (MemberPortalLayout::userIsSystemAdmin($user)) {
            $openWebmasterIssues = WebmasterContact::query()
                ->where('status', '!=', 'resolved')
                ->count();
            $unreadWebmasterIssues = WebmasterContact::query()
                ->where('is_read', false)
                ->count();

            if ($openWebmasterIssues > 0) {
                $items[] = [
                    'title' => 'Contact Webmaster issues',
                    'message' => $openWebmasterIssues . ' open report(s) from facility websites'
                        . ($unreadWebmasterIssues > 0 ? " ({$unreadWebmasterIssues} unread)" : '') . '.',
                    'tone' => $unreadWebmasterIssues > 0 ? 'rose' : 'amber',
                    'route' => route('admin.webmaster.contacts.index'),
                ];
            }
        }

        $uniqueItems = collect($items)
            ->unique(fn (array $item) => ($item['title'] ?? '') . '|' . ($item['message'] ?? ''))
            ->values()
            ->all();

        return [
            'count' => count($uniqueItems),
            'items' => array_slice($uniqueItems, 0, $limit),
            'documents_needed' => count($documentsNeeded),
            'trainings_needs_action' => (int) ($trainingsSummary['needs_action'] ?? 0),
            'credentials_at_risk' => $credentialCount,
        ];
    }

    public function build(User $user): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
            'currentAssignment.department',
            'currentAssignment.facility',
            'uploads',
        ]);

        $jobApplication = $user->jobApplications()->with('jobOpening')->latest()->first();
        $hasPreEmployment = $this->hasActivePreEmployment($jobApplication);
        $preEmploymentChecklists = $hasPreEmployment
            ? $user->employeeChecklists()->orderBy('item_label')->get()
            : collect();
        $checklistStats = $this->buildPreEmploymentStats($preEmploymentChecklists);

        $empChecklistItems = $bpEmployee
            ? (optional(BPEmpChecklist::query()->where('employee_num', $bpEmployee->employee_num)->first())->items ?? [])
            : [];

        $complianceEvaluation = $this->evaluateEmployeeChecklistCompliance($bpEmployee, $empChecklistItems);
        $documentsNeeded = $complianceEvaluation['missing'];
        $signaturesNeeded = $this->buildSignaturesNeeded($bpEmployee, $empChecklistItems);
        $reviewerAssessmentTodos = $this->buildReviewerAssessmentTodos($user);
        $employeeAssessmentTodos = $this->buildEmployeeAssessmentConfirmationTodos($user, $bpEmployee);
        $documentCompliance = $bpEmployee
            ? app(DocumentComplianceService::class)->forEmployee($bpEmployee)
            : [
                'position_id' => null,
                'position_title' => null,
                'department_id' => null,
                'items' => collect(),
                'summary' => [
                    'total' => 0,
                    'complete' => 0,
                    'expired' => 0,
                    'missing' => 0,
                ],
            ];
        $documentsCenter = $this->buildDocumentsCenter($user, $bpEmployee, $empChecklistItems, $documentCompliance);
        $facilityComplianceReport = $this->buildFacilityComplianceReport($user);
        $reminders = $this->buildReminders($bpEmployee, $empChecklistItems, $preEmploymentChecklists, $documentsNeeded);
        $todos = $this->buildTodos($user, $bpEmployee, $hasPreEmployment, $preEmploymentChecklists, $documentsNeeded, $signaturesNeeded, $reminders, $reviewerAssessmentTodos, $employeeAssessmentTodos);
        $calendarEvents = $this->buildCalendarEvents($bpEmployee, $reminders, $preEmploymentChecklists);
        $recentActivity = $this->buildRecentActivity($user, $bpEmployee, $preEmploymentChecklists, $empChecklistItems);
        $quickLinks = $this->buildQuickLinks($hasPreEmployment, (bool) $bpEmployee);

        $certSummary = ['expiring' => 0, 'expired' => 0, 'needs_attention' => 0];
        $certItems = [];
        if ($bpEmployee) {
            $certItems = $this->evaluateCertificationItems($bpEmployee, $empChecklistItems);
            foreach ($certItems as $cert) {
                $status = $cert['status'] ?? '';
                if (in_array($status, ['expiring_soon', 'expiring_urgent', 'expires_today'], true)) {
                    $certSummary['expiring']++;
                } elseif ($status === 'expired') {
                    $certSummary['expired']++;
                } elseif (!in_array($status, ['valid'], true)) {
                    $certSummary['needs_attention']++;
                }
            }
        }

        $trainingsCenter = $this->buildTrainingsCenter(
            $user,
            $bpEmployee,
            is_array($empChecklistItems) ? $empChecklistItems : [],
            $preEmploymentChecklists
        );
        $trainingsSummary = $trainingsCenter['summary'];

        $pendingActions = collect($todos)->where('done', false)->count();
        $stats = [
            'pending_actions' => $pendingActions,
            'documents_needed' => count($documentsNeeded),
            'signatures_needed' => count($signaturesNeeded),
            'reminders' => count($reminders),
            'checklist_completion' => $checklistStats['percent'] ?? null,
            'employee_file_verified' => $this->employeeFileVerifiedPercent($bpEmployee, $empChecklistItems),
            'certifications_expiring' => $certSummary['expiring'],
            'certifications_expired' => $certSummary['expired'],
            'certifications_needs_attention' => $certSummary['needs_attention'],
            'trainings_needs_action' => $trainingsSummary['needs_action'] ?? 0,
            'trainings_total' => $trainingsSummary['total'] ?? 0,
            'trainings_pending_signature' => $trainingsSummary['pending_signature'] ?? 0,
        ];

        return [
            'user' => $user,
            'bpEmployee' => $bpEmployee,
            'jobApplication' => $jobApplication,
            'hasPreEmployment' => $hasPreEmployment,
            'checklistStats' => $checklistStats,
            'preEmploymentChecklists' => $preEmploymentChecklists,
            'stats' => $stats,
            'todos' => $todos,
            'documentsNeeded' => $documentsNeeded,
            'certificationItems' => $certItems,
            'trainingsCenter' => $trainingsCenter,
            'documentsCenter' => $documentsCenter,
            'facilityComplianceReport' => $facilityComplianceReport,
            'signaturesNeeded' => $signaturesNeeded,
            'reminders' => $reminders,
            'calendarEvents' => $calendarEvents,
            'recentActivity' => $recentActivity,
            'quickLinks' => $quickLinks,
            'positionTitle' => $bpEmployee?->currentAssignment?->position?->title ?? '—',
            'facilityName' => $bpEmployee?->currentAssignment?->facility?->name ?? $user->facility?->name,
            'departmentName' => $bpEmployee?->currentAssignment?->department?->name,
            'employeeDisplayName' => $bpEmployee?->formalName() ?: $user->name,
        ];
    }

    /**
     * Documents page payload (avoids full dashboard build).
     *
     * @return array{documentsCenter: array, facilityComplianceReport: array|null, stats: array<string, mixed>}
     */
    public function buildDocumentsPage(User $user, ?Request $request = null): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
            'currentAssignment.department',
            'currentAssignment.facility',
            'uploads',
        ]);

        $empChecklistItems = $bpEmployee
            ? (optional(BPEmpChecklist::query()->where('employee_num', $bpEmployee->employee_num)->first())->items ?? [])
            : [];

        $documentCompliance = $bpEmployee
            ? app(DocumentComplianceService::class)->forEmployee($bpEmployee)
            : [
                'position_id' => null,
                'position_title' => null,
                'department_id' => null,
                'items' => collect(),
                'summary' => [
                    'total' => 0,
                    'complete' => 0,
                    'expired' => 0,
                    'missing' => 0,
                ],
            ];

        $documentsCenter = $this->buildDocumentsCenter(
            $user,
            $bpEmployee,
            $empChecklistItems,
            $documentCompliance,
            skipDocumentsList: true
        );

        $facility = $bpEmployee?->currentAssignment?->facility ?? $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        $documentFilters = $this->documentFiltersFromRequest($request);
        $documentsPaginator = $this->paginateEmployeeDocuments($bpEmployee, $facility, $user, $documentFilters);

        $documentsCenter['documents_paginator'] = $documentsPaginator;
        $documentsCenter['document_filters'] = $documentFilters;
        $documentsCenter['document_type_options'] = $this->documentTypeFilterOptions($bpEmployee);
        $documentsCenter['documents_total'] = $bpEmployee ? (int) $bpEmployee->uploads()->count() : 0;
        $documentsCenter['documents'] = $documentsPaginator->items();
        $documentsCenter['uploads'] = $documentsPaginator->items();
        $documentsCenter['position_id'] = $documentCompliance['position_id'] ?? null;
        $documentsCenter['position_title'] = $documentCompliance['position_title'] ?? null;

        $documentsNeeded = count($documentsCenter['compliance_missing'] ?? []);

        return [
            'documentsCenter' => $documentsCenter,
            'facilityComplianceReport' => $this->buildFacilityComplianceReport($user),
            'stats' => [
                'employee_file_verified' => $documentsCenter['verified_percent'] ?? null,
                'documents_needed' => $documentsNeeded,
            ],
        ];
    }

    /**
     * Certifications page payload (licenses & expiring checklist items).
     *
     * @return array{
     *     certificationsCenter: array<string, mixed>,
     *     facilityCertificationsReport: array<string, mixed>|null,
     *     stats: array<string, int>
     * }
     */
    public function buildCertificationsPage(User $user): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
            'currentAssignment.department',
            'currentAssignment.facility',
            'uploads',
        ]);

        $empChecklistItems = $bpEmployee
            ? (optional(BPEmpChecklist::query()->where('employee_num', $bpEmployee->employee_num)->first())->items ?? [])
            : [];

        $facility = $bpEmployee?->currentAssignment?->facility ?? $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        $certificationsCenter = $this->buildCertificationsCenter(
            $user,
            $bpEmployee,
            is_array($empChecklistItems) ? $empChecklistItems : [],
            $facility
        );

        return [
            'certificationsCenter' => $certificationsCenter,
            'facilityCertificationsReport' => $this->buildFacilityCertificationsReport($user),
            'stats' => $certificationsCenter['summary'],
        ];
    }

    /**
     * Trainings page payload (orientation, competency, required checklist items).
     *
     * @return array{
     *     trainingsCenter: array<string, mixed>,
     *     facilityTrainingsReport: array<string, mixed>|null,
     *     stats: array<string, int>
     * }
     */
    public function buildTrainingsPage(User $user, ?int $assessmentPeriodId = null): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
            'currentAssignment.department',
            'currentAssignment.facility',
        ]);

        $jobApplication = $user->jobApplications()->latest()->first();
        $hasPreEmployment = $this->hasActivePreEmployment($jobApplication);
        $preEmploymentChecklists = $hasPreEmployment
            ? $user->employeeChecklists()->orderBy('item_label')->get()
            : collect();

        $empChecklistItems = $bpEmployee
            ? (optional(BPEmpChecklist::query()->where('employee_num', $bpEmployee->employee_num)->first())->items ?? [])
            : [];

        $assessmentPeriods = collect();
        $selectedAssessmentPeriodId = null;
        if ($bpEmployee) {
            $periodService = app(EmployeeAssessmentPeriodService::class);
            $assessmentPeriods = $periodService->periodsForEmployee($bpEmployee);
            $selectedAssessmentPeriodId = $assessmentPeriodId
                && $assessmentPeriods->contains(fn ($period) => (int) $period->id === (int) $assessmentPeriodId)
                ? (int) $assessmentPeriodId
                : $periodService->resolveDefaultPeriodId($bpEmployee);
        }

        $trainingsCenter = $this->buildTrainingsCenter(
            $user,
            $bpEmployee,
            is_array($empChecklistItems) ? $empChecklistItems : [],
            $preEmploymentChecklists,
            $selectedAssessmentPeriodId
        );

        return [
            'trainingsCenter' => $trainingsCenter,
            'facilityTrainingsReport' => $this->buildFacilityTrainingsReport($user),
            'stats' => $trainingsCenter['summary'],
            'assessmentPeriods' => $assessmentPeriods,
            'selectedAssessmentPeriodId' => $selectedAssessmentPeriodId,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @param  Collection<int, EmployeeChecklist>  $preEmploymentChecklists
     * @return array<string, mixed>
     */
    protected function buildTrainingsCenter(
        User $user,
        ?BPEmployee $bpEmployee,
        array $empChecklistItems,
        Collection $preEmploymentChecklists,
        ?int $assessmentPeriodId = null
    ): array {
        $employmentPortalUrl = route('employment.portal');

        if ($bpEmployee && ! $assessmentPeriodId) {
            $assessmentPeriodId = app(EmployeeAssessmentPeriodService::class)->resolveDefaultPeriodId($bpEmployee);
        }

        $moduleTrainings = $bpEmployee
            ? $this->collectModuleTrainings($bpEmployee, $assessmentPeriodId)
            : ['hiring' => [], 'annual' => []];

        $orientationBundle = $bpEmployee
            ? $this->collectOrientationTrainings($empChecklistItems, $employmentPortalUrl)
            : ['items' => [], 'history_documents' => []];

        $competencyBundle = $bpEmployee
            ? $this->collectCompetencyTrainings(
                $bpEmployee->employee_num,
                $assessmentPeriodId
            )
            : ['items' => [], 'history_documents' => []];

        $performanceBundle = $bpEmployee
            ? $this->collectPerformanceChecklists(
                $bpEmployee->employee_num,
                $assessmentPeriodId
            )
            : ['items' => [], 'history_documents' => []];

        $requiredItems = $bpEmployee
            ? $this->evaluateTrainingChecklistItems($bpEmployee, $empChecklistItems, $employmentPortalUrl)
            : [];

        $preEmploymentItems = $this->mapPreEmploymentTrainings($preEmploymentChecklists);

        $trainingHistory = $bpEmployee
            ? $this->collectModuleTrainingHistory($bpEmployee, $assessmentPeriodId)
            : [];

        $groups = [
            [
                'key' => 'modules_annual',
                'bucket' => 'annual',
                'section' => 'trainings',
                'label' => 'Trainings',
                'description' => 'Recurring modules (annual, every 2 years, etc.) for the selected assessment period. Use history for earlier periods.',
                'items' => $moduleTrainings['annual'],
                'interactive' => true,
                'employee_can_act' => true,
                'history_documents' => $trainingHistory,
            ],
            [
                'key' => 'competency',
                'bucket' => 'annual',
                'section' => 'competencies',
                'label' => 'Competencies',
                'description' => 'All competencies required for your role in the selected assessment period, including those not started yet.',
                'items' => $competencyBundle['items'],
                'read_only' => true,
                'history_documents' => $competencyBundle['history_documents'],
            ],
            [
                'key' => 'performance',
                'bucket' => 'annual',
                'section' => 'performance',
                'label' => 'Performance',
                'description' => 'Performance appraisal for the selected assessment period, with status and PDF when available.',
                'items' => $performanceBundle['items'],
                'read_only' => true,
                'history_documents' => $performanceBundle['history_documents'],
            ],
            [
                'key' => 'modules_hiring',
                'bucket' => 'upon_hiring',
                'section' => 'trainings',
                'label' => 'Trainings',
                'description' => 'You start each module and submit when finished. DSD or supervisors confirm completion.',
                'items' => $moduleTrainings['hiring'],
                'interactive' => true,
                'employee_can_act' => true,
                'history_documents' => [],
            ],
            [
                'key' => 'orientation',
                'bucket' => 'upon_hiring',
                'section' => 'orientation',
                'label' => 'Orientation',
                'description' => 'Orientation progress. DSD or supervisors initiate and complete Part E.',
                'items' => $orientationBundle['items'],
                'read_only' => true,
                'history_documents' => $orientationBundle['history_documents'],
            ],
            [
                'key' => 'required',
                'bucket' => 'upon_hiring',
                'section' => 'acknowledgements',
                'label' => 'Acknowledgements',
                'description' => 'Read-only status for Parts B–D file items managed with your employment record.',
                'items' => $requiredItems,
                'read_only' => true,
                'history_documents' => [],
            ],
        ];

        if (count($preEmploymentItems) > 0) {
            $groups[] = [
                'key' => 'pre_employment',
                'bucket' => 'upon_hiring',
                'section' => 'pre_employment',
                'label' => 'Pre-employment checklist',
                'description' => 'Onboarding tasks before your start date',
                'items' => $preEmploymentItems,
                'history_documents' => [],
            ];
        }

        $allItems = collect($groups)->flatMap(fn ($group) => $group['items'])->all();
        $summary = $this->summarizeTrainingItems($allItems);

        return [
            'groups' => $groups,
            'buckets' => [
                [
                    'key' => 'annual',
                    'label' => 'Annual checklists',
                    'description' => 'Items tied to the selected assessment period.',
                    'uses_assessment_period' => true,
                ],
                [
                    'key' => 'upon_hiring',
                    'label' => 'Upon hiring checklists',
                    'description' => 'One-time onboarding and hire-period checklists.',
                    'uses_assessment_period' => false,
                ],
            ],
            'items' => $allItems,
            'summary' => $summary,
            'has_employee_record' => (bool) $bpEmployee,
            'has_pre_employment' => count($preEmploymentItems) > 0,
            'assessment_period_id' => $assessmentPeriodId,
        ];
    }

    /**
     * Part H training modules the employee can start / submit from My Checklists.
     *
     * @return array{hiring: list<array<string, mixed>>, annual: list<array<string, mixed>>}
     */
    protected function collectModuleTrainings(BPEmployee $employee, ?int $assessmentPeriodId): array
    {
        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $items = \App\Models\EmployeeTrainingItem::query()
            ->active()
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $hireCompletions = \App\Models\EmployeeTrainingCompletion::query()
            ->where('employee_num', $employee->employee_num)
            ->where('period_key', \App\Models\EmployeeTrainingCompletion::PERIOD_KEY_HIRE)
            ->get()
            ->keyBy('employee_training_item_id');

        $periodCompletions = $assessmentPeriodId
            ? \App\Models\EmployeeTrainingCompletion::query()
                ->where('employee_num', $employee->employee_num)
                ->where('period_key', (string) (int) $assessmentPeriodId)
                ->get()
                ->keyBy('employee_training_item_id')
            : collect();

        $latestCompletedAt = \App\Models\EmployeeTrainingCompletion::query()
            ->where('employee_num', $employee->employee_num)
            ->where('status', \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED)
            ->whereIn('employee_training_item_id', $items->pluck('id'))
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get(['employee_training_item_id', 'completed_at'])
            ->unique('employee_training_item_id')
            ->mapWithKeys(fn ($row) => [(int) $row->employee_training_item_id => $row->completed_at]);

        $today = Carbon::today();
        $hiring = [];
        $annual = [];

        foreach ($items as $item) {
            $isHiring = $item->isHiring();
            $completion = $isHiring
                ? $hireCompletions->get($item->id)
                : ($assessmentPeriodId ? $periodCompletions->get($item->id) : null);

            $lastCompletedAt = $latestCompletedAt->get((int) $item->id)
                ?? ($completion?->status === \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED
                    ? $completion->completed_at
                    : null);

            $row = $this->mapModuleTrainingRow(
                $item,
                $completion,
                $today,
                $isHiring ? null : $assessmentPeriodId,
                $lastCompletedAt
            );
            if ($isHiring) {
                $hiring[] = $row;
            } else {
                $annual[] = $row;
            }
        }

        usort($hiring, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));
        usort($annual, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        return ['hiring' => $hiring, 'annual' => $annual];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapModuleTrainingRow(
        \App\Models\EmployeeTrainingItem $item,
        ?\App\Models\EmployeeTrainingCompletion $completion,
        Carbon $today,
        ?int $assessmentPeriodId,
        mixed $lastCompletedAt = null
    ): array {
        $due = $item->evaluateDue(
            $lastCompletedAt ? Carbon::parse($lastCompletedAt) : null,
            $today
        );

        $hasOpenPeriodWork = $completion && ! in_array($completion->status, [
            \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED,
            \App\Models\EmployeeTrainingCompletion::STATUS_NA,
            \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED,
            null,
        ], true);

        $satisfiedFromPrior = $item->isRecurring()
            && ! $completion
            && ! $due['due'];

        $status = $completion?->status ?? \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED;
        if ($satisfiedFromPrior) {
            $statusMeta = $this->trainingStatusRow('completed', $due['status_hint'] ?? 'Current', $today, null);
            $statusMeta['badge_class'] = 'bg-emerald-50 text-emerald-800';
        } else {
            $statusMeta = match ($status) {
                \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED => $this->trainingStatusRow('completed', 'Completed', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_IN_PROGRESS => $this->trainingStatusRow('in_progress', 'In progress', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED => $this->trainingStatusRow('in_progress', 'Submitted for review', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_REJECTED => $this->trainingStatusRow('overdue', 'Returned — revise', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_NA => $this->trainingStatusRow('completed', 'N/A', $today, null),
                default => $this->trainingStatusRow('not_started', 'Not started', $today, null),
            };
        }

        // Preserve submitted as its own status for UI actions while keeping summary-friendly mapping above
        if (! $satisfiedFromPrior && $status === \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED) {
            $statusMeta['status'] = 'submitted';
            $statusMeta['status_label'] = 'Submitted for review';
            $statusMeta['badge_class'] = 'bg-amber-50 text-amber-800';
        } elseif (! $satisfiedFromPrior && $status === \App\Models\EmployeeTrainingCompletion::STATUS_REJECTED) {
            $statusMeta['status'] = 'rejected';
            $statusMeta['status_label'] = 'Returned — revise';
            $statusMeta['badge_class'] = 'bg-rose-50 text-rose-700';
        }

        $canStart = ! $satisfiedFromPrior && (! $completion || $completion->employeeCanStart());
        $canSubmit = ! $satisfiedFromPrior && $completion && $completion->employeeCanSubmit();
        // Allow submit from not_started via controller auto-start, but prefer Start first in UI
        $moduleUrl = $item->resolvedContentUrl();

        $subtitleParts = array_filter([
            $item->provider_label,
            $item->frequencyShortLabel(),
            $due['status_hint'] && $item->isRecurring() && ($satisfiedFromPrior || $hasOpenPeriodWork === false)
                ? $due['status_hint']
                : null,
            $item->description,
        ]);

        return array_merge($statusMeta, [
            'id' => 'training-module-'.$item->id.($assessmentPeriodId ? '-p'.$assessmentPeriodId : '-hire'),
            'title' => $item->name,
            'subtitle' => implode(' · ', $subtitleParts) ?: null,
            'category' => 'module',
            'interactive' => true,
            'training_item_id' => $item->id,
            'assessment_period_id' => $assessmentPeriodId,
            'workflow_status' => $satisfiedFromPrior ? 'satisfied' : $status,
            'module_url' => $moduleUrl,
            'can_start' => $canStart && ($item->isHiring() || $assessmentPeriodId),
            'can_submit' => $canSubmit && ($item->isHiring() || $assessmentPeriodId),
            'period_required' => $item->requiresAssessmentPeriod() && ! $assessmentPeriodId,
            'frequency' => $item->frequency,
            'frequency_label' => $item->frequencyShortLabel(),
            'due' => $due['due'] || $hasOpenPeriodWork,
            'satisfied_until' => $due['satisfied_until']?->toDateString(),
            'rejection_reason' => $completion?->rejection_reason,
            'notes' => $completion?->notes,
            'action_url' => $moduleUrl ?: '#',
            'action_label' => $moduleUrl ? 'Open module' : '—',
            'start_url' => route('member.checklists.start', $item),
            'submit_url' => route('member.checklists.submit', $item),
        ]);
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return array{items: list<array<string, mixed>>, history_documents: list<array<string, mixed>>}
     */
    protected function collectOrientationTrainings(array $empChecklistItems, string $actionUrl): array
    {
        $items = [];
        $historyDocuments = [];
        $today = Carbon::today();

        foreach ($empChecklistItems as $storageKey => $payload) {
            if (! is_string($storageKey) || ! str_starts_with($storageKey, 'part_e_orientation_summary_')) {
                continue;
            }

            $row = $this->mapOrientationTraining($storageKey, is_array($payload) ? $payload : [], $actionUrl, $today);
            $workflow = (string) (($payload['workflow_status'] ?? PartEOrientationChecklist::WORKFLOW_DRAFT));

            if ($workflow === PartEOrientationChecklist::WORKFLOW_COMPLETED) {
                $historyDocuments[] = [
                    'id' => 'orientation-history-'.$storageKey,
                    'title' => $row['title'],
                    'subtitle' => $row['history'] ?? 'Completed orientation record',
                    'status_label' => $row['status_label'] ?? 'Completed',
                    'badge_class' => $row['badge_class'] ?? 'bg-emerald-50 text-emerald-700',
                    'pdf_url' => null,
                    'period_label' => null,
                ];
            }

            $items[] = $row;
        }

        if (count($items) === 0) {
            $items[] = array_merge(
                $this->trainingStatusRow('not_started', 'Not started', $today, null),
                [
                    'id' => 'training-orientation-default',
                    'title' => 'Part E orientation checklist',
                    'subtitle' => 'Not started yet. Your DSD or supervisor will initiate orientation.',
                    'category' => 'orientation',
                    'read_only' => true,
                    'history' => 'No orientation activity recorded yet.',
                    'pdf_url' => null,
                    'can_view_pdf' => false,
                    'action_url' => null,
                    'action_label' => null,
                ]
            );
        }

        usort($items, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        // Keep only the primary current orientation row in the main list; completed copies live under history.
        $currentItems = collect($items)
            ->reject(fn (array $row) => ($row['workflow_status'] ?? '') === PartEOrientationChecklist::WORKFLOW_COMPLETED && count($items) > 1)
            ->values()
            ->all();

        if ($currentItems === [] && $items !== []) {
            $currentItems = [collect($items)->sortByDesc(fn ($row) => $row['due_at'] ?? '')->first()];
        }

        return [
            'items' => $currentItems,
            'history_documents' => $historyDocuments,
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    protected function mapOrientationTraining(string $storageKey, array $stored, string $actionUrl, Carbon $today): array
    {
        $suffix = str_replace('part_e_orientation_summary_', '', $storageKey);
        $title = 'Part E orientation checklist';
        if ($suffix !== '' && $suffix !== 'none') {
            $title .= ' (job code '.$suffix.')';
        }

        $workflow = (string) ($stored['workflow_status'] ?? PartEOrientationChecklist::WORKFLOW_DRAFT);
        $statusMeta = $this->resolveOrientationTrainingStatus($workflow, $today);

        $historyParts = array_filter([
            'Workflow: '.str_replace('_', ' ', $workflow),
            ! empty($stored['completed_at']) ? 'Completed: '.$stored['completed_at'] : null,
            ! empty($stored['employee_signed_at']) ? 'Employee signed: '.$stored['employee_signed_at'] : null,
            ! empty($stored['reviewer_signed_at']) ? 'Reviewer signed: '.$stored['reviewer_signed_at'] : null,
        ]);

        return array_merge($statusMeta, [
            'id' => 'training-orientation-'.$storageKey,
            'title' => $title,
            'subtitle' => PartEOrientationChecklist::WORKFLOW_COMPLETED === $workflow
                ? 'Orientation completed by facility leadership.'
                : 'Managed by DSD / supervisors — view progress only.',
            'category' => 'orientation',
            'workflow_status' => $workflow,
            'read_only' => true,
            'history' => implode(' · ', $historyParts) ?: null,
            'pdf_url' => null,
            'can_view_pdf' => false,
            'action_url' => null,
            'action_label' => null,
        ]);
    }

    /**
     * @return array{items: list<array<string, mixed>>, history_documents: list<array<string, mixed>>}
     */
    protected function collectCompetencyTrainings(
        string $employeeNum,
        ?int $assessmentPeriodId
    ): array {
        $today = Carbon::today();
        $historyRows = \App\Support\CompetencyAssessmentHistoryResolver::resolveForEmployee(
            $employeeNum,
            $assessmentPeriodId
        );

        $employee = BPEmployee::query()
            ->with('currentAssignment.position')
            ->where('employee_num', $employeeNum)
            ->first();

        $positionId = $employee?->currentAssignment?->position_id
            ?? $employee?->currentAssignment?->position?->id;

        $applicableSections = CompetencyAssessmentWorkflowReadiness::applicableSectionLabels(
            $positionId ? (int) $positionId : null
        );

        $period = $assessmentPeriodId
            ? EmployeeAssessmentPeriod::query()->find($assessmentPeriodId)
            : null;
        $periodLabel = $this->formatPeriodLabel($period)
            ?? ($assessmentPeriodId ? 'Period #'.$assessmentPeriodId : null);

        $submission = $assessmentPeriodId
            ? EmployeeCompetencyAssessment::query()
                ->where('employee_num', $employeeNum)
                ->where('assessment_period_id', $assessmentPeriodId)
                ->first()
            : null;

        $excludedSections = collect(
            is_array($submission?->snapshot_json)
                ? ($submission->snapshot_json['excluded_section_labels'] ?? [])
                : []
        )
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '')
            ->values()
            ->all();

        $currentItems = [];
        $historyDocuments = [];
        $seenCurrentSections = [];

        foreach ($historyRows as $row) {
            $periodId = (int) ($row['assessment_period_id'] ?? 0);
            $isCurrent = $assessmentPeriodId !== null && $periodId === (int) $assessmentPeriodId;
            $sectionLabel = trim((string) ($row['competency_section'] ?? $row['competency_name'] ?? ''));
            $statusMeta = $this->mapDisplayedChecklistStatus((string) ($row['status'] ?? 'Not started'), $today);
            $canViewPdf = ! empty($row['can_view_pdf'])
                && ! empty($row['competency_assessment_id'])
                && $sectionLabel !== '';
            $pdfUrl = $canViewPdf
                ? route('member.checklists.competency-section.pdf', [
                    'assessment' => $row['competency_assessment_id'],
                    'section' => $sectionLabel,
                ])
                : null;

            $mapped = array_merge($statusMeta, [
                'id' => 'training-competency-'.$periodId.'-'.md5($sectionLabel !== '' ? $sectionLabel : (string) ($row['competency_name'] ?? '')),
                'title' => $sectionLabel !== '' ? $sectionLabel : (string) ($row['competency_name'] ?? 'Competency'),
                'competency_section' => $sectionLabel !== '' ? $sectionLabel : null,
                'subtitle' => trim(implode(' · ', array_filter([
                    $row['period_label'] ?? null,
                    ! empty($row['reviewer_name']) ? 'Reviewer: '.$row['reviewer_name'] : null,
                    isset($row['items_count'], $row['total_items'])
                        ? 'Rated '.$row['items_count'].'/'.$row['total_items']
                        : null,
                ]))),
                'category' => 'competency',
                'assessment_id' => $row['competency_assessment_id'] ?? null,
                'assessment_period_id' => $periodId ?: null,
                'read_only' => true,
                'history' => $row['period_label'] ?? null,
                'pdf_url' => $pdfUrl,
                'can_view_pdf' => $canViewPdf,
                'action_url' => null,
                'action_label' => null,
            ]);

            if ($isCurrent) {
                $currentItems[] = $mapped;
                if ($sectionLabel !== '') {
                    $seenCurrentSections[mb_strtolower($sectionLabel)] = true;
                }
            } elseif ($canViewPdf || ($row['items_count'] ?? 0) > 0) {
                $historyDocuments[] = [
                    'id' => $mapped['id'].'-history',
                    'title' => $mapped['title'],
                    'subtitle' => $mapped['subtitle'],
                    'status_label' => $mapped['status_label'],
                    'badge_class' => $mapped['badge_class'],
                    'pdf_url' => $pdfUrl,
                    'period_label' => $row['period_label'] ?? null,
                ];
            }
        }

        // Include every role-required competency for the selected period, even if not started.
        if ($assessmentPeriodId) {
            foreach ($applicableSections as $sectionLabel) {
                $sectionLabel = trim((string) $sectionLabel);
                if ($sectionLabel === '') {
                    continue;
                }

                if (in_array($sectionLabel, $excludedSections, true)) {
                    continue;
                }

                $sectionKey = mb_strtolower($sectionLabel);
                if (isset($seenCurrentSections[$sectionKey])) {
                    continue;
                }

                $totalItems = \App\Support\CompetencyAssessmentHistoryBuilder::rateableItemCountForSection($sectionLabel);
                $statusMeta = $this->trainingStatusRow('not_started', 'Not Started', $today, null);

                $currentItems[] = array_merge($statusMeta, [
                    'id' => 'training-competency-'.$assessmentPeriodId.'-'.md5($sectionLabel),
                    'title' => $sectionLabel,
                    'competency_section' => $sectionLabel,
                    'subtitle' => trim(implode(' · ', array_filter([
                        $periodLabel,
                        $totalItems > 0 ? 'Rated 0/'.$totalItems : null,
                        'Required for your role — not started yet',
                    ]))),
                    'category' => 'competency',
                    'assessment_id' => $submission?->id,
                    'assessment_period_id' => $assessmentPeriodId,
                    'read_only' => true,
                    'history' => $periodLabel,
                    'pdf_url' => null,
                    'can_view_pdf' => false,
                    'action_url' => null,
                    'action_label' => null,
                ]);

                $seenCurrentSections[$sectionKey] = true;
            }
        }

        if ($currentItems === [] && $assessmentPeriodId) {
            $currentItems[] = array_merge(
                $this->trainingStatusRow('not_started', 'Not started', $today, null),
                [
                    'id' => 'training-competency-empty-'.$assessmentPeriodId,
                    'title' => 'Competency sections',
                    'subtitle' => $applicableSections === []
                        ? 'No competency sections are assigned to your role for this assessment period.'
                        : 'No competency ratings have been started for this assessment period yet.',
                    'category' => 'competency',
                    'read_only' => true,
                    'history' => null,
                    'pdf_url' => null,
                    'can_view_pdf' => false,
                    'action_url' => null,
                    'action_label' => null,
                ]
            );
        }

        usort($currentItems, function (array $a, array $b): int {
            $priority = $this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '');
            if ($priority !== 0) {
                return $priority;
            }

            return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
        });

        return [
            'items' => $currentItems,
            'history_documents' => $historyDocuments,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, history_documents: list<array<string, mixed>>}
     */
    protected function collectPerformanceChecklists(
        string $employeeNum,
        ?int $assessmentPeriodId
    ): array {
        $today = Carbon::today();
        $assessments = EmployeePerformanceAssessment::query()
            ->where('employee_num', $employeeNum)
            ->with('period')
            ->orderByDesc('updated_at')
            ->get();

        $currentItems = [];
        $historyDocuments = [];

        foreach ($assessments as $assessment) {
            $periodId = (int) ($assessment->assessment_period_id ?? 0);
            $isCurrent = $assessmentPeriodId !== null && $periodId === (int) $assessmentPeriodId;
            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $statusMeta = $this->resolvePerformanceChecklistStatus($assessment, $today);
            $pdfUrl = route('member.checklists.performance-assessment.pdf', $assessment);
            $canViewPdf = true;

            $mapped = array_merge($statusMeta, [
                'id' => 'checklist-performance-'.$assessment->id,
                'title' => 'Performance appraisal'.($periodLabel ? ' · '.$periodLabel : ''),
                'subtitle' => 'Managed by DSD / supervisors — view status and PDF.',
                'category' => 'performance',
                'assessment_id' => $assessment->id,
                'assessment_period_id' => $periodId ?: null,
                'read_only' => true,
                'history' => implode(' · ', array_filter([
                    'Status: '.AssessmentWorkflowStatus::label($assessment->workflowStatus()),
                    $assessment->acknowledge_dt ? 'Acknowledged: '.$assessment->acknowledge_dt->format('Y-m-d') : null,
                    $assessment->review_dt ? 'Reviewed: '.$assessment->review_dt->format('Y-m-d') : null,
                ])) ?: null,
                'pdf_url' => $pdfUrl,
                'can_view_pdf' => $canViewPdf,
                'action_url' => null,
                'action_label' => null,
            ]);

            if ($isCurrent) {
                $currentItems[] = $mapped;
            } else {
                $historyDocuments[] = [
                    'id' => $mapped['id'].'-history',
                    'title' => $mapped['title'],
                    'subtitle' => $mapped['history'],
                    'status_label' => $mapped['status_label'],
                    'badge_class' => $mapped['badge_class'],
                    'pdf_url' => $pdfUrl,
                    'period_label' => $periodLabel,
                ];
            }
        }

        if ($currentItems === []) {
            $currentItems[] = array_merge(
                $this->trainingStatusRow('not_started', 'Not started', $today, null),
                [
                    'id' => 'checklist-performance-empty',
                    'title' => 'Performance appraisal',
                    'subtitle' => 'No performance appraisal has been started for the current assessment period yet.',
                    'category' => 'performance',
                    'read_only' => true,
                    'history' => null,
                    'pdf_url' => null,
                    'can_view_pdf' => false,
                    'action_url' => null,
                    'action_label' => null,
                ]
            );
        }

        usort($currentItems, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        return [
            'items' => $currentItems,
            'history_documents' => $historyDocuments,
        ];
    }

    /**
     * Prior annual training completions outside the selected/current period.
     *
     * @return list<array<string, mixed>>
     */
    protected function collectModuleTrainingHistory(BPEmployee $employee, ?int $assessmentPeriodId): array
    {
        $today = Carbon::today();
        $completions = \App\Models\EmployeeTrainingCompletion::query()
            ->with('trainingItem')
            ->where('employee_num', $employee->employee_num)
            ->where('period_key', '!=', \App\Models\EmployeeTrainingCompletion::PERIOD_KEY_HIRE)
            ->when($assessmentPeriodId, fn ($q) => $q->where('period_key', '!=', (string) (int) $assessmentPeriodId))
            ->orderByDesc('updated_at')
            ->get();

        $periodIds = $completions
            ->pluck('period_key')
            ->filter(fn ($key) => is_numeric($key))
            ->map(fn ($key) => (int) $key)
            ->unique()
            ->values();

        $periods = $periodIds->isEmpty()
            ? collect()
            : EmployeeAssessmentPeriod::query()->whereIn('id', $periodIds)->get()->keyBy('id');

        $rows = [];
        foreach ($completions as $completion) {
            $item = $completion->trainingItem;
            if (! $item) {
                continue;
            }

            $periodId = is_numeric($completion->period_key) ? (int) $completion->period_key : null;
            $periodLabel = $periodId && $periods->has($periodId)
                ? $this->formatPeriodLabel($periods->get($periodId))
                : ('Period '.$completion->period_key);

            $statusMeta = match ($completion->status) {
                \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED => $this->trainingStatusRow('completed', 'Completed', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED => $this->trainingStatusRow('submitted', 'Submitted for review', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_IN_PROGRESS => $this->trainingStatusRow('in_progress', 'In progress', $today, null),
                \App\Models\EmployeeTrainingCompletion::STATUS_REJECTED => $this->trainingStatusRow('rejected', 'Returned — revise', $today, null),
                default => $this->trainingStatusRow('not_started', 'Not started', $today, null),
            };

            $rows[] = [
                'id' => 'training-history-'.$completion->id,
                'title' => $item->name,
                'subtitle' => $periodLabel,
                'status_label' => $statusMeta['status_label'],
                'badge_class' => $statusMeta['badge_class'],
                'pdf_url' => $item->resolvedContentUrl(),
                'period_label' => $periodLabel,
            ];
        }

        return $rows;
    }

    protected function mapDisplayedChecklistStatus(string $label, Carbon $today): array
    {
        $lower = strtolower(trim($label));

        if ($lower === '' || str_contains($lower, 'not started') || str_contains($lower, 'pending start')) {
            return $this->trainingStatusRow('not_started', $label !== '' ? $label : 'Not started', $today, null);
        }

        if (str_contains($lower, 'complet') || str_contains($lower, 'approved') || str_contains($lower, 'signed')) {
            return $this->trainingStatusRow('completed', $label, $today, null);
        }

        if (str_contains($lower, 'confirm') || str_contains($lower, 'signature') || str_contains($lower, 'acknowledg')) {
            return $this->trainingStatusRow('pending_signature', $label, $today, null);
        }

        if (str_contains($lower, 'overdue')) {
            return $this->trainingStatusRow('overdue', $label, $today, null);
        }

        return $this->trainingStatusRow('in_progress', $label, $today, null);
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return list<array<string, mixed>>
     */
    protected function evaluateTrainingChecklistItems(
        BPEmployee $employee,
        array $empChecklistItems,
        string $actionUrl
    ): array {
        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $applicableItems = ChecklistItem::query()
            ->applicableToPosition($positionId)
            ->whereIn('section', ['PART B', 'PART C', 'PART D'])
            ->where('isExpiring', false)
            ->orderBy('order')
            ->get();

        $today = Carbon::today();
        $rows = [];

        foreach ($applicableItems as $item) {
            $key = 'item_' . $item->id;
            $stored = $empChecklistItems[$key] ?? $empChecklistItems[$item->name] ?? null;
            $onFile = is_array($stored) && !empty($stored['on_file']);
            $verified = is_array($stored) && !empty($stored['verified_dt']);

            if ($onFile && $verified) {
                $statusMeta = $this->trainingStatusRow('completed', 'Completed', $today, null);
            } elseif ($onFile) {
                $statusMeta = $this->trainingStatusRow('in_progress', 'On file — pending verification', $today, null);
            } else {
                $statusMeta = $this->trainingStatusRow('not_started', 'Not on file', $today, null);
            }

            $rows[] = array_merge($statusMeta, [
                'id' => 'training-checklist-' . $item->id,
                'title' => $item->name,
                'subtitle' => $item->section.' · Managed with your employment file',
                'category' => 'required',
                'section' => $item->section,
                'read_only' => true,
                'history' => $onFile
                    ? ($verified ? 'On file and verified' : 'On file — pending verification')
                    : 'Not on file yet',
                'action_url' => null,
                'action_label' => null,
            ]);
        }

        usort($rows, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        return $rows;
    }

    /**
     * @param  Collection<int, EmployeeChecklist>  $preEmploymentChecklists
     * @return list<array<string, mixed>>
     */
    protected function mapPreEmploymentTrainings(Collection $preEmploymentChecklists): array
    {
        if ($preEmploymentChecklists->isEmpty()) {
            return [];
        }

        $today = Carbon::today();
        $portalUrl = route('pre-employment.portal');

        return $preEmploymentChecklists->map(function (EmployeeChecklist $item) use ($today, $portalUrl) {
            $rawStatus = (string) ($item->status ?? '');
            $statusMeta = match ($rawStatus) {
                'completed' => $this->trainingStatusRow('completed', 'Completed', $today, null),
                'submitted' => $this->trainingStatusRow('in_progress', 'Submitted — under review', $today, null),
                'returned' => $this->trainingStatusRow('overdue', 'Returned — corrections needed', $today, null),
                'draft' => $this->trainingStatusRow('in_progress', 'Draft in progress', $today, null),
                default => $this->trainingStatusRow('not_started', 'Not started', $today, null),
            };

            return array_merge($statusMeta, [
                'id' => 'training-pe-' . $item->id,
                'title' => $item->item_label ?? 'Checklist item',
                'subtitle' => 'Pre-employment onboarding',
                'category' => 'pre_employment',
                'action_url' => $portalUrl,
                'action_label' => 'Open pre-employment portal',
            ]);
        })->values()->all();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{total: int, completed: int, in_progress: int, needs_action: int, pending_signature: int, overdue: int}
     */
    protected function summarizeTrainingItems(array $items): array
    {
        $summary = [
            'total' => count($items),
            'completed' => 0,
            'in_progress' => 0,
            'needs_action' => 0,
            'pending_signature' => 0,
            'overdue' => 0,
        ];

        foreach ($items as $item) {
            $status = $item['status'] ?? '';
            match ($status) {
                'completed' => $summary['completed']++,
                'in_progress', 'submitted' => $summary['in_progress']++,
                'pending_signature' => $summary['pending_signature']++,
                'overdue', 'rejected' => $summary['overdue']++,
                default => null,
            };

            if (in_array($status, ['pending_signature', 'overdue', 'not_started', 'rejected'], true)) {
                $summary['needs_action']++;
            }
        }

        return $summary;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildFacilityTrainingsReport(User $user): ?array
    {
        if (!$user->hasRole(['facility-admin', 'facility-dsd'])) {
            return null;
        }

        $facility = $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        if (!$facility) {
            $bpEmployee = $user->resolvedBpEmployee(['currentAssignment.facility']);
            $facility = $bpEmployee?->currentAssignment?->facility;
        }

        if (!$facility) {
            return null;
        }

        $facilityKey = $facility->slug ?? $facility->id;

        $employees = BPEmployee::query()
            ->with(['currentAssignment.position'])
            ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facility->id))
            ->orderedByName()
            ->get();

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $employees->pluck('employee_num')->filter()->all())
            ->get()
            ->keyBy('employee_num');

        $rows = [];
        $employeesWithIssues = 0;
        $totalIncompleteOrientation = 0;
        $totalUnsignedCompetency = 0;
        $totalIncompleteTraining = 0;

        foreach ($employees as $employee) {
            $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $empItems = is_array($items) ? $items : [];
            $row = $this->summarizeEmployeeTraining($employee, $empItems);
            $rows[] = $row;

            if ($row['issue_count'] > 0) {
                $employeesWithIssues++;
            }
            $totalIncompleteOrientation += $row['incomplete_orientation'];
            $totalUnsignedCompetency += $row['unsigned_competency'];
            $totalIncompleteTraining += $row['incomplete_training'];
        }

        $rows = BPEmployee::sortTableRowsByName($rows);

        return [
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'key' => $facilityKey,
            ],
            'summary' => [
                'total_employees' => $employees->count(),
                'employees_with_issues' => $employeesWithIssues,
                'incomplete_orientation' => $totalIncompleteOrientation,
                'unsigned_competency' => $totalUnsignedCompetency,
                'incomplete_training' => $totalIncompleteTraining,
            ],
            'employees' => $rows,
            'employees_list_url' => route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return array<string, mixed>
     */
    public function summarizeEmployeeTraining(BPEmployee $employee, array $empChecklistItems): array
    {
        $orientationItems = $this->collectOrientationTrainings($empChecklistItems, '#')['items'];
        $incompleteOrientation = collect($orientationItems)
            ->where('status', '!=', 'completed')
            ->count();

        $unsignedCompetency = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->whereNull('employee_signed_at')
            ->whereIn('status', ['submitted', 'completed'])
            ->count();

        $requiredItems = $this->evaluateTrainingChecklistItems($employee, $empChecklistItems, '#');
        $incompleteTraining = collect($requiredItems)
            ->whereIn('status', ['not_started', 'in_progress', 'overdue', 'pending_signature'])
            ->count();

        $overdueCount = collect($requiredItems)->where('status', 'overdue')->count()
            + collect($orientationItems)->where('status', 'overdue')->count();

        $issueCount = $incompleteOrientation + $unsignedCompetency + $incompleteTraining;

        $topIssues = [];
        if ($incompleteOrientation > 0) {
            $topIssues[] = 'Orientation incomplete';
        }
        if ($unsignedCompetency > 0) {
            $topIssues[] = $unsignedCompetency . ' competency signature(s) pending';
        }
        if ($incompleteTraining > 0) {
            $topIssues[] = $incompleteTraining . ' training item(s) incomplete';
        }

        $status = $issueCount === 0 ? 'compliant' : ($overdueCount > 0 ? 'overdue' : 'attention');
        $trainingChecklistTab = $this->resolvePrimaryTrainingChecklistTab($employee, $empChecklistItems, $incompleteTraining);

        $trainingSummary = [
            'incomplete_orientation' => $incompleteOrientation,
            'unsigned_competency' => $unsignedCompetency,
            'incomplete_training' => $incompleteTraining,
            'training_checklist_tab' => $trainingChecklistTab,
        ];

        return [
            'employee_num' => $employee->employee_num,
            'name' => $employee->formalName(),
            'last_name' => (string) ($employee->last_name ?? ''),
            'first_name' => (string) ($employee->first_name ?? ''),
            'middle_name' => (string) ($employee->middle_name ?? ''),
            'position' => $employee->currentAssignment?->position?->title ?? '—',
            'department' => $employee->currentAssignment?->position?->department?->name ?? '—',
            'incomplete_orientation' => $incompleteOrientation,
            'unsigned_competency' => $unsignedCompetency,
            'incomplete_training' => $incompleteTraining,
            'training_checklist_tab' => $trainingChecklistTab,
            'overdue_count' => $overdueCount,
            'issue_count' => $issueCount,
            'status' => $status,
            'top_issues' => $topIssues,
            'manage_url' => $this->resolveEmployeeTeamReviewUrl($employee, $trainingSummary),
        ];
    }

    /**
     * Admin employee edit URL with optional main tab and checklist sub-tab (e.g. partE).
     */
    public function buildAdminEmployeeEditUrl(
        int|string $employeeId,
        ?string $tab = null,
        ?string $checklistTab = null,
        ?int $assessmentPeriodId = null,
    ): string {
        $url = route('admin.employees.edit', $employeeId);
        $query = [];

        if ($tab !== null && $tab !== '') {
            $query['tab'] = $tab;
        }

        if ($checklistTab !== null && $checklistTab !== '') {
            $query['checklist_tab'] = $checklistTab;
        }

        if ($assessmentPeriodId !== null) {
            $query['assessment_period_id'] = $assessmentPeriodId;
        }

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /**
     * Deep-link to the employee edit screen tab that matches the team's top issue.
     *
     * @param  array<string, mixed>  $trainingSummary  Keys from summarizeEmployeeTraining()
     */
    public function resolveEmployeeTeamReviewUrl(
        BPEmployee $employee,
        array $trainingSummary,
        int $missingDocs = 0,
        int $certRisk = 0,
        ?string $primaryIssueLabel = null
    ): string {
        $employeeId = (int) $employee->id;

        if ($this->issueLabelIndicatesOrientation($primaryIssueLabel)
            || ($trainingSummary['incomplete_orientation'] ?? 0) > 0
            || in_array('Orientation incomplete', $trainingSummary['top_issues'] ?? [], true)) {
            return $this->buildAdminEmployeeEditUrl($employeeId, 'checklist', 'partE');
        }

        if ($this->issueLabelIndicatesCompetency($primaryIssueLabel)
            || ($trainingSummary['unsigned_competency'] ?? 0) > 0) {
            return $this->buildAdminEmployeeEditUrl($employeeId, 'checklist', 'partG');
        }

        if ($this->issueLabelIndicatesTraining($primaryIssueLabel)
            || ($trainingSummary['incomplete_training'] ?? 0) > 0) {
            $part = (string) ($trainingSummary['training_checklist_tab'] ?? 'partB');

            return $this->buildAdminEmployeeEditUrl($employeeId, 'checklist', $part);
        }

        if ($this->issueLabelIndicatesDocuments($primaryIssueLabel) || $missingDocs > 0 || $certRisk > 0) {
            return $this->buildAdminEmployeeEditUrl($employeeId, 'documents');
        }

        return $this->buildAdminEmployeeEditUrl($employeeId, 'checklist');
    }

    protected function issueLabelIndicatesOrientation(?string $label): bool
    {
        return is_string($label) && str_contains($label, 'Orientation incomplete');
    }

    protected function issueLabelIndicatesCompetency(?string $label): bool
    {
        return is_string($label) && str_contains($label, 'competency signature');
    }

    protected function issueLabelIndicatesTraining(?string $label): bool
    {
        return is_string($label) && str_contains($label, 'training item');
    }

    protected function issueLabelIndicatesDocuments(?string $label): bool
    {
        if (!is_string($label)) {
            return false;
        }

        return str_contains($label, 'document gap') || str_contains($label, 'credential item');
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     */
    protected function resolvePrimaryTrainingChecklistTab(
        BPEmployee $employee,
        array $empChecklistItems,
        int $incompleteTraining
    ): string {
        if ($incompleteTraining <= 0) {
            return 'partB';
        }

        $requiredItems = $this->evaluateTrainingChecklistItems($employee, $empChecklistItems, '#');
        $firstIncomplete = collect($requiredItems)->first(
            fn ($item) => in_array($item['status'] ?? '', ['not_started', 'in_progress', 'overdue', 'pending_signature'], true)
        );

        return match ((string) ($firstIncomplete['section'] ?? 'PART B')) {
            'PART C' => 'partC',
            'PART D' => 'partD',
            default => 'partB',
        };
    }

    protected function resolveOrientationTrainingStatus(string $workflow, Carbon $today): array
    {
        return match ($workflow) {
            PartEOrientationChecklist::WORKFLOW_COMPLETED => $this->trainingStatusRow(
                'completed',
                'Completed',
                $today,
                null
            ),
            PartEOrientationChecklist::WORKFLOW_EMPLOYEE_SIGNATURE => $this->trainingStatusRow(
                'pending_signature',
                'Awaiting employee signature',
                $today,
                null
            ),
            PartEOrientationChecklist::WORKFLOW_REVIEWER_SIGNATURE => $this->trainingStatusRow(
                'in_progress',
                'Awaiting DSD / supervisor signature',
                $today,
                null
            ),
            default => $this->trainingStatusRow(
                'in_progress',
                'In progress (DSD / supervisor)',
                $today,
                null
            ),
        };
    }

    protected function resolveCompetencyTrainingStatus(EmployeeCompetencyAssessment $assessment, Carbon $today): array
    {
        $periodEnd = $this->parseDate($assessment->period?->date_to);
        $dueAt = $periodEnd?->toDateString();

        if ($assessment->employee_signed_at) {
            return $this->trainingStatusRow('completed', 'Signed & complete', $today, $dueAt);
        }

        $workflowStatus = AssessmentWorkflowStatus::normalize((string) ($assessment->status ?? ''));

        if ($workflowStatus === AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION) {
            return $this->trainingStatusRow('pending_signature', 'Signature required', $today, $dueAt);
        }

        if ($workflowStatus === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            return $this->trainingStatusRow('in_progress', 'Awaiting reviewer approval', $today, $dueAt);
        }

        $rawStatus = (string) ($assessment->status ?? '');

        if (in_array($rawStatus, ['submitted', 'completed'], true)) {
            if ($periodEnd && $periodEnd->lt($today)) {
                return $this->trainingStatusRow('overdue', 'Signature overdue', $today, $dueAt);
            }

            return $this->trainingStatusRow('pending_signature', 'Signature required', $today, $dueAt);
        }

        if ($rawStatus === 'draft') {
            if ($periodEnd && $periodEnd->lt($today)) {
                return $this->trainingStatusRow('overdue', 'Assessment overdue', $today, $dueAt);
            }

            return $this->trainingStatusRow('in_progress', 'Draft in progress', $today, $dueAt);
        }

        if ($periodEnd && $periodEnd->lt($today)) {
            return $this->trainingStatusRow('overdue', 'Period ended — not complete', $today, $dueAt);
        }

        return $this->trainingStatusRow('not_started', 'Not started', $today, $dueAt);
    }

    protected function resolvePerformanceChecklistStatus(EmployeePerformanceAssessment $assessment, Carbon $today): array
    {
        $periodEnd = $this->parseDate($assessment->period?->date_to);
        $dueAt = $periodEnd?->toDateString();
        $workflowStatus = AssessmentWorkflowStatus::normalize($assessment->workflowStatus());

        if ($workflowStatus === AssessmentWorkflowStatus::COMPLETED || $assessment->finalized) {
            return $this->trainingStatusRow('completed', 'Completed', $today, $dueAt);
        }

        if ($workflowStatus === AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION) {
            return $this->trainingStatusRow('pending_signature', 'Signature required', $today, $dueAt);
        }

        if ($workflowStatus === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            return $this->trainingStatusRow('in_progress', 'Awaiting reviewer approval', $today, $dueAt);
        }

        if ($periodEnd && $periodEnd->lt($today)) {
            return $this->trainingStatusRow('overdue', 'Period ended — not complete', $today, $dueAt);
        }

        if ($workflowStatus === AssessmentWorkflowStatus::DRAFT) {
            return $this->trainingStatusRow('in_progress', 'In progress', $today, $dueAt);
        }

        return $this->trainingStatusRow('not_started', 'Not started', $today, $dueAt);
    }

    /**
     * @return array<string, mixed>
     */
    protected function trainingStatusRow(
        string $status,
        string $statusLabel,
        Carbon $today,
        ?string $dueAt
    ): array {
        $badgeClass = match ($status) {
            'completed' => 'bg-emerald-50 text-emerald-700',
            'in_progress' => 'bg-sky-50 text-sky-700',
            'pending_signature' => 'bg-amber-50 text-amber-800',
            'overdue' => 'bg-rose-50 text-rose-700',
            default => 'bg-slate-100 text-slate-700',
        };

        $dueDate = $this->parseDate($dueAt);
        $daysUntil = $dueDate ? (int) $today->diffInDays($dueDate, false) : null;

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'due_at' => $dueAt,
            'due_at_formatted' => $dueDate?->format('M j, Y'),
            'days_until' => $daysUntil,
        ];
    }

    protected function trainingStatusPriority(string $status): int
    {
        return match ($status) {
            'overdue', 'rejected' => 0,
            'pending_signature' => 1,
            'not_started' => 2,
            'in_progress', 'submitted' => 3,
            'completed' => 4,
            default => 5,
        };
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return array<string, mixed>
     */
    protected function buildCertificationsCenter(
        User $user,
        ?BPEmployee $bpEmployee,
        array $empChecklistItems,
        ?Facility $facility
    ): array {
        $documentCompliance = $bpEmployee
            ? app(DocumentComplianceService::class)->forEmployee($bpEmployee)
            : null;

        $items = $bpEmployee
            ? $this->evaluateCertificationItems($bpEmployee, $empChecklistItems, $documentCompliance)
            : [];

        $relevantUploadTypeIds = collect($items)
            ->pluck('upload_type_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->flip()
            ->all();

        $uploads = $this->mapEmployeeDocuments($bpEmployee, $facility, $user);
        $expiringUploads = array_values(array_filter($uploads, function ($row) use ($relevantUploadTypeIds) {
            if (empty($row['expires_at'])) {
                return false;
            }

            $uploadTypeId = (int) ($row['upload_type_id'] ?? 0);

            return $uploadTypeId > 0 && isset($relevantUploadTypeIds[$uploadTypeId]);
        }));

        $summary = [
            'total' => count($items),
            'valid' => 0,
            'expiring' => 0,
            'expired' => 0,
            'missing' => 0,
        ];

        foreach ($items as $item) {
            match ($item['status'] ?? '') {
                'valid' => $summary['valid']++,
                'expired' => $summary['expired']++,
                'expiring_soon', 'expiring_urgent', 'expires_today' => $summary['expiring']++,
                default => $summary['missing']++,
            };
        }

        return [
            'items' => $items,
            'expiring_documents' => $expiringUploads,
            'expiring_uploads' => $expiringUploads,
            'summary' => $summary,
            'has_employee_record' => (bool) $bpEmployee,
            'position_id' => $documentCompliance['position_id'] ?? null,
            'position_title' => $documentCompliance['position_title'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @param  array<string, mixed>|null  $documentCompliance
     * @return list<array<string, mixed>>
     */
    protected function evaluateCertificationItems(
        BPEmployee $employee,
        array $empChecklistItems,
        ?array $documentCompliance = null
    ): array {
        $documentCompliance ??= app(DocumentComplianceService::class)->forEmployee($employee);

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $applicableItems = collect($documentCompliance['items'] ?? [])
            ->filter(fn ($item) => !empty($item['is_license_or_certification']))
            ->values();

        $today = Carbon::today();
        $rows = [];
        $seenUploadTypeIds = [];
        $seenChecklistItemIds = [];

        foreach ($applicableItems as $item) {
            $uploadTypeId = (int) ($item['upload_type_id'] ?? 0);
            if ($uploadTypeId > 0) {
                $seenUploadTypeIds[$uploadTypeId] = true;
            }

            $rows[] = array_merge(
                $this->certificationRowFromComplianceItem($item, $today),
                ['upload_type_id' => $uploadTypeId]
            );
        }

        if ($positionId) {
            $checklistItems = ChecklistItem::query()
                ->with('docType')
                ->applicableToPosition($positionId)
                ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
                ->orderBy('order')
                ->get();

            $syncedUploadTypesByChecklistId = UploadType::query()
                ->whereIn('checklist_item_id', $checklistItems->pluck('id'))
                ->pluck('id', 'checklist_item_id');

            foreach ($checklistItems as $item) {
                if (!$this->isLicenseOrCertificationItem($item)) {
                    continue;
                }

                $checklistItemId = (int) $item->id;
                $syncedUploadTypeId = (int) ($syncedUploadTypesByChecklistId[$checklistItemId] ?? 0);

                if ($syncedUploadTypeId > 0 && isset($seenUploadTypeIds[$syncedUploadTypeId])) {
                    $seenChecklistItemIds[$checklistItemId] = true;

                    continue;
                }

                if (isset($seenChecklistItemIds[$checklistItemId])) {
                    continue;
                }

                $seenChecklistItemIds[$checklistItemId] = true;
                if ($syncedUploadTypeId > 0) {
                    $seenUploadTypeIds[$syncedUploadTypeId] = true;
                }

                $stored = $empChecklistItems['item_' . $checklistItemId] ?? $empChecklistItems[$item->name] ?? null;
                $statusMeta = $this->resolveCertificationStatus($item, $stored, $today);

                $rows[] = array_merge([
                    'id' => 'cert-checklist-' . $checklistItemId,
                    'checklist_item_id' => $checklistItemId,
                    'upload_type_id' => $syncedUploadTypeId > 0 ? $syncedUploadTypeId : null,
                    'title' => (string) ($item->name ?? '—'),
                    'section' => (string) ($item->section ?? 'Employee file'),
                    'required' => true,
                    'doc_type' => $item->docType?->name ?? 'Checklist',
                    'on_file' => is_array($stored) && !empty($stored['on_file']),
                    'verified' => is_array($stored) && !empty($stored['verified_dt']),
                    'is_license_or_certification' => true,
                ], $statusMeta);
            }
        }

        usort($rows, function ($a, $b) {
            $priority = [
                'expired' => 0,
                'expires_today' => 1,
                'expiring_urgent' => 2,
                'expiring_soon' => 3,
                'missing_expiry' => 4,
                'not_on_file' => 5,
                'not_verified' => 6,
                'valid' => 7,
            ];

            $aRank = $priority[$a['status'] ?? ''] ?? 8;
            $bRank = $priority[$b['status'] ?? ''] ?? 8;

            if ($aRank !== $bRank) {
                return $aRank <=> $bRank;
            }

            $aDays = $a['days_until'] ?? PHP_INT_MAX;
            $bDays = $b['days_until'] ?? PHP_INT_MAX;

            return $aDays <=> $bDays;
        });

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function certificationRowFromComplianceItem(array $item, Carbon $today): array
    {
        $status = (string) ($item['status'] ?? 'missing');
        $latestExpiry = $this->parseDate($item['latest_expires_at'] ?? null);
        $daysUntil = isset($item['days_to_expiry']) ? (int) $item['days_to_expiry'] : null;

        if ($status === 'complete') {
            if (($item['requires_expiry'] ?? false) && $daysUntil !== null) {
                if ($daysUntil < 0) {
                    $statusMeta = $this->certificationStatusRow('expired', 'Expired', $latestExpiry?->toDateString(), $daysUntil, $latestExpiry?->format('M j, Y'));
                } elseif ($daysUntil === 0) {
                    $statusMeta = $this->certificationStatusRow('expires_today', 'Expires today', $latestExpiry?->toDateString(), $daysUntil, $latestExpiry?->format('M j, Y'));
                } elseif ($daysUntil <= 30) {
                    $statusMeta = $this->certificationStatusRow('expiring_urgent', "Expires in {$daysUntil} day(s)", $latestExpiry?->toDateString(), $daysUntil, $latestExpiry?->format('M j, Y'));
                } elseif ($daysUntil <= 60) {
                    $statusMeta = $this->certificationStatusRow('expiring_soon', "Expires in {$daysUntil} day(s)", $latestExpiry?->toDateString(), $daysUntil, $latestExpiry?->format('M j, Y'));
                } else {
                    $statusMeta = $this->certificationStatusRow('valid', 'Valid', $latestExpiry?->toDateString(), $daysUntil, $latestExpiry?->format('M j, Y'));
                }
            } else {
                $statusMeta = $this->certificationStatusRow('valid', 'On file', null, null);
            }
        } elseif ($status === 'expired') {
            $expiredDays = $latestExpiry ? (int) $today->diffInDays($latestExpiry, false) : null;
            $statusMeta = $this->certificationStatusRow('expired', 'Expired', $latestExpiry?->toDateString(), $expiredDays, $latestExpiry?->format('M j, Y'));
        } elseif ($status === 'pending_review') {
            $statusMeta = $this->certificationStatusRow('not_verified', 'Pending leadership review', null, null);
        } else {
            $statusMeta = $this->certificationStatusRow('not_on_file', 'Not on file', null, null);
        }

        return array_merge([
            'id' => 'cert-upload-type-' . (int) ($item['upload_type_id'] ?? 0),
            'checklist_item_id' => null,
            'title' => (string) ($item['name'] ?? '—'),
            'section' => 'Required for your position',
            'required' => true,
            'doc_type' => 'Required document',
            'on_file' => in_array($status, ['complete', 'pending_review', 'expired'], true),
            'verified' => $status === 'complete',
            'is_license_or_certification' => true,
        ], $statusMeta);
    }

    protected function isLicenseOrCertificationItem(ChecklistItem $item): bool
    {
        if ((bool) ($item->is_license_or_certification ?? false)) {
            return true;
        }

        $haystack = strtolower(trim(($item->name ?? '') . ' ' . ($item->docType?->name ?? '')));

        if ($haystack === '') {
            return false;
        }

        return preg_match('/license|licensure|certification|credential|cpr|bls|acls|rn\b|lpn\b|lvn\b/', $haystack) === 1;
    }

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array<string, mixed>
     */
    protected function resolveCertificationStatus(ChecklistItem $item, mixed $stored, Carbon $today): array
    {
        $onFile = is_array($stored) && !empty($stored['on_file']);
        $verified = is_array($stored) && !empty($stored['verified_dt']);

        if (!$onFile) {
            return $this->certificationStatusRow('not_on_file', 'Not on file', null, null);
        }

        if (!$verified) {
            return $this->certificationStatusRow('not_verified', 'Pending verification', null, null);
        }

        $expDtRaw = is_array($stored) ? ($stored['exp_dt'] ?? null) : null;
        $expNotRequired = is_array($stored) && !empty($stored['exp_dt_not_required']);

        if (empty($expDtRaw) && !$expNotRequired) {
            return $this->certificationStatusRow('missing_expiry', 'Expiry date required', null, null);
        }

        if ($expNotRequired || empty($expDtRaw)) {
            return $this->certificationStatusRow('valid', 'On file (no expiry tracked)', null, null);
        }

        $expDate = $this->parseDate($expDtRaw);
        if (!$expDate) {
            return $this->certificationStatusRow('missing_expiry', 'Invalid expiry date', null, null);
        }

        $daysUntil = (int) $today->diffInDays($expDate, false);
        $expFormatted = $expDate->format('M j, Y');

        if ($daysUntil < 0) {
            return $this->certificationStatusRow(
                'expired',
                'Expired ' . abs($daysUntil) . ' day(s) ago',
                $expDate->toDateString(),
                $daysUntil,
                $expFormatted
            );
        }

        if ($daysUntil === 0) {
            return $this->certificationStatusRow(
                'expires_today',
                'Expires today',
                $expDate->toDateString(),
                $daysUntil,
                $expFormatted
            );
        }

        if ($daysUntil <= 30) {
            return $this->certificationStatusRow(
                'expiring_urgent',
                "Expires in {$daysUntil} day(s)",
                $expDate->toDateString(),
                $daysUntil,
                $expFormatted
            );
        }

        if ($daysUntil <= 60) {
            return $this->certificationStatusRow(
                'expiring_soon',
                "Expires in {$daysUntil} day(s)",
                $expDate->toDateString(),
                $daysUntil,
                $expFormatted
            );
        }

        return $this->certificationStatusRow(
            'valid',
            'Valid',
            $expDate->toDateString(),
            $daysUntil,
            $expFormatted
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function certificationStatusRow(
        string $status,
        string $statusLabel,
        ?string $expDt,
        ?int $daysUntil,
        ?string $expDtFormatted = null
    ): array {
        $badgeClass = match ($status) {
            'expired' => 'bg-rose-50 text-rose-700',
            'expires_today', 'expiring_urgent' => 'bg-rose-50 text-rose-700',
            'expiring_soon' => 'bg-amber-50 text-amber-700',
            'valid' => 'bg-emerald-50 text-emerald-700',
            'missing_expiry' => 'bg-amber-50 text-amber-700',
            default => 'bg-slate-100 text-slate-700',
        };

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'exp_dt' => $expDt,
            'exp_dt_formatted' => $expDtFormatted ?? ($expDt ? Carbon::parse($expDt)->format('M j, Y') : null),
            'days_until' => $daysUntil,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildFacilityCertificationsReport(User $user): ?array
    {
        if (!$user->hasRole(['facility-admin', 'facility-dsd'])) {
            return null;
        }

        $facility = $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        if (!$facility) {
            $bpEmployee = $user->resolvedBpEmployee(['currentAssignment.facility']);
            $facility = $bpEmployee?->currentAssignment?->facility;
        }

        if (!$facility) {
            return null;
        }

        $facilityKey = $facility->slug ?? $facility->id;

        $employees = BPEmployee::query()
            ->with(['currentAssignment.position'])
            ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facility->id))
            ->orderedByName()
            ->get();

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $employees->pluck('employee_num')->filter()->all())
            ->get()
            ->keyBy('employee_num');

        $rows = [];
        $employeesWithIssues = 0;
        $totalExpiring = 0;
        $totalExpired = 0;
        $totalMissing = 0;

        foreach ($employees as $employee) {
            $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $certItems = $this->evaluateCertificationItems($employee, is_array($items) ? $items : []);

            $expiring = 0;
            $expired = 0;
            $missing = 0;
            $topIssues = [];

            foreach ($certItems as $cert) {
                $status = $cert['status'] ?? '';
                if (in_array($status, ['expiring_soon', 'expiring_urgent', 'expires_today'], true)) {
                    $expiring++;
                } elseif ($status === 'expired') {
                    $expired++;
                } elseif (!in_array($status, ['valid'], true)) {
                    $missing++;
                }

                if (count($topIssues) < 3 && !in_array($status, ['valid'], true)) {
                    $topIssues[] = $cert['title'] ?? '—';
                }
            }

            $issueCount = $expiring + $expired + $missing;
            if ($issueCount > 0) {
                $employeesWithIssues++;
            }

            $totalExpiring += $expiring;
            $totalExpired += $expired;
            $totalMissing += $missing;

            $rows[] = array_merge($employee->tableNameFields(), [
                'employee_num' => $employee->employee_num,
                'position' => $employee->currentAssignment?->position?->title ?? '—',
                'expiring_count' => $expiring,
                'expired_count' => $expired,
                'missing_count' => $missing,
                'issue_count' => $issueCount,
                'top_issues' => $topIssues,
                'manage_url' => route('admin.employees.edit', $employee->id) . '?tab=checklist',
            ]);
        }

        $rows = BPEmployee::sortTableRowsByName($rows);

        return [
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'key' => $facilityKey,
            ],
            'summary' => [
                'total_employees' => $employees->count(),
                'employees_with_issues' => $employeesWithIssues,
                'total_expiring' => $totalExpiring,
                'total_expired' => $totalExpired,
                'total_missing' => $totalMissing,
            ],
            'employees' => $rows,
            'employees_list_url' => route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id,
        ];
    }

    protected function hasActivePreEmployment(?JobApplication $jobApplication): bool
    {
        if (!$jobApplication) {
            return false;
        }

        return in_array($jobApplication->status, ['pre-employment', 'pending', 'submitted'], true);
    }

    protected function buildPreEmploymentStats(Collection $items): ?array
    {
        if ($items->isEmpty()) {
            return null;
        }

        $total = $items->count();
        $completed = $items->where('status', 'completed')->count();
        $submitted = $items->where('status', 'submitted')->count();
        $draft = $items->where('status', 'draft')->count();
        $returned = $items->where('status', 'returned')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'submitted' => $submitted,
            'draft' => $draft,
            'returned' => $returned,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return array{missing: list<array<string, mixed>>, complete: list<array<string, mixed>>, verified_percent: ?int, total_applicable: int, verified_count: int}
     */
    protected function evaluateEmployeeChecklistCompliance(?BPEmployee $employee, array $empChecklistItems): array
    {
        if (!$employee) {
            return [
                'missing' => [],
                'complete' => [],
                'verified_percent' => null,
                'total_applicable' => 0,
                'verified_count' => 0,
            ];
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $applicableItems = ChecklistItem::query()
            ->applicableToPosition($positionId)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $missing = [];
        $complete = [];
        $verifiedCount = 0;

        foreach ($applicableItems as $item) {
            $key = 'item_' . $item->id;
            $stored = $empChecklistItems[$key] ?? $empChecklistItems[$item->name] ?? null;

            if (is_array($stored) && !empty($stored['verified_dt'])) {
                $verifiedCount++;
            }

            $issue = $this->resolveChecklistItemIssue($item, $stored);
            if ($issue !== null) {
                $idPrefix = ($issue['status'] ?? '') === 'expiry_missing' ? 'doc-exp-' : 'doc-';
                $missing[] = array_merge([
                    'id' => $idPrefix . $item->id,
                    'title' => $item->name,
                    'section' => $item->section,
                ], $issue);
                continue;
            }

            $onFile = is_array($stored) && !empty($stored['on_file']);
            $verified = is_array($stored) && !empty($stored['verified_dt']);
            if ($onFile && $verified) {
                $complete[] = [
                    'id' => 'doc-ok-' . $item->id,
                    'title' => $item->name,
                    'section' => $item->section,
                    'status' => 'complete',
                    'status_label' => 'On file & verified',
                ];
            }
        }

        $total = $applicableItems->count();

        return [
            'missing' => $missing,
            'complete' => $complete,
            'verified_percent' => $total > 0 ? (int) round(($verifiedCount / $total) * 100) : null,
            'total_applicable' => $total,
            'verified_count' => $verifiedCount,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array<string, mixed>|null
     */
    protected function resolveChecklistItemIssue(ChecklistItem $item, mixed $stored): ?array
    {
        $onFile = is_array($stored) && !empty($stored['on_file']);
        $verified = is_array($stored) && !empty($stored['verified_dt']);

        if (!$onFile || !$verified) {
            return [
                'status' => !$onFile ? 'not_on_file' : 'not_verified',
                'status_label' => !$onFile ? 'Not on file' : 'Pending verification',
                'priority' => 'high',
                'due_at' => null,
            ];
        }

        if ($item->isExpiring && is_array($stored)) {
            $expDt = $stored['exp_dt'] ?? null;
            if (empty($expDt) && empty($stored['exp_dt_not_required'])) {
                return [
                    'status' => 'expiry_missing',
                    'status_label' => 'Expiry date required',
                    'priority' => 'medium',
                    'due_at' => null,
                ];
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $documentComplianceItems
     * @return list<array<string, mixed>>
     */
    protected function buildEmployeeUploadTypeOptions(?BPEmployee $employee, array $documentComplianceItems): array
    {
        return UploadType::optionsForEmployee($employee, $documentComplianceItems);
    }

    /**
     * Count employee documents and checklist items expiring within the given window (default 60 days).
     *
     * @param  iterable<int, array<string, mixed>>  $complianceItems
     */
    protected function countDocumentsExpiringWithinDays(
        ?BPEmployee $employee,
        array $empChecklistItems,
        iterable $complianceItems,
        int $withinDays = 60
    ): int {
        if (!$employee) {
            return 0;
        }

        $today = Carbon::today();
        $through = $today->copy()->addDays($withinDays);
        $total = 0;
        $countedUploadIds = [];

        foreach (collect($complianceItems) as $item) {
            $daysToExpiry = $item['days_to_expiry'] ?? null;
            if (($item['status'] ?? '') !== 'complete' || $daysToExpiry === null) {
                continue;
            }
            if ($daysToExpiry < 0 || $daysToExpiry > $withinDays) {
                continue;
            }
            $total++;
            if (!empty($item['valid_upload_id'])) {
                $countedUploadIds[(int) $item['valid_upload_id']] = true;
            }
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        if ($positionId) {
            $checklistItems = ChecklistItem::query()
                ->applicableToPosition($positionId)
                ->where('isExpiring', true)
                ->get();

            foreach ($checklistItems as $item) {
                if (UploadType::query()->where('checklist_item_id', $item->id)->exists()) {
                    continue;
                }

                $stored = $empChecklistItems['item_' . $item->id] ?? $empChecklistItems[$item->name] ?? null;
                if (!is_array($stored) || empty($stored['on_file']) || empty($stored['exp_dt']) || !empty($stored['exp_dt_not_required'])) {
                    continue;
                }

                try {
                    $expDate = Carbon::parse($stored['exp_dt'])->startOfDay();
                } catch (\Throwable) {
                    continue;
                }

                if ($expDate->lt($today) || $expDate->gt($through)) {
                    continue;
                }

                $total++;
            }
        }

        $requiredTypeIds = collect($complianceItems)->pluck('upload_type_id')->filter()->values()->all();
        $uncountedUploadQuery = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', $today)
            ->whereDate('expires_at', '<=', $through);

        if ($countedUploadIds !== []) {
            $uncountedUploadQuery->whereNotIn('id', array_keys($countedUploadIds));
        }
        if ($requiredTypeIds !== []) {
            $uncountedUploadQuery->whereNotIn('upload_type_id', $requiredTypeIds);
        }

        $total += (int) $uncountedUploadQuery->count();

        return $total;
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @param  array<string, mixed>|null  $documentCompliance
     * @return array<string, mixed>
     */
    public function buildDocumentsCenter(
        User $user,
        ?BPEmployee $bpEmployee,
        array $empChecklistItems,
        ?array $documentCompliance = null,
        bool $skipDocumentsList = false
    ): array {
        $documentCompliance ??= [
            'items' => collect(),
            'summary' => [
                'total' => 0,
                'complete' => 0,
                'expired' => 0,
                'missing' => 0,
            ],
        ];

        $complianceItems = collect($documentCompliance['items'] ?? []);
        $checklistEvaluation = $this->evaluateEmployeeChecklistCompliance($bpEmployee, $empChecklistItems);
        $requiredUploadTypes = $this->buildEmployeeUploadTypeOptions($bpEmployee, $complianceItems->all());

        $complianceMissing = $complianceItems
            ->filter(fn ($item) => in_array(($item['status'] ?? ''), ['missing', 'expired', 'pending_review'], true))
            ->map(function ($item) {
                $status = (string) ($item['status'] ?? 'missing');
                $statusLabel = match ($status) {
                    'expired' => 'Expired',
                    'pending_review' => 'Pending leadership review',
                    default => 'Not on file',
                };

                return [
                    'id' => 'upload-type-' . ($item['upload_type_id'] ?? uniqid()),
                    'upload_type_id' => $item['upload_type_id'] ?? null,
                    'checklist_item_id' => null,
                    'title' => $item['name'] ?? 'Required document',
                    'section' => 'Required for your position',
                    'required' => true,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'priority' => in_array($status, ['expired', 'pending_review'], true) ? 'medium' : 'high',
                    'due_at' => null,
                ];
            })
            ->values();

        foreach ($checklistEvaluation['missing'] as $item) {
            $checklistItemId = null;
            if (preg_match('/^doc(?:-exp)?-(\d+)$/', (string) ($item['id'] ?? ''), $matches)) {
                $checklistItemId = (int) $matches[1];
            }

            $uploadTypeId = $checklistItemId
                ? UploadType::query()->where('checklist_item_id', $checklistItemId)->value('id')
                : null;

            $complianceMissing->push([
                'id' => $item['id'] ?? ('checklist-' . ($checklistItemId ?? uniqid())),
                'upload_type_id' => $uploadTypeId,
                'checklist_item_id' => $checklistItemId,
                'title' => $item['title'] ?? 'Checklist document',
                'section' => $item['section'] ?? 'Employee checklist',
                'required' => true,
                'status' => $item['status'] ?? 'not_on_file',
                'status_label' => $item['status_label'] ?? 'Needs attention',
                'priority' => $item['priority'] ?? 'high',
                'due_at' => $item['due_at'] ?? null,
            ]);
        }

        $complianceMissing = $complianceMissing->values()->all();

        $requiredNotOnFileCount = collect($complianceMissing)
            ->filter(fn ($item) => in_array($item['status'] ?? '', ['missing', 'not_on_file'], true))
            ->count();

        $expiringIn60DaysCount = $this->countDocumentsExpiringWithinDays(
            $bpEmployee,
            $empChecklistItems,
            $complianceItems
        );

        $complianceComplete = $complianceItems
            ->filter(fn ($item) => ($item['status'] ?? '') === 'complete')
            ->map(function ($item) {
                return [
                    'id' => 'upload-type-ok-' . ($item['upload_type_id'] ?? uniqid()),
                    'title' => $item['name'] ?? 'Required document',
                    'section' => 'Required for your position',
                    'required' => true,
                    'status' => 'complete',
                    'status_label' => 'Approved',
                ];
            })
            ->values()
            ->all();

        $summary = $documentCompliance['summary'] ?? [];
        $totalRequired = (int) ($summary['total'] ?? count($complianceItems));
        $completeRequired = (int) ($summary['complete'] ?? count($complianceComplete));
        $checklistTotal = (int) ($checklistEvaluation['total_applicable'] ?? 0);
        $checklistVerified = (int) ($checklistEvaluation['verified_count'] ?? 0);
        $combinedTotal = $totalRequired + $checklistTotal;
        $combinedComplete = $completeRequired + $checklistVerified;
        $verifiedPercent = $combinedTotal > 0
            ? (int) round(($combinedComplete / $combinedTotal) * 100)
            : ($checklistEvaluation['verified_percent'] ?? null);

        $facility = $bpEmployee?->currentAssignment?->facility ?? $user->facility;

        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        $documentsList = $skipDocumentsList
            ? []
            : $this->mapEmployeeDocuments($bpEmployee, $facility, $user);

        return [
            'uploads' => $documentsList,
            'documents' => $documentsList,
            'compliance_missing' => $complianceMissing,
            'compliance_complete' => $complianceComplete,
            'required_not_on_file_count' => $requiredNotOnFileCount,
            'expiring_in_60_days_count' => $expiringIn60DaysCount,
            'required_upload_types' => $requiredUploadTypes,
            'signatures' => $this->buildSignaturesNeeded($bpEmployee, $empChecklistItems),
            'verified_percent' => $verifiedPercent,
            'has_employee_record' => (bool) $bpEmployee,
            'submission_reason_options' => \App\Support\UploadSubmissionReason::options(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildFacilityComplianceReport(User $user): ?array
    {
        if (!$user->hasRole(['facility-admin', 'facility-dsd'])) {
            return null;
        }

        $facility = $user->facility;
        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        if (!$facility) {
            $bpEmployee = $user->resolvedBpEmployee(['currentAssignment.facility']);
            $facility = $bpEmployee?->currentAssignment?->facility;
        }

        if (!$facility) {
            return null;
        }

        $facilityKey = $facility->slug ?? $facility->id;

        $employees = BPEmployee::query()
            ->with(['currentAssignment.position'])
            ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facility->id))
            ->orderedByName()
            ->get();

        $checklistsByNum = BPEmpChecklist::query()
            ->whereIn('employee_num', $employees->pluck('employee_num')->filter()->all())
            ->get()
            ->keyBy('employee_num');

        $rows = [];
        $totalMissing = 0;
        $employeesWithGaps = 0;
        $compliancePercents = [];

        foreach ($employees as $employee) {
            $items = $checklistsByNum->get($employee->employee_num)?->items ?? [];
            $evaluation = $this->evaluateEmployeeChecklistCompliance($employee, is_array($items) ? $items : []);
            $missing = $evaluation['missing'];
            $missingCount = count($missing);

            if ($missingCount > 0) {
                $employeesWithGaps++;
            }

            $totalMissing += $missingCount;

            if ($evaluation['verified_percent'] !== null) {
                $compliancePercents[] = $evaluation['verified_percent'];
            }

            $rows[] = array_merge($employee->tableNameFields(), [
                'employee_num' => $employee->employee_num,
                'position' => $employee->currentAssignment?->position?->title ?? '—',
                'missing_count' => $missingCount,
                'top_missing' => array_slice(array_column($missing, 'title'), 0, 3),
                'verified_percent' => $evaluation['verified_percent'],
                'manage_url' => route('admin.employees.edit', $employee->id) . '?tab=checklist',
            ]);
        }

        $rows = BPEmployee::sortTableRowsByName($rows);

        $avgCompliance = count($compliancePercents) > 0
            ? (int) round(array_sum($compliancePercents) / count($compliancePercents))
            : null;

        return [
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'key' => $facilityKey,
            ],
            'summary' => [
                'total_employees' => $employees->count(),
                'employees_with_gaps' => $employeesWithGaps,
                'total_missing_items' => $totalMissing,
                'average_compliance_percent' => $avgCompliance,
            ],
            'employees' => $rows,
            'employees_list_url' => route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function mapEmployeeDocuments(?BPEmployee $employee, ?Facility $facility, User $user): array
    {
        if (!$employee) {
            return [];
        }

        return $employee->uploads()
            ->with(['uploadType', 'checklistItem'])
            ->orderByDesc('uploaded_at')
            ->get()
            ->map(fn (Upload $upload) => $this->formatUploadRow($upload, $employee, $facility, $user))
            ->values()
            ->all();
    }

    /**
     * @param  array{search?: string, type?: string, expiry?: string, sort?: string, per_page?: int}  $filters
     */
    public function paginateEmployeeDocuments(
        ?BPEmployee $employee,
        ?Facility $facility,
        User $user,
        array $filters = []
    ): LengthAwarePaginator {
        $filters = array_merge($this->documentFiltersFromRequest(null), $filters);

        if (!$employee) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $filters['per_page'], 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        $query = $this->employeeDocumentsQuery($employee, $filters);

        return $query
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(fn (Upload $upload) => $this->formatUploadRow($upload, $employee, $facility, $user));
    }

    /**
     * @param  array{search?: string, type?: string, expiry?: string, sort?: string}  $filters
     */
    protected function employeeDocumentsQuery(BPEmployee $employee, array $filters): HasMany
    {
        $today = Carbon::today()->toDateString();
        $search = trim((string) ($filters['search'] ?? ''));

        $query = $employee->uploads()
            ->with(['uploadType', 'checklistItem']);

        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function ($scope) use ($like) {
                $scope->where('original_filename', 'like', $like)
                    ->orWhere('comments', 'like', $like)
                    ->orWhereHas('uploadType', fn ($typeQuery) => $typeQuery->where('name', 'like', $like))
                    ->orWhereHas('checklistItem', fn ($itemQuery) => $itemQuery->where('name', 'like', $like));
            });
        }

        $type = (string) ($filters['type'] ?? '');
        if ($type !== '') {
            if (str_starts_with($type, 'checklist-')) {
                $query->where('checklist_item_id', (int) substr($type, strlen('checklist-')));
            } else {
                $query->where('upload_type_id', (int) $type);
            }
        }

        match ((string) ($filters['expiry'] ?? '')) {
            'valid' => $query->where(function ($scope) use ($today) {
                $scope->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today);
            }),
            'expired' => $query->whereNotNull('expires_at')->whereDate('expires_at', '<', $today),
            'none' => $query->whereNull('expires_at'),
            'expiring' => $query->whereNotNull('expires_at')
                ->whereDate('expires_at', '>=', $today)
                ->whereDate('expires_at', '<=', Carbon::today()->addDays(30)->toDateString()),
            default => null,
        };

        match ((string) ($filters['sort'] ?? 'uploaded_desc')) {
            'uploaded_asc' => $query->orderBy('uploaded_at')->orderBy('id'),
            'name_asc' => $query->orderBy('original_filename')->orderByDesc('uploaded_at'),
            'name_desc' => $query->orderByDesc('original_filename')->orderByDesc('uploaded_at'),
            'expires_asc' => $query->orderByRaw('expires_at IS NULL')->orderBy('expires_at')->orderByDesc('uploaded_at'),
            'expires_desc' => $query->orderByRaw('expires_at IS NULL DESC')->orderByDesc('expires_at')->orderByDesc('uploaded_at'),
            default => $query->orderByDesc('uploaded_at')->orderByDesc('id'),
        };

        return $query;
    }

    /**
     * @return array{search: string, type: string, expiry: string, sort: string, per_page: int}
     */
    protected function documentFiltersFromRequest(?Request $request): array
    {
        $allowedSorts = [
            'uploaded_desc',
            'uploaded_asc',
            'name_asc',
            'name_desc',
            'expires_asc',
            'expires_desc',
        ];

        $allowedExpiry = ['', 'valid', 'expired', 'none', 'expiring'];
        $sort = (string) ($request?->query('sort') ?? 'uploaded_desc');
        $expiry = (string) ($request?->query('expiry') ?? '');

        return [
            'search' => trim((string) ($request?->query('q') ?? '')),
            'type' => (string) ($request?->query('type') ?? ''),
            'expiry' => in_array($expiry, $allowedExpiry, true) ? $expiry : '',
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'uploaded_desc',
            'per_page' => min(50, max(5, (int) ($request?->query('per_page') ?? 10))),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function documentTypeFilterOptions(?BPEmployee $employee): array
    {
        if (!$employee) {
            return [];
        }

        return Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->with(['uploadType:id,name', 'checklistItem:id,name'])
            ->get()
            ->map(function (Upload $upload) {
                if ($upload->upload_type_id) {
                    return [
                        'value' => (string) $upload->upload_type_id,
                        'label' => $upload->checklistItem?->name
                            ?? $upload->uploadType?->name
                            ?? 'Document',
                    ];
                }

                if ($upload->checklist_item_id) {
                    return [
                        'value' => 'checklist-' . $upload->checklist_item_id,
                        'label' => $upload->checklistItem?->name ?? 'Checklist document',
                    ];
                }

                return null;
            })
            ->filter()
            ->unique('value')
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatUploadRow(
        Upload $upload,
        BPEmployee $employee,
        ?Facility $facility,
        User $user
    ): array {
        $canUseAdminUploadRoutes = $facility
            && method_exists($user, 'canManageFacility')
            && $user->canManageFacility($facility->id);

        $facilityKey = $facility ? ($facility->slug ?? $facility->id) : null;
        $uploadedAt = $this->parseDate($upload->uploaded_at);
        $expiresAt = $this->parseDate($upload->expires_at);
        $expiryStatus = 'none';

        if ($expiresAt) {
            $expiryStatus = $expiresAt->copy()->startOfDay()->lt(Carbon::today()) ? 'expired' : 'valid';
            if ($expiryStatus === 'valid' && $expiresAt->copy()->startOfDay()->lte(Carbon::today()->addDays(30))) {
                $expiryStatus = 'expiring';
            }
        }

        $needTracking = (bool) (
            $upload->uploadType?->requires_expiry
            || $upload->checklistItem?->isExpiring
        );

        $row = [
            'id' => $upload->id,
            'upload_type_id' => $upload->upload_type_id,
            'name' => $upload->original_filename ?: basename((string) $upload->file_path),
            'type' => $upload->checklistItem?->name
                ?? $upload->uploadType?->name
                ?? 'Document',
            'is_license_or_certification' => (bool) ($upload->uploadType?->is_license_or_certification ?? false),
            'verification_status' => $upload->verification_status,
            'verification_status_label' => $upload->verificationStatusLabel() ?? 'Not submitted',
            'verification_badge_class' => match ($upload->verification_status) {
                Upload::VERIFICATION_APPROVED => 'bg-emerald-50 text-emerald-700',
                Upload::VERIFICATION_PENDING => 'bg-amber-50 text-amber-700',
                Upload::VERIFICATION_REJECTED => 'bg-rose-50 text-rose-700',
                default => 'bg-slate-100 text-slate-600',
            },
            'verification_notes' => $upload->verification_notes,
            'can_submit_for_review' => $upload->isOwnedBy($user) && $upload->canSubmitForVerification(),
            'is_owned_by_user' => $upload->isOwnedBy($user),
            'notify_preview_url' => null,
            'notify_send_url' => null,
            'uploaded_at' => $uploadedAt?->format('M j, Y'),
            'uploaded_at_sort' => $uploadedAt?->toDateString(),
            'expires_at' => $expiresAt?->format('M j, Y'),
            'expiration_date' => $expiresAt?->format('M j, Y'),
            'expires_at_sort' => $expiresAt?->toDateString(),
            'expiry_status' => $expiryStatus,
            'need_tracking' => $needTracking,
            'need_tracking_label' => $needTracking ? 'Yes' : 'No',
            'view_url' => null,
            'download_url' => null,
            'edit_url' => null,
        ];

        if ($canUseAdminUploadRoutes && $facilityKey) {
            $row['view_url'] = route('admin.facility.uploads.view', [
                'facility' => $facilityKey,
                'upload' => $upload->id,
            ]);
            $row['download_url'] = route('admin.facility.uploads.download', [
                'facility' => $facilityKey,
                'upload' => $upload->id,
            ]);
            $row['edit_url'] = $this->buildAdminEmployeeEditUrl($employee->id, 'documents');
        } else {
            $row['view_url'] = route('employment.documents.view', ['document' => $upload->id]);
            $row['download_url'] = route('employment.documents.download', ['document' => $upload->id]);
            $row['edit_url'] = route('employment.portal', ['tab' => 'documents']) . '#upload-table';
            if ($upload->isOwnedBy($user)) {
                $row['notify_preview_url'] = route('employment.documents.notify.preview', ['document' => $upload->id]);
                $row['notify_send_url'] = route('employment.documents.notify', ['document' => $upload->id]);
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return list<array<string, mixed>>
     */
    protected function buildSignaturesNeeded(?BPEmployee $employee, array $empChecklistItems): array
    {
        $needed = [];

        if ($employee) {
            foreach ($empChecklistItems as $storageKey => $payload) {
                if (!is_string($storageKey) || !str_starts_with($storageKey, 'part_e_orientation_summary_')) {
                    continue;
                }
                if (!is_array($payload)) {
                    continue;
                }

                $status = (string) ($payload['workflow_status'] ?? '');
                if ($status === PartEOrientationChecklist::WORKFLOW_EMPLOYEE_SIGNATURE) {
                    $needed[] = [
                        'id' => 'sig-orientation-' . $storageKey,
                        'title' => 'Orientation checklist',
                        'description' => 'Your signature is required on the orientation checklist.',
                        'type' => 'orientation',
                        'priority' => 'high',
                        'due_at' => null,
                    ];
                }
            }
        }

        return $needed;
    }

    /**
     * Dashboard tasks for employees who must confirm or re-confirm an assessment.
     *
     * @return list<array<string, mixed>>
     */
    public function pendingEmployeeAssessmentConfirmationTodos(User $user, ?BPEmployee $employee = null): array
    {
        return $this->buildEmployeeAssessmentConfirmationTodos($user, $employee);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function pendingReviewerAssessmentTasks(User $user): array
    {
        $reviewerTodos = [];

        foreach ($this->buildReviewerAssessmentTodos($user) as $reviewTodo) {
            $reviewerTodos[] = $this->todoItem(
                $reviewTodo['id'],
                $reviewTodo['title'],
                $reviewTodo['description'],
                'assessment-review',
                $reviewTodo['priority'],
                $reviewTodo['action_url'] ?? route('employment.portal'),
                false
            );
        }

        return $reviewerTodos;
    }

    /**
     * @return list<string>
     */
    public function resolveEmployeeNumbersForUser(User $user, ?BPEmployee $employee = null): array
    {
        $employeeNums = [];

        if ($employee && filled($employee->employee_num)) {
            $employeeNums[] = (string) $employee->employee_num;
        }

        if (User::bpEmployeesTableHasUserId()) {
            $employeeNums = array_merge(
                $employeeNums,
                BPEmployee::query()
                    ->where('user_id', $user->id)
                    ->pluck('employee_num')
                    ->map(fn ($num) => (string) $num)
                    ->all()
            );
        }

        if (filled($user->email)) {
            $employeeNums = array_merge(
                $employeeNums,
                BPEmployee::query()
                    ->where('email', $user->email)
                    ->pluck('employee_num')
                    ->map(fn ($num) => (string) $num)
                    ->all()
            );
        }

        return array_values(array_unique(array_filter($employeeNums)));
    }

    /**
     * Dashboard tasks for employees who must confirm or re-confirm an assessment.
     *
     * @return list<array<string, mixed>>
     */
    protected function buildEmployeeAssessmentConfirmationTodos(User $user, ?BPEmployee $employee = null): array
    {
        $employeeNums = $this->resolveEmployeeNumbersForUser($user, $employee);
        if ($employeeNums === []) {
            return [];
        }

        $employeesByNum = BPEmployee::query()
            ->whereIn('employee_num', $employeeNums)
            ->get()
            ->keyBy(fn (BPEmployee $row) => (string) $row->employee_num);

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        $competencyAssessments = EmployeeCompetencyAssessment::query()
            ->whereIn('employee_num', $employeeNums)
            ->with('period')
            ->latest('updated_at')
            ->get();

        foreach ($competencyAssessments as $assessment) {
            $sectionWorkflow->syncSubmittedSectionsWithoutWorkflow($assessment);
        }

        $competencyAssessments->each->refresh();

        $todos = [];

        foreach ($sectionWorkflow->pendingEmployeeConfirmationItems($competencyAssessments) as $item) {
            $assessment = $item['assessment'];
            $sectionLabel = $item['section'];

            $assessmentEmployee = $employeesByNum->get((string) $assessment->employee_num)
                ?? $employee
                ?? $employeesByNum->first();

            if (! $assessmentEmployee) {
                continue;
            }

            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $sectionState = $sectionWorkflow->sectionWorkflow($assessment, $sectionLabel);
            $isResubmit = filled($sectionState['returned_at'] ?? null)
                || (
                    filled($sectionState['submitted_at'] ?? null)
                    && filled($sectionState['employee_signed_at'] ?? null)
                );

            $todos[] = array_merge($this->todoItem(
                'confirm-competency-' . $assessment->id . '-' . md5($sectionLabel),
                $isResubmit
                    ? 'Sign: Review updated ' . $sectionLabel
                    : 'Sign: Confirm ' . $sectionLabel,
                $isResubmit
                    ? ('Your reviewer updated ' . $sectionLabel . ($periodLabel ? " ({$periodLabel})" : '') . '. Review and sign again.')
                    : ('Review and sign ' . $sectionLabel . ($periodLabel ? " ({$periodLabel})" : '') . '.'),
                'competency-confirmation',
                'high',
                $sectionWorkflow->buildSectionChecklistUrl(
                    $assessmentEmployee,
                    (int) $assessment->assessment_period_id,
                    $sectionLabel,
                ),
                false
            ), ['action' => 'sign']);
        }

        $notificationService = app(AssessmentConfirmationNotificationService::class);

        $performancePending = EmployeePerformanceAssessment::query()
            ->whereIn('employee_num', $employeeNums)
            ->whereIn('status', [
                AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                'for_employee_signature',
            ])
            ->whereNull('acknowledge_dt')
            ->with('period')
            ->latest('updated_at')
            ->get();

        foreach ($performancePending as $assessment) {
            $assessmentEmployee = $employeesByNum->get((string) $assessment->employee_num)
                ?? $employee
                ?? $employeesByNum->first();

            if (! $assessmentEmployee) {
                continue;
            }

            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $isResubmit = filled($assessment->review_dt)
                && (
                    filled(trim((string) ($assessment->employee_comments ?? '')))
                    || $assessment->updated_at?->gt($assessment->review_dt)
                );

            $todos[] = array_merge($this->todoItem(
                'confirm-performance-' . $assessment->id,
                $isResubmit ? 'Sign: Review updated performance appraisal' : 'Sign: Confirm performance appraisal',
                $isResubmit
                    ? ('Your reviewer updated your performance appraisal' . ($periodLabel ? " ({$periodLabel})" : '') . '. Review and sign again.')
                    : ('Review and acknowledge your performance appraisal' . ($periodLabel ? " ({$periodLabel})" : '') . '.'),
                'performance-confirmation',
                'high',
                $notificationService->buildEmployeeChecklistUrl(
                    $assessmentEmployee,
                    'partF',
                    (int) $assessment->assessment_period_id
                ),
                false
            ), ['action' => 'sign']);
        }

        return $todos;
    }

    protected function competencyAssessmentTaskLabel(EmployeeCompetencyAssessment $assessment): string
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $sections = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '')
            ->unique()
            ->values();

        if ($sections->count() === 1) {
            return $sections->first();
        }

        return 'competency assessment';
    }

    /**
     * Tasks for reviewers who must update or approve assessments they submitted.
     *
     * @return list<array<string, mixed>>
     */
    protected function buildReviewerAssessmentTodos(User $user): array
    {
        $todos = [];
        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        $competencyAssessments = EmployeeCompetencyAssessment::query()
            ->where('submitted_by', $user->id)
            ->with('period')
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $employeesByNum = BPEmployee::query()
            ->whereIn('employee_num', $competencyAssessments->pluck('employee_num')->filter()->unique()->all())
            ->get()
            ->keyBy('employee_num');

        foreach ($competencyAssessments as $assessment) {
            $sectionWorkflow->syncSubmittedSectionsWithoutWorkflow($assessment);
        }

        $competencyAssessments->each->refresh();

        foreach ($sectionWorkflow->pendingReviewerApprovalItems($competencyAssessments, $user->id) as $item) {
            $assessment = $item['assessment'];
            $sectionLabel = $item['section'];
            $employee = $employeesByNum->get($assessment->employee_num);

            if (! $employee) {
                continue;
            }

            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $employeeLabel = $employee->formalName();

            $todos[] = [
                'id' => 'review-competency-approve-' . $assessment->id . '-' . md5($sectionLabel),
                'title' => 'Approve: ' . $sectionLabel,
                'description' => $employeeLabel . ' signed ' . $sectionLabel
                    . ($periodLabel ? " ({$periodLabel})" : '')
                    . '. Review and sign to complete this section.',
                'type' => 'competency-review',
                'priority' => 'high',
                'due_at' => $this->parseDate($assessment->period?->date_to)?->format('Y-m-d'),
                'action_url' => $sectionWorkflow->buildReviewerSectionChecklistUrl(
                    $employee,
                    (int) $assessment->assessment_period_id,
                    $sectionLabel,
                ),
            ];
        }

        foreach ($sectionWorkflow->sectionsReturnedToReviewerItems($competencyAssessments, $user->id) as $item) {
            $assessment = $item['assessment'];
            $sectionLabel = $item['section'];
            $employee = $employeesByNum->get($assessment->employee_num);

            if (! $employee) {
                continue;
            }

            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $employeeLabel = $employee->formalName();

            $todos[] = [
                'id' => 'review-competency-returned-' . $assessment->id . '-' . md5($sectionLabel),
                'title' => 'Update: ' . $sectionLabel,
                'description' => $employeeLabel . ' sent ' . $sectionLabel . ' back for updates'
                    . ($periodLabel ? " ({$periodLabel})" : '')
                    . '.',
                'type' => 'competency-review',
                'priority' => 'high',
                'due_at' => $this->parseDate($assessment->period?->date_to)?->format('Y-m-d'),
                'action_url' => $sectionWorkflow->buildReviewerSectionChecklistUrl(
                    $employee,
                    (int) $assessment->assessment_period_id,
                    $sectionLabel,
                ),
            ];
        }

        $notificationService = app(AssessmentConfirmationNotificationService::class);

        $performanceAssessments = EmployeePerformanceAssessment::query()
            ->where('assessed_by', $user->id)
            ->where(function ($query) {
                $query->whereIn('status', [
                    AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
                    'for_reviewer_signature',
                ])
                    ->orWhere(function ($nested) {
                        $nested->where('status', AssessmentWorkflowStatus::DRAFT)
                            ->whereNotNull('review_dt')
                            ->whereNull('acknowledge_dt')
                            ->whereNull('employee_signature_path');
                    });
            })
            ->with('period')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $performanceEmployeesByNum = BPEmployee::query()
            ->whereIn('employee_num', $performanceAssessments->pluck('employee_num')->filter()->unique()->all())
            ->get()
            ->keyBy('employee_num');

        foreach ($performanceAssessments as $assessment) {
            $employee = $performanceEmployeesByNum->get($assessment->employee_num);
            if (! $employee) {
                continue;
            }

            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $employeeLabel = $employee->formalName();
            $isApproval = $assessment->workflowStatus() === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL;

            $todos[] = [
                'id' => $isApproval
                    ? 'review-performance-approve-' . $assessment->id
                    : 'review-performance-returned-' . $assessment->id,
                'title' => $isApproval ? 'Approve performance appraisal' : 'Update performance appraisal',
                'description' => $isApproval
                    ? ($employeeLabel . ' acknowledged their performance appraisal' . ($periodLabel ? " ({$periodLabel})" : '') . '. Review and approve to complete.')
                    : ($employeeLabel . ' sent their performance appraisal back for updates' . ($periodLabel ? " ({$periodLabel})" : '') . '.'),
                'type' => 'performance-review',
                'priority' => 'high',
                'due_at' => $this->parseDate($assessment->period?->date_to)?->format('Y-m-d'),
                'action_url' => $notificationService->buildReviewerChecklistUrl(
                    $employee,
                    'partF',
                    (int) $assessment->assessment_period_id
                ),
            ];
        }

        return $todos;
    }

    /**
     * @param  list<array<string, mixed>>  $documentsNeeded
     * @return list<array<string, mixed>>
     */
    protected function buildReminders(
        ?BPEmployee $employee,
        array $empChecklistItems,
        Collection $preEmploymentChecklists,
        array $documentsNeeded
    ): array {
        $reminders = [];
        $today = Carbon::today();

        foreach ($preEmploymentChecklists->where('status', 'returned') as $item) {
            $reminders[] = [
                'id' => 'rem-pe-' . $item->id,
                'title' => 'Pre-employment item returned',
                'message' => ($item->item_label ?? 'Checklist item') . ' needs corrections.',
                'type' => 'warning',
                'date' => $item->returned_at?->format('Y-m-d') ?? $today->toDateString(),
                'icon' => 'fa-rotate-left',
            ];
        }

        if ($employee) {
            $positionId = $employee->currentAssignment?->position_id
                ?? $employee->currentAssignment?->position?->id;

            $expiringItems = ChecklistItem::query()
                ->applicableToPosition($positionId)
                ->where('isExpiring', true)
                ->get();

            foreach ($expiringItems as $item) {
                $key = 'item_' . $item->id;
                $stored = $empChecklistItems[$key] ?? $empChecklistItems[$item->name] ?? null;
                if (!is_array($stored) || empty($stored['exp_dt']) || !empty($stored['exp_dt_not_required'])) {
                    continue;
                }

                try {
                    $expDate = Carbon::parse($stored['exp_dt']);
                } catch (\Throwable) {
                    continue;
                }

                $daysUntil = $today->diffInDays($expDate, false);
                if ($daysUntil <= 60) {
                    $reminders[] = [
                        'id' => 'rem-exp-' . $item->id,
                        'title' => $item->name . ' expiring soon',
                        'message' => $daysUntil < 0
                            ? 'Expired ' . abs($daysUntil) . ' day(s) ago.'
                            : ($daysUntil === 0 ? 'Expires today.' : "Expires in {$daysUntil} day(s)."),
                        'type' => $daysUntil < 0 ? 'danger' : ($daysUntil <= 14 ? 'warning' : 'info'),
                        'date' => $expDate->toDateString(),
                        'icon' => 'fa-calendar-xmark',
                    ];
                }
            }
        }

        if (count($documentsNeeded) > 0) {
            $reminders[] = [
                'id' => 'rem-docs-summary',
                'title' => 'Employee file documents',
                'message' => count($documentsNeeded) . ' employee file item(s) need attention.',
                'type' => 'info',
                'date' => $today->toDateString(),
                'icon' => 'fa-folder-open',
            ];
        }

        return collect($reminders)
            ->sortBy('date')
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $documentsNeeded
     * @param  list<array<string, mixed>>  $signaturesNeeded
     * @param  list<array<string, mixed>>  $reminders
     * @param  list<array<string, mixed>>  $reviewerAssessmentTodos
     * @param  list<array<string, mixed>>  $employeeAssessmentTodos
     * @return list<array<string, mixed>>
     */
    protected function buildTodos(
        User $user,
        ?BPEmployee $employee,
        bool $hasPreEmployment,
        Collection $preEmploymentChecklists,
        array $documentsNeeded,
        array $signaturesNeeded,
        array $reminders,
        array $reviewerAssessmentTodos = [],
        array $employeeAssessmentTodos = [],
    ): array {
        $employeeConfirmationTodos = $employeeAssessmentTodos;
        $reviewerTodos = [];
        $signatureTodos = [];
        $otherTodos = [];

        foreach ($reviewerAssessmentTodos as $reviewTodo) {
            $reviewerTodos[] = $this->todoItem(
                $reviewTodo['id'],
                $reviewTodo['title'],
                $reviewTodo['description'],
                'assessment-review',
                $reviewTodo['priority'],
                $reviewTodo['action_url'] ?? route('employment.portal'),
                false
            );
        }

        if (!$user->email_verified_at) {
            $otherTodos[] = $this->todoItem('todo-verify-email', 'Verify your email address', 'Confirm your email to secure your account.', 'account', 'high', route('verification.notice'), false);
        }

        if (empty($user->google2fa_secret ?? null)) {
            $otherTodos[] = $this->todoItem('todo-mfa', 'Enable multi-factor authentication', 'Add an extra layer of security to your account.', 'security', 'medium', route('admin.mfa.setup.form'), false);
        }

        if ($hasPreEmployment) {
            foreach ($preEmploymentChecklists->whereIn('status', ['draft', 'returned']) as $item) {
                $otherTodos[] = $this->todoItem(
                    'todo-pe-' . $item->id,
                    $item->item_label ?? 'Pre-employment item',
                    $item->status === 'returned' ? 'Returned — please revise and resubmit.' : 'Complete and submit this item.',
                    'pre-employment',
                    $item->status === 'returned' ? 'high' : 'medium',
                    route('pre-employment.portal'),
                    false
                );
            }
        }

        foreach ($signaturesNeeded as $sig) {
            $signatureTodos[] = $this->todoItem(
                $sig['id'],
                'Sign: ' . $sig['title'],
                $sig['description'],
                'signature',
                $sig['priority'],
                $sig['action_url'] ?? route('employment.portal'),
                false
            );
        }

        foreach (array_slice($documentsNeeded, 0, 8) as $doc) {
            $otherTodos[] = $this->todoItem(
                'todo-' . $doc['id'],
                $doc['title'],
                $doc['section'] . ' — ' . $doc['status_label'],
                'document',
                $doc['priority'],
                route('employment.portal'),
                false
            );
        }

        if ($employee && !$employee->currentAssignment) {
            $otherTodos[] = $this->todoItem('todo-assignment', 'Position assignment pending', 'Contact HR to confirm your current job assignment.', 'hr', 'medium', route('employment.portal'), false);
        }

        foreach (array_slice($reminders, 0, 3) as $reminder) {
            if (($reminder['type'] ?? '') === 'danger') {
                $otherTodos[] = $this->todoItem(
                    'todo-' . $reminder['id'],
                    $reminder['title'],
                    $reminder['message'],
                    'reminder',
                    'high',
                    route('employment.portal'),
                    false
                );
            }
        }

        return array_merge($employeeConfirmationTodos, $reviewerTodos, $signatureTodos, $otherTodos);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildCalendarEvents(?BPEmployee $employee, array $reminders, Collection $preEmploymentChecklists): array
    {
        $events = [];

        foreach ($reminders as $reminder) {
            if (!empty($reminder['date'])) {
                $events[] = [
                    'id' => 'cal-' . $reminder['id'],
                    'title' => $reminder['title'],
                    'date' => $reminder['date'],
                    'type' => $reminder['type'],
                    'description' => $reminder['message'],
                ];
            }
        }

        $periods = EmployeeAssessmentPeriod::query()
            ->orderByDesc('date_from')
            ->limit(6)
            ->get();

        foreach ($periods as $period) {
            $dateFrom = $this->parseDate($period->date_from);
            $dateTo = $this->parseDate($period->date_to);

            if ($dateFrom) {
                $events[] = [
                    'id' => 'cal-period-start-' . $period->id,
                    'title' => 'Assessment period opens',
                    'date' => $dateFrom->toDateString(),
                    'type' => 'period',
                    'description' => $this->formatPeriodLabel($period) ?: 'Review period',
                ];
            }
            if ($dateTo) {
                $events[] = [
                    'id' => 'cal-period-end-' . $period->id,
                    'title' => 'Assessment period due',
                    'date' => $dateTo->toDateString(),
                    'type' => 'deadline',
                    'description' => $this->formatPeriodLabel($period) ?: 'Review period deadline',
                ];
            }
        }

        if ($employee?->original_hire_dt) {
            try {
                $hire = Carbon::parse($employee->original_hire_dt);
                $events[] = [
                    'id' => 'cal-hire-anniversary',
                    'title' => 'Work anniversary',
                    'date' => $hire->copy()->year(now()->year)->format('Y-m-d'),
                    'type' => 'milestone',
                    'description' => 'Anniversary of your hire date.',
                ];
            } catch (\Throwable) {
                // ignore invalid date
            }
        }

        foreach ($preEmploymentChecklists->whereNotNull('submitted_at') as $item) {
            $events[] = [
                'id' => 'cal-pe-sub-' . $item->id,
                'title' => 'Submitted: ' . ($item->item_label ?? 'Checklist'),
                'date' => $item->submitted_at->format('Y-m-d'),
                'type' => 'activity',
                'description' => 'Pre-employment checklist submission.',
            ];
        }

        return collect($events)
            ->unique('id')
            ->sortBy('date')
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildRecentActivity(
        User $user,
        ?BPEmployee $employee,
        Collection $preEmploymentChecklists,
        array $empChecklistItems
    ): array {
        $activities = [];

        foreach ($preEmploymentChecklists->sortByDesc('updated_at')->take(5) as $item) {
            $activities[] = [
                'icon' => match ($item->status) {
                    'completed' => 'fa-check-circle',
                    'returned' => 'fa-rotate-left',
                    'submitted' => 'fa-paper-plane',
                    default => 'fa-clipboard-list',
                },
                'color' => match ($item->status) {
                    'completed' => 'emerald',
                    'returned' => 'amber',
                    'submitted' => 'sky',
                    default => 'slate',
                },
                'message' => ($item->item_label ?? 'Checklist item') . ' — ' . ucfirst($item->status ?? 'updated'),
                'time' => $item->updated_at ?? $item->created_at,
            ];
        }

        if ($employee) {
            $latestCompetency = EmployeeCompetencyAssessment::query()
                ->where('employee_num', $employee->employee_num)
                ->latest('updated_at')
                ->first();

            if ($latestCompetency) {
                $activities[] = [
                    'icon' => 'fa-star',
                    'color' => 'teal',
                    'message' => 'Competency assessment ' . str_replace('_', ' ', (string) $latestCompetency->status),
                    'time' => $latestCompetency->updated_at,
                ];
            }
        }

        $activities[] = [
            'icon' => 'fa-user',
            'color' => 'blue',
            'message' => 'Account active',
            'time' => $user->updated_at ?? $user->created_at,
        ];

        return collect($activities)
            ->filter(fn ($a) => !empty($a['time']))
            ->sortByDesc('time')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildQuickLinks(bool $hasPreEmployment, bool $hasEmployeeRecord): array
    {
        $links = [
            ['label' => 'My Profile', 'route' => route('settings.profile'), 'icon' => 'fa-user', 'color' => 'slate'],
            ['label' => 'Change Password', 'route' => route('settings.password'), 'icon' => 'fa-key', 'color' => 'slate'],
        ];

        if ($hasPreEmployment) {
            $links[] = ['label' => 'Pre-Employment Portal', 'route' => route('pre-employment.portal'), 'icon' => 'fa-clipboard-check', 'color' => 'teal'];
        }

        if ($hasEmployeeRecord) {
            $links[] = ['label' => 'Employment Portal', 'route' => route('employment.portal'), 'icon' => 'fa-briefcase', 'color' => 'indigo'];
        }

        return $links;
    }

    protected function employeeFileVerifiedPercent(?BPEmployee $employee, array $empChecklistItems): ?int
    {
        return $this->evaluateEmployeeChecklistCompliance($employee, $empChecklistItems)['verified_percent'];
    }

    protected function formatPeriodLabel(?EmployeeAssessmentPeriod $period): ?string
    {
        if (!$period) {
            return null;
        }

        $year = $period->period_year ?? $period->date_from?->format('Y');
        $seq = $period->period_sequence;

        if ($year && $seq) {
            return "{$year} · Period {$seq}";
        }

        $from = $this->parseDate($period->date_from);
        $to = $this->parseDate($period->date_to);
        if ($from && $to) {
            return $from->format('M j') . ' – ' . $to->format('M j, Y');
        }

        return $year ? (string) $year : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function todoItem(
        string $id,
        string $title,
        string $description,
        string $category,
        string $priority,
        string $url,
        bool $done
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'priority' => $priority,
            'url' => $url,
            'done' => $done,
        ];
    }
}
