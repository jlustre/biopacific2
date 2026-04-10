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
        $routeName = request()->route()->getName();

        // Helper fallback for hasRole
        $hasRole = function($role) use ($user) {
            return method_exists($user, 'hasRole') ? $user->hasRole($role) : false;
        };

        // Admin dashboard main view
        if ($routeName === 'admin.dashboard.index' && $hasRole('admin')) {
            $facilities = \App\Models\Facility::all();
            $facilitiesByState = $facilities->groupBy('state');
            $recentActivities = [];
            return view('admin.dashboard.index', compact('facilities', 'facilitiesByState', 'recentActivities'));
        }

        // HR Portal dashboard for hrrd, facility-admin, facility-dsd
        if ($routeName === 'admin.dashboard.index' && $hasRole(['hrrd', 'facility-admin', 'facility-dsd'])) {
            return view('dashboard.hr-portal-dashboard');
        }

        // Member dashboard placeholder (for basic users)
        if ($routeName === 'user.dashboard' && $hasRole('user')) {
            return view('dashboard.member-placeholder');
        }

        // Fallback for any other roles/routes
        return view('dashboard.member-placeholder')->with('message', 'No dashboard available for your role.');
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