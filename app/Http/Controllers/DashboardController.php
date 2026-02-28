<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $user = Auth::user();
        // Recent Activity Data
        $lastUpdated = $user->updated_at;

        // Determine which dashboard view to show
        $routeName = request()->route()->getName();
        if ($routeName === 'admin.dashboard.index' && $user->hasRole('admin')) {
            // Admin dashboard view (with admin sidebar and widgets)
            $newFacilitiesCount = Facility::where('created_at', '>=', now()->subWeek())->count();
            $newFaqsCount = Faq::where('created_at', '>=', now()->subWeek())->count();
            $facilities = Facility::all();
            $facilitiesByState = $facilities->groupBy('state');
            return view('admin.dashboard.index', [
                'lastUpdated' => $lastUpdated,
                'newFacilitiesCount' => $newFacilitiesCount,
                'newFaqsCount' => $newFaqsCount,
                'facilitiesByState' => $facilitiesByState,
                'facilities' => $facilities,
            ]);
        }

        $roleStats = null;
        $roleFacility = null;
        if ($user->hasRole(['hrrd', 'facility-admin', 'facility-dsd'])) {
            $facilityId = null;
            if ($user->hasRole(['facility-admin', 'facility-dsd'])) {
                $facilityId = $user->facility_id;
                $roleFacility = $user->facility;
            }

            $jobApplicationsQuery = JobApplication::query();
            if ($facilityId) {
                $jobApplicationsQuery->whereHas('jobOpening', function ($query) use ($facilityId) {
                    $query->where('facility_id', $facilityId);
                });
            }

            $applicantsTotal = (clone $jobApplicationsQuery)->count();
            $applicantsToday = (clone $jobApplicationsQuery)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            $applicantsThisWeek = (clone $jobApplicationsQuery)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            $applicantUserIds = JobApplication::query()
                ->select('user_id')
                ->whereNotNull('user_id')
                ->distinct();
            if ($facilityId) {
                $applicantUserIds->whereHas('jobOpening', function ($query) use ($facilityId) {
                    $query->where('facility_id', $facilityId);
                });
            }

            $checklistBaseQuery = EmployeeChecklist::query()
                ->whereIn('user_id', $applicantUserIds);

            $submittedForms = (clone $checklistBaseQuery)
                ->whereNotNull('submitted_at')
                ->count();
            $pendingReviews = (clone $checklistBaseQuery)
                ->where('status', 'submitted')
                ->count();
            $returnedForms = (clone $checklistBaseQuery)
                ->where('status', 'returned')
                ->count();
            $completedForms = (clone $checklistBaseQuery)
                ->where('status', 'completed')
                ->count();

            $jobOpeningsQuery = JobOpening::query()->where('active', true);
            if ($facilityId) {
                $jobOpeningsQuery->where('facility_id', $facilityId);
            }
            $openJobOpenings = $jobOpeningsQuery->count();

            $roleStats = [
                'applicants_total' => $applicantsTotal,
                'applicants_today' => $applicantsToday,
                'applicants_week' => $applicantsThisWeek,
                'submitted_forms' => $submittedForms,
                'pending_reviews' => $pendingReviews,
                'returned_forms' => $returnedForms,
                'completed_forms' => $completedForms,
                'open_job_openings' => $openJobOpenings,
            ];
        }

        // User-specific data
        $jobApplication = $user->jobApplications()->where('status', 'pre-employment')->first();
        $hasPreEmployment = $jobApplication !== null;
        
        // Pre-employment checklist stats
        $checklistStats = null;
        if ($hasPreEmployment) {
            $checklistItems = $user->employeeChecklists;
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
        if ($user->created_at->gt(now()->subDays(7))) {
            $recentActivity[] = [
                'icon' => 'fa-user-plus',
                'color' => 'blue',
                'message' => 'Account created ' . $user->created_at->diffForHumans()
            ];
        }
        $recentActivity[] = [
            'icon' => 'fa-clock',
            'color' => 'gray',
            'message' => 'Last login ' . ($user->updated_at ? $user->updated_at->diffForHumans() : 'recently')
        ];

        // Default: user dashboard
        $preEmployment = $jobApplication; // Alias for clarity in view
        return view('dashboard', [
            'lastUpdated' => $lastUpdated,
            'hasPreEmployment' => $hasPreEmployment,
            'jobApplication' => $jobApplication,
            'preEmployment' => $preEmployment,
            'checklistStats' => $checklistStats,
            'recentActivity' => $recentActivity,
            'readOnly' => false,
            'viewingUser' => $user,
            'roleStats' => $roleStats,
            'roleFacility' => $roleFacility,
        ]);
    }

    /**
     * View another user's dashboard (for authorized staff)
     */
    public function showUserDashboard($userId)
    {
        $currentUser = Auth::user();
        
        // Check if current user has permission to view employee/applicant information
        if (!$currentUser->hasRole(['admin', 'hrrd', 'facility-admin', 'facility-dsd'])) {
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