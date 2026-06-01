<?php

namespace App\Services;

use App\Livewire\Admin\Facilities\Checklist\PartEOrientationChecklist;
use App\Models\BPEmployee;
use App\Models\BPEmpChecklist;
use App\Models\ChecklistItem;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeChecklist;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;
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
                        'route' => route('member.trainings'),
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
                    'route' => route('member.trainings'),
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
                        'title' => 'Facility HR Portal',
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
                        'route' => route('member.trainings'),
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
                        'route' => route('member.trainings'),
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
                        'route' => route('member.trainings'),
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
                        'title' => 'My Trainings',
                        'subtitle' => 'Complete required clinical trainings',
                        'route' => route('member.trainings'),
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
                        'title' => 'My Trainings',
                        'subtitle' => 'Stay current on mandatory training items',
                        'route' => route('member.trainings'),
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
                    'route' => route('member.trainings'),
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
                        'route' => route('member.trainings'),
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
                    'title' => 'Facility HR Portal',
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
                    'route' => route('member.trainings'),
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
                        'label' => 'Open Facility HR Portal',
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

    protected function userHasRole(User $user, string $role): bool
    {
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        return (bool) $user->hasRole($role);
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
        $documentsCenter = $this->buildDocumentsCenter($user, $bpEmployee, $empChecklistItems, $complianceEvaluation);
        $facilityComplianceReport = $this->buildFacilityComplianceReport($user);
        $reminders = $this->buildReminders($bpEmployee, $empChecklistItems, $preEmploymentChecklists, $documentsNeeded);
        $todos = $this->buildTodos($user, $bpEmployee, $hasPreEmployment, $preEmploymentChecklists, $documentsNeeded, $signaturesNeeded, $reminders);
        $calendarEvents = $this->buildCalendarEvents($bpEmployee, $reminders, $preEmploymentChecklists);
        $recentActivity = $this->buildRecentActivity($user, $bpEmployee, $preEmploymentChecklists, $empChecklistItems);
        $quickLinks = $this->buildQuickLinks($hasPreEmployment, (bool) $bpEmployee);

        $certSummary = ['expiring' => 0, 'expired' => 0, 'needs_attention' => 0];
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

        $trainingsSummary = $this->buildTrainingsCenter(
            $user,
            $bpEmployee,
            is_array($empChecklistItems) ? $empChecklistItems : [],
            $preEmploymentChecklists
        )['summary'];

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
            'employeeDisplayName' => $bpEmployee
                ? trim(($bpEmployee->first_name ?? '') . ' ' . ($bpEmployee->last_name ?? ''))
                : $user->name,
        ];
    }

    /**
     * Documents page payload (avoids full dashboard build).
     *
     * @return array{documentsCenter: array, facilityComplianceReport: array|null, stats: array<string, mixed>}
     */
    /**
     * Schedule page payload (HR calendar events; work shifts not yet integrated).
     *
     * @return array{
     *     calendarEvents: list<array<string, mixed>>,
     *     weekDays: list<array<string, mixed>>,
     *     upcomingEvents: list<array<string, mixed>>,
     *     todayLabel: string,
     *     weekRangeLabel: string,
     *     hasShiftData: bool,
     *     positionTitle: string,
     *     facilityName: string|null
     * }
     */
    public function buildSchedulePage(User $user): array
    {
        $bpEmployee = $user->resolvedBpEmployee([
            'currentAssignment.position',
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

        $documentsNeeded = $this->evaluateEmployeeChecklistCompliance($bpEmployee, $empChecklistItems)['missing'];
        $reminders = $this->buildReminders($bpEmployee, $empChecklistItems, $preEmploymentChecklists, $documentsNeeded);
        $calendarEvents = $this->buildCalendarEvents($bpEmployee, $reminders, $preEmploymentChecklists);

        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $weekDays = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dateStr = $day->toDateString();
            $weekDays[] = [
                'date' => $dateStr,
                'label' => $day->format('D'),
                'day_num' => $day->format('j'),
                'is_today' => $day->isSameDay($today),
                'events' => collect($calendarEvents)
                    ->filter(fn ($event) => ($event['date'] ?? '') === $dateStr)
                    ->values()
                    ->all(),
            ];
        }

        $upcomingEvents = collect($calendarEvents)
            ->filter(fn ($event) => !empty($event['date']) && $event['date'] >= $today->toDateString())
            ->sortBy('date')
            ->values()
            ->all();

        return [
            'calendarEvents' => $calendarEvents,
            'weekDays' => $weekDays,
            'upcomingEvents' => $upcomingEvents,
            'todayLabel' => $today->format('l, F j, Y'),
            'weekRangeLabel' => $weekStart->format('M j') . ' – ' . $weekStart->copy()->endOfWeek()->format('M j, Y'),
            'hasShiftData' => false,
            'positionTitle' => $bpEmployee?->currentAssignment?->position?->title ?? '—',
            'facilityName' => $bpEmployee?->currentAssignment?->facility?->name ?? $user->facility?->name,
        ];
    }

    public function buildDocumentsPage(User $user): array
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

        $documentsCenter = $this->buildDocumentsCenter($user, $bpEmployee, $empChecklistItems, $documentCompliance);
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
    public function buildTrainingsPage(User $user): array
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

        $trainingsCenter = $this->buildTrainingsCenter(
            $user,
            $bpEmployee,
            is_array($empChecklistItems) ? $empChecklistItems : [],
            $preEmploymentChecklists
        );

        return [
            'trainingsCenter' => $trainingsCenter,
            'facilityTrainingsReport' => $this->buildFacilityTrainingsReport($user),
            'stats' => $trainingsCenter['summary'],
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
        Collection $preEmploymentChecklists
    ): array {
        $employmentPortalUrl = route('employment.portal');
        $profileCompetenciesUrl = route('settings.profile') . '#competencies';

        $orientationItems = $bpEmployee
            ? $this->collectOrientationTrainings($empChecklistItems, $employmentPortalUrl)
            : [];

        $competencyItems = $bpEmployee
            ? $this->collectCompetencyTrainings($bpEmployee->employee_num, $profileCompetenciesUrl, $employmentPortalUrl)
            : [];

        $requiredItems = $bpEmployee
            ? $this->evaluateTrainingChecklistItems($bpEmployee, $empChecklistItems, $employmentPortalUrl)
            : [];

        $preEmploymentItems = $this->mapPreEmploymentTrainings($preEmploymentChecklists);

        $groups = [
            [
                'key' => 'orientation',
                'label' => 'Orientation',
                'description' => 'Part E facility orientation checklist and signatures',
                'items' => $orientationItems,
            ],
            [
                'key' => 'competency',
                'label' => 'Competency evaluations',
                'description' => 'Periodic skills and competency assessments',
                'items' => $competencyItems,
            ],
            [
                'key' => 'required',
                'label' => 'Required training & acknowledgements',
                'description' => 'Employee file items from Parts B–D (policies, safety, HIPAA, etc.)',
                'items' => $requiredItems,
            ],
        ];

        if (count($preEmploymentItems) > 0) {
            $groups[] = [
                'key' => 'pre_employment',
                'label' => 'Pre-employment checklist',
                'description' => 'Onboarding tasks before your start date',
                'items' => $preEmploymentItems,
            ];
        }

        $allItems = collect($groups)->flatMap(fn ($group) => $group['items'])->all();
        $summary = $this->summarizeTrainingItems($allItems);

        return [
            'groups' => $groups,
            'items' => $allItems,
            'summary' => $summary,
            'has_employee_record' => (bool) $bpEmployee,
            'has_pre_employment' => count($preEmploymentItems) > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return list<array<string, mixed>>
     */
    protected function collectOrientationTrainings(array $empChecklistItems, string $actionUrl): array
    {
        $items = [];
        $today = Carbon::today();

        foreach ($empChecklistItems as $storageKey => $payload) {
            if (!is_string($storageKey) || !str_starts_with($storageKey, 'part_e_orientation_summary_')) {
                continue;
            }

            $items[] = $this->mapOrientationTraining($storageKey, is_array($payload) ? $payload : [], $actionUrl, $today);
        }

        if (count($items) === 0) {
            $items[] = array_merge(
                $this->trainingStatusRow('not_started', 'Not started', $today, null),
                [
                    'id' => 'training-orientation-default',
                    'title' => 'Part E orientation checklist',
                    'subtitle' => 'Facility orientation has not been started in your employee file.',
                    'category' => 'orientation',
                    'action_url' => $actionUrl,
                    'action_label' => 'Open employment portal',
                ]
            );
        }

        usort($items, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        return $items;
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
            $title .= ' (job code ' . $suffix . ')';
        }

        $workflow = (string) ($stored['workflow_status'] ?? PartEOrientationChecklist::WORKFLOW_DRAFT);
        $statusMeta = $this->resolveOrientationTrainingStatus($workflow, $today);

        $actionLabel = $statusMeta['status'] === 'pending_signature'
            ? 'Sign checklist'
            : 'View in employment portal';

        return array_merge($statusMeta, [
            'id' => 'training-orientation-' . $storageKey,
            'title' => $title,
            'subtitle' => PartEOrientationChecklist::WORKFLOW_COMPLETED === $workflow
                ? 'Orientation checklist completed and signed.'
                : 'Workflow: ' . str_replace('_', ' ', $workflow),
            'category' => 'orientation',
            'workflow_status' => $workflow,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function collectCompetencyTrainings(
        string $employeeNum,
        string $profileUrl,
        string $employmentPortalUrl
    ): array {
        $today = Carbon::today();
        $assessments = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->with('period')
            ->orderByDesc('updated_at')
            ->get();

        $items = [];

        foreach ($assessments as $assessment) {
            $periodLabel = $this->formatPeriodLabel($assessment->period);
            $title = 'Competency assessment' . ($periodLabel ? " · {$periodLabel}" : '');
            $statusMeta = $this->resolveCompetencyTrainingStatus($assessment, $today);
            $needsSign = ($statusMeta['status'] ?? '') === 'pending_signature';

            $items[] = array_merge($statusMeta, [
                'id' => 'training-competency-' . $assessment->id,
                'title' => $title,
                'subtitle' => 'Status: ' . str_replace('_', ' ', (string) ($assessment->status ?? 'unknown')),
                'category' => 'competency',
                'assessment_id' => $assessment->id,
                'action_url' => $needsSign ? $profileUrl : $employmentPortalUrl,
                'action_label' => $needsSign ? 'Review & sign' : 'View details',
            ]);
        }

        usort($items, fn ($a, $b) => ($this->trainingStatusPriority($a['status'] ?? '') <=> $this->trainingStatusPriority($b['status'] ?? '')));

        return $items;
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
                'subtitle' => $item->section,
                'category' => 'required',
                'section' => $item->section,
                'action_url' => $actionUrl,
                'action_label' => ($statusMeta['status'] ?? '') === 'completed' ? 'View file' : 'Complete in portal',
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
                'in_progress' => $summary['in_progress']++,
                'pending_signature' => $summary['pending_signature']++,
                'overdue' => $summary['overdue']++,
                default => null,
            };

            if (in_array($status, ['pending_signature', 'overdue', 'not_started'], true)) {
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
            ->orderBy('last_name')
            ->orderBy('first_name')
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

            $orientationItems = $this->collectOrientationTrainings($empItems, '#');
            $incompleteOrientation = collect($orientationItems)
                ->where('status', '!=', 'completed')
                ->count();

            $unsignedCompetency = EmployeeCompetencyAssessment::query()
                ->where('employee_num', $employee->employee_num)
                ->whereNull('employee_signed_at')
                ->whereIn('status', ['submitted', 'completed'])
                ->count();

            $requiredItems = $this->evaluateTrainingChecklistItems($employee, $empItems, '#');
            $incompleteTraining = collect($requiredItems)
                ->whereIn('status', ['not_started', 'in_progress', 'overdue', 'pending_signature'])
                ->count();

            $issueCount = $incompleteOrientation + $unsignedCompetency + $incompleteTraining;
            if ($issueCount > 0) {
                $employeesWithIssues++;
            }

            $totalIncompleteOrientation += $incompleteOrientation;
            $totalUnsignedCompetency += $unsignedCompetency;
            $totalIncompleteTraining += $incompleteTraining;

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

            $rows[] = [
                'employee_num' => $employee->employee_num,
                'name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                'position' => $employee->currentAssignment?->position?->title ?? '—',
                'incomplete_orientation' => $incompleteOrientation,
                'unsigned_competency' => $unsignedCompetency,
                'incomplete_training' => $incompleteTraining,
                'issue_count' => $issueCount,
                'top_issues' => $topIssues,
                'manage_url' => route('admin.employees.edit', $employee->employee_num) . '?tab=checklist',
            ];
        }

        usort($rows, fn ($a, $b) => $b['issue_count'] <=> $a['issue_count']);

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
                'Your signature required',
                $today,
                null
            ),
            PartEOrientationChecklist::WORKFLOW_REVIEWER_SIGNATURE => $this->trainingStatusRow(
                'in_progress',
                'Awaiting reviewer signature',
                $today,
                null
            ),
            default => $this->trainingStatusRow(
                'in_progress',
                'In progress',
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
            'overdue' => 0,
            'pending_signature' => 1,
            'not_started' => 2,
            'in_progress' => 3,
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
        $items = $bpEmployee
            ? $this->evaluateCertificationItems($bpEmployee, $empChecklistItems)
            : [];

        $uploads = $this->mapEmployeeUploads($bpEmployee, $facility, $user);
        $expiringUploads = array_values(array_filter($uploads, function ($row) {
            return !empty($row['expires_at']) && !empty($row['is_license_or_certification']);
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
            'expiring_uploads' => $expiringUploads,
            'summary' => $summary,
            'has_employee_record' => (bool) $bpEmployee,
        ];
    }

    /**
     * @param  array<string, mixed>  $empChecklistItems
     * @return list<array<string, mixed>>
     */
    protected function evaluateCertificationItems(BPEmployee $employee, array $empChecklistItems): array
    {
        $compliance = app(DocumentComplianceService::class)->forEmployee($employee);
        $applicableItems = collect($compliance['items'] ?? [])
            ->filter(fn ($item) => !empty($item['is_license_or_certification']))
            ->values();

        $today = Carbon::today();
        $rows = [];

        foreach ($applicableItems as $item) {
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
            } else {
                $statusMeta = $this->certificationStatusRow('not_on_file', 'Not on file', null, null);
            }

            $rows[] = array_merge([
                'id' => 'cert-upload-type-' . (int) ($item['upload_type_id'] ?? 0),
                'upload_type_id' => (int) ($item['upload_type_id'] ?? 0),
                'title' => (string) ($item['name'] ?? '—'),
                'section' => 'Required upload type',
                'required' => true,
                'doc_type' => 'Upload Type',
                'on_file' => ($item['status'] ?? '') !== 'missing',
                'verified' => ($item['status'] ?? '') === 'complete',
                'is_license_or_certification' => true,
            ], $statusMeta);
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

    protected function isLicenseOrCertificationItem(ChecklistItem $item): bool
    {
        if ($item->is_license_or_certification !== null) {
            return (bool) $item->is_license_or_certification;
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
            ->orderBy('last_name')
            ->orderBy('first_name')
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

            $rows[] = [
                'employee_num' => $employee->employee_num,
                'name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                'position' => $employee->currentAssignment?->position?->title ?? '—',
                'expiring_count' => $expiring,
                'expired_count' => $expired,
                'missing_count' => $missing,
                'issue_count' => $issueCount,
                'top_issues' => $topIssues,
                'manage_url' => route('admin.employees.edit', $employee->employee_num) . '?tab=checklist',
            ];
        }

        usort($rows, fn ($a, $b) => $b['issue_count'] <=> $a['issue_count']);

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
     * @param  array<string, mixed>  $empChecklistItems
     * @param  array<string, mixed>|null  $documentCompliance
     * @return array<string, mixed>
     */
    public function buildDocumentsCenter(
        User $user,
        ?BPEmployee $bpEmployee,
        array $empChecklistItems,
        ?array $documentCompliance = null
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
        $requiredUploadTypes = $complianceItems
            ->map(function ($item) {
                return [
                    'id' => (int) ($item['upload_type_id'] ?? 0),
                    'name' => $item['name'] ?? 'Document',
                    'requires_expiry' => (bool) ($item['requires_expiry'] ?? false),
                ];
            })
            ->filter(fn ($item) => $item['id'] > 0)
            ->unique('id')
            ->values()
            ->all();

        $complianceMissing = $complianceItems
            ->filter(fn ($item) => in_array(($item['status'] ?? ''), ['missing', 'expired'], true))
            ->map(function ($item) {
                $status = (string) ($item['status'] ?? 'missing');

                return [
                    'id' => 'upload-type-' . ($item['upload_type_id'] ?? uniqid()),
                    'upload_type_id' => $item['upload_type_id'] ?? null,
                    'title' => $item['name'] ?? 'Required document',
                    'section' => 'Required upload type',
                    'required' => true,
                    'status' => $status,
                    'status_label' => $status === 'expired' ? 'Expired' : 'Not on file',
                    'priority' => $status === 'expired' ? 'medium' : 'high',
                    'due_at' => null,
                ];
            })
            ->values()
            ->all();

        $complianceComplete = $complianceItems
            ->filter(fn ($item) => ($item['status'] ?? '') === 'complete')
            ->map(function ($item) {
                return [
                    'id' => 'upload-type-ok-' . ($item['upload_type_id'] ?? uniqid()),
                    'title' => $item['name'] ?? 'Required document',
                    'section' => 'Required upload type',
                    'required' => true,
                    'status' => 'complete',
                    'status_label' => 'On file',
                ];
            })
            ->values()
            ->all();

        $summary = $documentCompliance['summary'] ?? [];
        $totalRequired = (int) ($summary['total'] ?? count($complianceItems));
        $completeRequired = (int) ($summary['complete'] ?? count($complianceComplete));
        $verifiedPercent = $totalRequired > 0
            ? (int) round(($completeRequired / $totalRequired) * 100)
            : null;

        $facility = $bpEmployee?->currentAssignment?->facility ?? $user->facility;

        if (!$facility && $user->facility_id) {
            $facility = Facility::find($user->facility_id);
        }

        return [
            'uploads' => $this->mapEmployeeUploads($bpEmployee, $facility, $user),
            'compliance_missing' => $complianceMissing,
            'compliance_complete' => $complianceComplete,
            'required_upload_types' => $requiredUploadTypes,
            'signatures' => $this->buildSignaturesNeeded($bpEmployee, $empChecklistItems),
            'verified_percent' => $verifiedPercent,
            'has_employee_record' => (bool) $bpEmployee,
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
            ->orderBy('last_name')
            ->orderBy('first_name')
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

            $rows[] = [
                'employee_num' => $employee->employee_num,
                'name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                'position' => $employee->currentAssignment?->position?->title ?? '—',
                'missing_count' => $missingCount,
                'top_missing' => array_slice(array_column($missing, 'title'), 0, 3),
                'verified_percent' => $evaluation['verified_percent'],
                'manage_url' => route('admin.employees.edit', $employee->employee_num) . '?tab=checklist',
            ];
        }

        usort($rows, fn ($a, $b) => $b['missing_count'] <=> $a['missing_count']);

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
    protected function mapEmployeeUploads(?BPEmployee $employee, ?Facility $facility, User $user): array
    {
        if (!$employee) {
            return [];
        }

        $canUseAdminUploadRoutes = $facility
            && method_exists($user, 'canManageFacility')
            && $user->canManageFacility($facility->id);

        $facilityKey = $facility ? ($facility->slug ?? $facility->id) : null;

        return $employee->uploads()
            ->with('uploadType')
            ->orderByDesc('uploaded_at')
            ->get()
            ->map(function (Upload $upload) use ($canUseAdminUploadRoutes, $facilityKey) {
                $uploadedAt = $this->parseDate($upload->uploaded_at);
                $expiresAt = $this->parseDate($upload->expires_at);

                $row = [
                    'id' => $upload->id,
                    'upload_type_id' => $upload->upload_type_id,
                    'name' => $upload->original_filename ?: basename((string) $upload->file_path),
                    'type' => $upload->uploadType?->name ?? 'Document',
                    'is_license_or_certification' => (bool) ($upload->uploadType?->is_license_or_certification ?? false),
                    'uploaded_at' => $uploadedAt?->format('M j, Y'),
                    'expires_at' => $expiresAt?->format('M j, Y'),
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
                    $row['edit_url'] = route('admin.employees.edit', $upload->employee_num) . '?tab=documents';
                } else {
                    $row['view_url'] = route('employment.documents.view', ['document' => $upload->id]);
                    $row['download_url'] = route('employment.documents.download', ['document' => $upload->id]);
                    $row['edit_url'] = route('employment.portal', ['tab' => 'documents']) . '#upload-table';
                }

                return $row;
            })
            ->values()
            ->all();
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

            $competencyPending = EmployeeCompetencyAssessment::query()
                ->where('employee_num', $employee->employee_num)
                ->whereNull('employee_signed_at')
                ->whereIn('status', ['submitted', 'completed'])
                ->with('period')
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($competencyPending as $assessment) {
                $periodLabel = $this->formatPeriodLabel($assessment->period);
                $needed[] = [
                    'id' => 'sig-competency-' . $assessment->id,
                    'title' => 'Competency assessment',
                    'description' => 'Sign competency evaluation' . ($periodLabel ? " ({$periodLabel})" : '') . '.',
                    'type' => 'competency',
                    'priority' => 'high',
                    'due_at' => $this->parseDate($assessment->period?->date_to)?->format('Y-m-d'),
                ];
            }

            $performancePending = EmployeePerformanceAssessment::query()
                ->where('employee_num', $employee->employee_num)
                ->where('finalized', 1)
                ->whereNull('acknowledge_dt')
                ->with('period')
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($performancePending as $assessment) {
                $periodLabel = $this->formatPeriodLabel($assessment->period);
                $needed[] = [
                    'id' => 'sig-performance-' . $assessment->id,
                    'title' => 'Performance appraisal',
                    'description' => 'Acknowledge your performance appraisal' . ($periodLabel ? " ({$periodLabel})" : '') . '.',
                    'type' => 'performance',
                    'priority' => 'medium',
                    'due_at' => $this->parseDate($assessment->period?->date_to)?->format('Y-m-d'),
                ];
            }
        }

        return $needed;
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
                'message' => count($documentsNeeded) . ' checklist item(s) need attention.',
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
     * @return list<array<string, mixed>>
     */
    protected function buildTodos(
        User $user,
        ?BPEmployee $employee,
        bool $hasPreEmployment,
        Collection $preEmploymentChecklists,
        array $documentsNeeded,
        array $signaturesNeeded,
        array $reminders
    ): array {
        $todos = [];

        if (!$user->email_verified_at) {
            $todos[] = $this->todoItem('todo-verify-email', 'Verify your email address', 'Confirm your email to secure your account.', 'account', 'high', route('settings.profile'), false);
        }

        if (empty($user->google2fa_secret ?? null)) {
            $todos[] = $this->todoItem('todo-mfa', 'Enable multi-factor authentication', 'Add an extra layer of security to your account.', 'security', 'medium', route('admin.mfa.setup.form'), false);
        }

        if ($hasPreEmployment) {
            foreach ($preEmploymentChecklists->whereIn('status', ['draft', 'returned']) as $item) {
                $todos[] = $this->todoItem(
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
            $todos[] = $this->todoItem(
                $sig['id'],
                'Sign: ' . $sig['title'],
                $sig['description'],
                'signature',
                $sig['priority'],
                route('employment.portal'),
                false
            );
        }

        foreach (array_slice($documentsNeeded, 0, 8) as $doc) {
            $todos[] = $this->todoItem(
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
            $todos[] = $this->todoItem('todo-assignment', 'Position assignment pending', 'Contact HR to confirm your current job assignment.', 'hr', 'medium', route('employment.portal'), false);
        }

        foreach (array_slice($reminders, 0, 3) as $reminder) {
            if (($reminder['type'] ?? '') === 'danger') {
                $todos[] = $this->todoItem(
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

        return $todos;
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
