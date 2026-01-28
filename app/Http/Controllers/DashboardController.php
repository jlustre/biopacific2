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
        $newFacilitiesCount = Facility::where('created_at', '>=', now()->subWeek())->count();
        $newFaqsCount = Faq::where('created_at', '>=', now()->subWeek())->count();

        // Determine which dashboard view to show
        $routeName = request()->route()->getName();
        if ($routeName === 'admin.dashboard.index' && $user->hasRole('admin')) {
            // Admin dashboard view (with admin sidebar and widgets)
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
        // Default: user dashboard
        return view('dashboard', [
            'lastUpdated' => $lastUpdated,
            'newFacilitiesCount' => $newFacilitiesCount,
            'newFaqsCount' => $newFaqsCount,
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