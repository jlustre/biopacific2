<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Services\MemberDashboardService;
use App\Services\PersonalProfilePanelsService;
use Illuminate\Http\Request;
use App\Helpers\FacilityDataHelper;
use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\Upload;
use App\Support\FacilityShutdown;
use App\Support\SelectedFacility;
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

        // Regional HR landing (facility picker + portal links)
        if ($routeName === 'admin.dashboard.index' && $hasRole(['rdhr'])) {
            return view('dashboard.hr-portal-dashboard');
        }

        // Personal work-queue dashboard (all roles — facility leaders, DON, staff, etc.)
        if (in_array($routeName, ['user.dashboard', 'dashboard.index'], true)) {
            return $this->memberDashboard($user);
        }

        return $this->memberDashboard($user);
    }

    public function facilityDashboard(?Facility $facility = null)
    {
        if (! $facility) {
            $sessionFacility = SelectedFacility::model();
            if ($sessionFacility) {
                return redirect()->route('member.facility.dashboard', ['facility' => $sessionFacility->getRouteKey()]);
            }
        }

        return $this->renderFacilityPortalPage($facility, 'operations', 'facility-dashboard', 'Facility Dashboard');
    }

    public function hrManagementHub(?Facility $facility = null)
    {
        if (! $facility) {
            $sessionFacility = SelectedFacility::model();
            if ($sessionFacility) {
                return redirect()->route('user.hr-portal', ['facility' => $sessionFacility->getRouteKey()]);
            }
        }

        return $this->renderFacilityPortalPage($facility, 'hr_hub', 'hr-portal', 'HR Management');
    }

    protected function renderFacilityPortalPage(?Facility $facility, string $profile, string $portalActive, string $pageTitlePrefix)
    {
        $user = Auth::user();
        $roleDashboard = app(\App\Services\RoleMemberDashboardService::class);

        if (! $roleDashboard->canAccessFacilityDashboard($user)) {
            abort(403, 'Unauthorized: Facility dashboard access is limited to leadership roles.');
        }

        $service = app(\App\Services\FacilityDashboardService::class);
        $facilities = $service->facilitiesForUser($user);
        $facilitySwitchRoute = $profile === 'hr_hub' ? 'user.hr-portal' : 'member.facility.dashboard';

        if (! $facility) {
            $facility = SelectedFacility::model();
        }

        if (! $facility && $facilities->count() === 1) {
            $facility = $facilities->first();
        }

        if (! $facility && $user->facility_id && ! SelectedFacility::id()) {
            $facility = Facility::find($user->facility_id);
        }

        if ($facility) {
            $hasRouteFacility = request()->route('facility') !== null;
            if ($hasRouteFacility || ! session()->has(SelectedFacility::SESSION_ID_KEY)) {
                SelectedFacility::remember($facility);
            }
        }

        if (!$facility) {
            return view('dashboard.member.facility-select', array_merge($this->memberPortalContext($user), [
                'facilities' => $facilities,
                'facilitySwitchRoute' => $facilitySwitchRoute,
                'organizationStats' => $roleDashboard->organizationOverviewStatsForUser($user),
                'portalActive' => $portalActive,
                'portalTitle' => 'Select Facility | Bio Pacific',
                'portalEyebrow' => $profile === 'hr_hub' ? 'HR Management' : 'Facility Portal',
                'portalPageTitle' => 'Select a facility',
                'showPortalSearch' => false,
                'showPortalNotifications' => true,
            ]));
        }

        $payload = $service->build($user, $facility, $profile);

        return view('dashboard.member.facility-dashboard', array_merge($this->memberPortalContext($user), $payload, [
            'facilities' => $facilities,
            'facilitySwitchRoute' => $facilitySwitchRoute,
            'todayLabel' => now()->format('l, F j, Y'),
            'portalActive' => $portalActive,
            'portalTitle' => ($facility->name ?? 'Facility') . ' | Bio Pacific',
            'portalEyebrow' => $profile === 'hr_hub' ? 'HR Management' : 'Facility Portal',
            'portalPageTitle' => $profile === 'hr_hub' ? 'HR Management' : 'Facility Dashboard',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    protected function memberDashboard($user)
    {
        $context = $this->memberPortalContext($user);
        $roleDashboard = app(\App\Services\RoleMemberDashboardService::class)->build($user);

        return view('dashboard.member.index', array_merge($context, $roleDashboard, [
            'todayLabel' => now()->format('l, F j, Y'),
            'portalActive' => 'dashboard',
            'portalTitle' => 'Bio Pacific HR Management | My Dashboard',
            'portalEyebrow' => 'My Dashboard',
            'portalPageTitle' => 'My Dashboard',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberDocuments(Request $request)
    {
        $user = Auth::user();
        $documentsData = app(MemberDashboardService::class)->buildDocumentsPage($user, $request);
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

    public function memberTeamDocumentView(Request $request, BPEmployee $employee, Upload $upload)
    {
        return $this->serveTeamDocument($request, $employee, $upload, download: false);
    }

    public function memberTeamDocumentDownload(Request $request, BPEmployee $employee, Upload $upload)
    {
        return $this->serveTeamDocument($request, $employee, $upload, download: true);
    }

    protected function serveTeamDocument(Request $request, BPEmployee $employee, Upload $upload, bool $download)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if ($upload->employee_num !== $employee->employee_num) {
            abort(404);
        }

        $verification = app(\App\Services\EmployeeDocumentVerificationService::class);
        if (! $verification->actorCanViewEmployeeDocumentHistory($user, $employee)) {
            abort(403, 'You do not have access to this employee\'s documents.');
        }

        $filePath = storage_path('app/public/' . $upload->file_path);
        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        if ($download) {
            return response()->download($filePath, $upload->original_filename);
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $disposition = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], true)
            ? 'inline'
            : 'attachment';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $upload->original_filename . '"',
        ]);
    }


    public function memberTasks()
    {
        $user = Auth::user();

        return view('dashboard.member.tasks', array_merge($this->memberPortalContext($user), [
            'portalActive' => 'tasks',
            'portalTitle' => 'My Tasks | Bio Pacific HR Management',
            'portalEyebrow' => 'My Tasks',
            'portalPageTitle' => 'My Tasks',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberMessages(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $messagesService = app(\App\Services\MemberMessages\MemberMessagesService::class);
        $filters = $messagesService->filtersFor($user);
        $source = $request->string('source')->toString() ?: 'all';
        $allowed = collect($filters)->pluck('key')->all();
        if (! in_array($source, $allowed, true)) {
            $source = 'all';
        }

        return view('dashboard.member.messages', array_merge($this->memberPortalContext($user), [
            'portalActive' => 'messages',
            'portalTitle' => 'My Messages | Bio Pacific HR Management',
            'portalEyebrow' => 'Inbox',
            'portalPageTitle' => 'My Messages',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'messageFilters' => $filters,
            'messages' => $messagesService->allFor($user, $source),
            'activeMessageSource' => $source,
        ]));
    }

    public function memberCertifications()
    {
        $user = Auth::user();
        $certificationsData = app(MemberDashboardService::class)->buildCertificationsPage($user);

        return view('dashboard.member.certifications', array_merge($this->memberPortalContext($user), $certificationsData, [
            'portalActive' => 'certifications',
            'portalTitle' => 'My Credentials | Bio Pacific HR Management',
            'portalEyebrow' => 'My Credentials',
            'portalPageTitle' => 'My Credentials',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberChecklists(Request $request)
    {
        $user = Auth::user();
        $periodId = $request->filled('assessment_period_id')
            ? (int) $request->input('assessment_period_id')
            : null;
        $trainingsData = app(MemberDashboardService::class)->buildTrainingsPage($user, $periodId);
        $isFacilityTrainingsAdmin = $user->hasRole(['facility-admin', 'facility-dsd'])
            && !empty($trainingsData['facilityTrainingsReport']);

        return view('dashboard.member.checklists', array_merge($this->memberPortalContext($user), $trainingsData, [
            'portalActive' => 'checklists',
            'portalTitle' => 'My Checklists | Bio Pacific HR Management',
            'portalEyebrow' => 'Personal Checklists',
            'portalPageTitle' => 'My Checklists',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'isFacilityTrainingsAdmin' => $isFacilityTrainingsAdmin,
        ]));
    }

    /** @deprecated Use memberChecklists() */
    public function memberTrainings(Request $request)
    {
        return $this->memberChecklists($request);
    }

    public function memberNewsEvents()
    {
        $user = Auth::user();
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment.position', 'currentAssignment.facility'])
            : null;
        $facility = $this->resolveMemberFacility($user, $employee);
        $newsItems = $facility
            ? FacilityDataHelper::getNews($facility, 'portal')
            : News::query()
                ->where('status', true)
                ->where('is_global', true)
                ->visibleOn('portal')
                ->orderByDesc('published_at')
                ->get();

        $newsItems->loadMissing('facility');

        $articles = $newsItems->map(function (News $item) {
            $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $item->content)));
            $summary = filled($item->summary) ? (string) $item->summary : \Illuminate\Support\Str::limit($plain, 180);

            return [
                'id' => $item->id,
                'title' => (string) $item->title,
                'summary' => $summary,
                'content' => (string) $item->content,
                'excerpt' => \Illuminate\Support\Str::limit($summary, 140),
                'image_url' => $item->image ? asset('storage/'.$item->image) : null,
                'is_global' => (bool) $item->is_global,
                'scope' => $item->is_global ? 'company' : 'facility',
                'scope_label' => $item->is_global ? 'Company-wide' : 'Facility',
                'facility_name' => $item->is_global
                    ? 'Bio Pacific'
                    : ($item->facility?->name ?? 'Facility'),
                'published_at' => optional($item->published_at)?->toIso8601String(),
                'published_label' => optional($item->published_at)?->timezone(config('app.timezone'))->format('M j, Y'),
                'published_relative' => optional($item->published_at)?->timezone(config('app.timezone'))->diffForHumans(),
            ];
        })->values();

        $companyCount = $articles->where('is_global', true)->count();
        $facilityCount = $articles->where('is_global', false)->count();

        return view('dashboard.member.news-events', array_merge($this->memberPortalContext($user), [
            'newsItems' => $newsItems,
            'newsArticles' => $articles,
            'newsCompanyCount' => $companyCount,
            'newsFacilityCount' => $facilityCount,
            'newsFacilityLabel' => $facility?->name,
            'portalActive' => 'facility-news',
            'portalTitle' => 'News/Events | Bio Pacific',
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => 'News/Events',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function memberProfile(Request $request)
    {
        $user = $request->user()->fresh();
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee([
                'currentAssignment.position.reportsToPosition',
                'currentAssignment.reportsToPosition',
                'currentAssignment.facility',
                'currentAssignment.department',
                'phone',
                'phones',
                'address',
            ])
            : null;

        if ($employee) {
            $employee->refresh();
            $employee->loadMissing([
                'currentAssignment.position.reportsToPosition',
                'currentAssignment.reportsToPosition',
                'currentAssignment.facility',
                'currentAssignment.department',
                'phone',
                'phones',
                'address',
            ]);
        }

        $portalContext = $this->memberPortalContext($user);

        $context = array_merge($portalContext, [
            'employee' => $employee,
            'positionTitle' => $employee?->currentAssignment?->position?->title ?? 'Team Member',
            'departmentName' => $employee?->currentAssignment?->department?->name ?? '—',
            'reportsToName' => $this->resolveReportsToName($employee),
            'facilityName' => $employee?->currentAssignment?->facility?->name
                ?? ($portalContext['facility']?->name ?? '—'),
        ]);

        $panels = app(PersonalProfilePanelsService::class);

        return view('dashboard.member.profile', array_merge($context, [
            'portalActive' => 'profile',
            'portalTitle' => 'Bio Pacific HR Management | My Profile',
            'portalEyebrow' => 'Personal Portal',
            'portalPageTitle' => 'My Profile',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'profileComplete' => $this->calculatePersonalProfileComplete($user, $employee),
            'personalPhone' => $employee?->displayPhoneNumber(),
            'personalAddress' => $this->formatPersonalAddress($employee?->address),
            'dateOfBirth' => $employee?->formattedDateOfBirth(),
            'legalName' => $employee
                ? trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name])))
                : null,
            'emailVerified' => $user->hasVerifiedEmail(),
            'lastUpdated' => $this->formatProfileLastUpdated($user, $employee),
            'upcomingExpirations' => $panels->upcomingExpirations($user, $employee),
            'profileRecognitions' => $panels->recognitions($user, $employee),
            'emergencyContacts' => $user->emergencyContacts()->get(),
            'profileHrAssessment' => app(\App\Services\MemberProfileHrReviewService::class)->assess($user, $employee),
        ]));
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

    public function facility(Request $request, int|string $id)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole(['admin', 'super-admin', 'facility-admin', 'facility-dsd', 'rdhr', 'don'])) {
            abort(403);
        }

        $facility = Facility::findOrFail($id);

        if ($response = FacilityShutdown::responseFor($facility)) {
            return $response;
        }

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
            $sectionVariances = is_array($activeWebContent->variances)
                ? $activeWebContent->variances
                : (json_decode($activeWebContent->variances, true) ?: []);
            $layoutTemplate = FacilityDataHelper::resolveLayoutTemplate(
                $activeWebContent->layout_template,
                $facility,
            );
        }

        $aboutMenuItems = collect(['about', 'services', 'testimonials'])
            ->filter(fn ($section) => !empty($sections) && in_array($section, $sections));
        $roomsMenuItems = collect(['news', 'gallery'])
            ->filter(fn ($section) => !empty($sections) && in_array($section, $sections));
        $contactMenuItems = collect(['contact', 'faqs', 'resources', 'careers'])
            ->filter(fn ($section) => !empty($sections) && in_array($section, $sections));

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