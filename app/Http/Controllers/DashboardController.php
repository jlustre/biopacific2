<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Services\MemberDashboardService;
use App\Services\PersonalProfilePanelsService;
use Illuminate\Http\Request;
use App\Helpers\FacilityDataHelper;
use App\Models\Facility;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\Service;
use App\Models\News;
use App\Models\ColorScheme;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\EmployeeChecklist;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use ProvidesMemberPortalContext;

    public function index()
    {

        $user = Auth::user();
        $routeName = request()->route()->getName();

        // Helper fallback for hasRole
        $hasRole = function($role) use ($user) {
            return method_exists($user, 'hasRole') ? $user->hasRole($role) : false;
        };

        // Admin dashboard main view
        if ($routeName === 'admin.dashboard.index' && $hasRole(['admin', 'super-admin'])) {
            $facilities = \App\Models\Facility::all();
            $facilitiesByState = $facilities->groupBy('state');
            $recentActivities = [];
            $context = $this->memberPortalContext($user);

            return view('admin.dashboard.index', array_merge($context, compact(
                'facilities',
                'facilitiesByState',
                'recentActivities'
            )));
        }

        // HR Portal dashboard for rdhr, facility-admin, facility-dsd
        if ($routeName === 'admin.dashboard.index' && $hasRole(['rdhr', 'facility-admin', 'facility-dsd'])) {
            return view('dashboard.hr-portal-dashboard');
        }

        // Member / employee dashboard (static UI — refactor with live data later)
        if (in_array($routeName, ['user.dashboard', 'dashboard.index'], true)) {
            if (!$hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'facility-editor'])) {
                return $this->memberDashboard($user);
            }
        }

        // Fallback for any other roles/routes
        return $this->memberDashboard($user);
    }

    protected function memberDashboard($user)
    {
        $context = $this->memberPortalContext($user);
        $dashboardService = app(MemberDashboardService::class);
        $dashboardData = $dashboardService->build($user);
        $newsEventsCount = $context['newsEventsCount'] ?? $this->countMemberNewsEvents($context['facility'] ?? null);
        $dashboardModules = $dashboardService->buildDashboardModules(
            $user,
            $dashboardData['stats'] ?? [],
            (int) $newsEventsCount,
            $dashboardData['positionTitle'] ?? null
        );

        return view('dashboard.member.index', array_merge($context, $dashboardData, $dashboardModules, [
            'newsEventsCount' => $newsEventsCount,
            'todayLabel' => now()->format('l, F j, Y'),
            'portalActive' => 'dashboard',
            'portalTitle' => 'Bio Pacific HR Management | Employee Dashboard',
            'portalEyebrow' => 'Employee Dashboard',
            'portalPageTitle' => 'Welcome back, ' . ($context['firstNameOnly'] ?? 'there'),
            'showPortalSearch' => true,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberDocuments()
    {
        $user = Auth::user();
        $documentsData = app(MemberDashboardService::class)->buildDocumentsPage($user);
        $isFacilityDocumentsAdmin = $user->hasRole(['facility-admin', 'facility-dsd'])
            && !empty($documentsData['facilityComplianceReport']);

        return view('dashboard.member.documents', array_merge($this->memberPortalContext($user), $documentsData, [
            'portalActive' => 'documents',
            'portalTitle' => 'Documents | Bio Pacific HR Management',
            'portalEyebrow' => 'Document Center',
            'portalPageTitle' => 'Documents',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'isFacilityDocumentsAdmin' => $isFacilityDocumentsAdmin,
        ]));
    }

    public function memberCertifications()
    {
        $user = Auth::user();
        $certificationsData = app(MemberDashboardService::class)->buildCertificationsPage($user);
        $isFacilityCertificationsAdmin = $user->hasRole(['facility-admin', 'facility-dsd'])
            && !empty($certificationsData['facilityCertificationsReport']);

        return view('dashboard.member.certifications', array_merge($this->memberPortalContext($user), $certificationsData, [
            'portalActive' => 'certifications',
            'portalTitle' => 'Certifications | Bio Pacific HR Management',
            'portalEyebrow' => 'Licenses & Certifications',
            'portalPageTitle' => 'Certifications',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'isFacilityCertificationsAdmin' => $isFacilityCertificationsAdmin,
        ]));
    }

    public function memberTrainings()
    {
        $user = Auth::user();
        $trainingsData = app(MemberDashboardService::class)->buildTrainingsPage($user);
        $isFacilityTrainingsAdmin = $user->hasRole(['facility-admin', 'facility-dsd'])
            && !empty($trainingsData['facilityTrainingsReport']);

        return view('dashboard.member.trainings', array_merge($this->memberPortalContext($user), $trainingsData, [
            'portalActive' => 'trainings',
            'portalTitle' => 'Trainings | Bio Pacific HR Management',
            'portalEyebrow' => 'Learning & Compliance',
            'portalPageTitle' => 'Trainings',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'isFacilityTrainingsAdmin' => $isFacilityTrainingsAdmin,
        ]));
    }

    public function memberNewsEvents()
    {
        $user = Auth::user();
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment.position', 'currentAssignment.facility'])
            : null;
        $facility = $this->resolveMemberFacility($user, $employee);
        $newsItems = $facility
            ? FacilityDataHelper::getNews($facility)
            : News::query()->where('status', true)->where('is_global', true)->orderByDesc('published_at')->get();

        return view('dashboard.member.news-events', array_merge($this->memberPortalContext($user), [
            'newsItems' => $newsItems,
            'portalActive' => 'news',
            'portalTitle' => 'News & Events | Bio Pacific HR Management',
            'portalEyebrow' => 'News & Events',
            'portalPageTitle' => $facility?->name ?? 'Company updates',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberProfile(Request $request)
    {
        $user = $request->user()->fresh();
        $context = $this->memberPortalContext($user);
        $employee = $context['employee'] ?? null;

        if ($employee) {
            $employee->loadMissing(['phone', 'address']);
        }

        $panels = app(PersonalProfilePanelsService::class);

        return view('dashboard.member.profile', array_merge($context, [
            'portalActive' => 'profile',
            'portalTitle' => 'Bio Pacific HR Management | My Profile',
            'portalEyebrow' => 'Personal Portal',
            'portalPageTitle' => 'My Profile',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'profileComplete' => $this->calculatePersonalProfileComplete($user, $employee),
            'personalPhone' => $employee?->phone?->phone_number,
            'personalAddress' => $this->formatPersonalAddress($employee?->address),
            'dateOfBirth' => $employee?->dob?->format('M j, Y'),
            'legalName' => $employee
                ? trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name])))
                : null,
            'emailVerified' => $user->hasVerifiedEmail(),
            'memberSince' => $user->created_at?->timezone(config('app.timezone'))->format('M j, Y'),
            'lastUpdated' => $user->updated_at?->timezone(config('app.timezone'))->diffForHumans(),
            'upcomingExpirations' => $panels->upcomingExpirations($user, $employee),
            'profileRecognitions' => $panels->recognitions($user, $employee),
            'emergencyContacts' => $user->emergencyContacts()->get(),
        ]));
    }

    protected function calculatePersonalProfileComplete($user, $employee): int
    {
        $score = 0;

        if (filled($user->name)) {
            $score += 20;
        }
        if (filled($user->email)) {
            $score += 20;
        }
        if ($user->hasVerifiedEmail()) {
            $score += 20;
        }
        if ($employee?->phone?->phone_number) {
            $score += 20;
        }
        if ($this->formatPersonalAddress($employee?->address)) {
            $score += 20;
        }

        return min(100, $score);
    }

    protected function formatPersonalAddress($address): ?string
    {
        if (!$address) {
            return null;
        }

        $line1 = trim((string) ($address->address1 ?? ''));
        $line2 = trim((string) ($address->address2 ?? ''));
        $city = trim((string) ($address->city ?? ''));
        $state = trim((string) ($address->state ?? ''));
        $zip = trim((string) ($address->zip ?? ''));

        $cityLine = trim(implode(', ', array_filter([
            $city,
            trim($state . ($zip !== '' ? ' ' . $zip : '')),
        ])));

        $parts = array_filter([$line1, $line2, $cityLine]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    public function memberPassword(Request $request)
    {
        $context = $this->memberPortalContext($request->user());

        return view('dashboard.member.password', array_merge($context, [
            'portalActive' => 'profile',
            'portalTitle' => 'Bio Pacific HR Management | Change Password',
            'portalEyebrow' => 'Account Security',
            'portalPageTitle' => 'Change Password',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    /**
     * View another user's dashboard (for authorized staff)
     */
    public function showUserDashboard($userId)
    {
        $currentUser = Auth::user();
        
        // Check if current user has permission to view employee/applicant information
        if (!$currentUser->hasRole(['admin', 'rdhr', 'facility-admin', 'facility-dsd'])) {
            abort(403, 'Unauthorized to view user dashboards.');
        }

        $viewingUser = \App\Models\User::findOrFail($userId);
        
        // Facility admins can only view users from their facility
        if ($currentUser->hasRole(['facility-admin', 'facility-dsd']) && 
            $currentUser->facility_id !== $viewingUser->facility_id) {
            abort(403, 'Unauthorized to view this user\'s dashboard.');
        }

        // Get user-specific data
        $lastUpdated = $viewingUser->updated_at;
        $jobApplication = $viewingUser->jobApplications()->where('status', 'pre-employment')->first();
        $hasPreEmployment = $jobApplication !== null;
        
        // Pre-employment checklist stats
        $checklistStats = null;
        if ($hasPreEmployment) {
            $checklistItems = $viewingUser->employeeChecklists;
            $checklistStats = [
                'total' => $checklistItems->count(),
                'completed' => $checklistItems->where('status', 'completed')->count(),
                'submitted' => $checklistItems->where('status', 'submitted')->count(),
                'draft' => $checklistItems->where('status', 'draft')->count(),
                'returned' => $checklistItems->where('status', 'returned')->count(),
            ];
        }

        // Recent activity specific to this user
        $recentActivity = [];
        if ($hasPreEmployment) {
            $recentActivity[] = [
                'icon' => 'fa-clipboard-check',
                'color' => 'green',
                'message' => 'Pre-employment checklist in progress'
            ];
        }
        if ($viewingUser->created_at->gt(now()->subDays(7))) {
            $recentActivity[] = [
                'icon' => 'fa-user-plus',
                'color' => 'blue',
                'message' => 'Account created ' . $viewingUser->created_at->diffForHumans()
            ];
        }
        $recentActivity[] = [
            'icon' => 'fa-clock',
            'color' => 'gray',
            'message' => 'Last login ' . ($viewingUser->updated_at ? $viewingUser->updated_at->diffForHumans() : 'recently')
        ];

        return view('dashboard', [
            'lastUpdated' => $lastUpdated,
            'hasPreEmployment' => $hasPreEmployment,
            'jobApplication' => $jobApplication,
            'checklistStats' => $checklistStats,
            'recentActivity' => $recentActivity,
            'readOnly' => true,
            'viewingUser' => $viewingUser,
            'roleStats' => null,
            'roleFacility' => null,
        ]);
    }

    public function facility(Request $request)
    {

        $user = Auth::user();
        if ($user->hasRole('admin')) {
            // Admin dashboard
            $facility = Facility::find($request->id);

            if ($facility) {
                $activeWebContent = $facility->webContents()->where('is_active', true)->first();
                $sections = [];
                $sectionVariances = [];
                $layoutTemplate = 'default-template';
                
                if ($activeWebContent) {
                    $rawSections = $activeWebContent->sections;
                    if (is_string($rawSections)) {
                        $sections = json_decode($rawSections, true) ?: [];
                    } elseif (is_array($rawSections)) {
                        $sections = $rawSections;
                    } elseif ($rawSections instanceof \Illuminate\Support\Collection) {
                        $sections = $rawSections->toArray();
                    } else {
                        $sections = (array) $rawSections;
                    }
                     $sectionVariances = is_array($activeWebContent->variances) ? $activeWebContent->variances : (json_decode($activeWebContent->variances, true) ?: []);
                     $layoutTemplate = $activeWebContent->layout_template;
                }

                $aboutMenuItems = collect(['about', 'services', 'testimonials'])
                    ->filter(fn($section) => !empty($sections) && in_array($section, $sections));
                $roomsMenuItems = collect(['news', 'gallery'])
                    ->filter(fn($section) => !empty($sections) && in_array($section, $sections));
                $contactMenuItems = collect(['contact', 'faqs', 'resources', 'careers'])
                    ->filter(fn($section) => !empty($sections) && in_array($section, $sections));

                $faqs = FacilityDataHelper::getFaqs($facility);
                $categories = $faqs->pluck('category')->filter()->unique()->values();
                $testimonials = FacilityDataHelper::getTestimonials($facility);
                $services = FacilityDataHelper::getServices($facility);
                $newsItems = FacilityDataHelper::getFormattedNews($facility);
                $colors = FacilityDataHelper::getColors($facility);
        
                return view('welcome', [
                    'facility' => $facility,
                    'activeSections' => is_array($sections) ? $sections : [],
                    'layoutTemplate' => $layoutTemplate,
                    'sections' => $sections,
                    'sectionVariances' => $sectionVariances,
                    'services' => $services,
                    'faqs' => $faqs,
                    'categories' => $categories,
                    'testimonials' => $testimonials,
                    'primary' => $colors['primary'],
                    'secondary' => $colors['secondary'],
                    'accent' => $colors['accent'],
                    'neutral_light' => $colors['neutral_light'],
                    'neutral_dark' => $colors['neutral_dark'],
                    'newsItems' => $newsItems,
                    'aboutMenuItems' => $aboutMenuItems,
                    'roomsMenuItems' => $roomsMenuItems,
                    'contactMenuItems' => $contactMenuItems,
                ]);
            }
        }
    }
}