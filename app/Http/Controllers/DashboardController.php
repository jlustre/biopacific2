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
        if ($user->hasRole('admin')) {
            // Admin dashboard
            $totalFacilities = Facility::count();
            $activeFacilities = Facility::where('is_active', true)->count();
            $inactiveFacilities = $totalFacilities - $activeFacilities;
            $recentFacilities = Facility::orderBy('created_at', 'desc')->take(5)->get();

            // Debugging log for super admin role
            Log::debug('Super admin accessing dashboard', [
                'totalFacilities' => $totalFacilities,
                'activeFacilities' => $activeFacilities,
                'inactiveFacilities' => $inactiveFacilities,
                'recentFacilities' => $recentFacilities->pluck('id')->toArray(),
            ]);
        }
        else if ($user->hasRole('manager')) {
            // Facility admin dashboard
            $facility = $user->facility;

            // Debugging log for facility retrieval
            Log::debug('Facility retrieved for manager role', [
                'facility_id' => $facility ? $facility->id : null,
                'facility_slug' => $facility ? $facility->slug : null,
            ]);

            if ($facility) {
                $activeWebContent = $facility->webContents()->where('is_active', true)->first();
                $sections = [];
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
                }
                $faqs = FacilityDataHelper::getFaqs($facility);
                $categories = $faqs->pluck('category')->filter()->unique()->values();
                $testimonials = FacilityDataHelper::getTestimonials($facility);
                $services = FacilityDataHelper::getServices($facility);
                $newsItems = FacilityDataHelper::getFormattedNews($facility);

                $colors = FacilityDataHelper::getColors($facility);

                // Ensure services are defined with a fallback
                $services = property_exists($facility, 'services') && is_array($facility->services) ? $facility->services : [];

                return view('welcome', [
                    'facility' => $facility,
                    'active_sections' => is_array($sections) ? $sections : [],
                    'layoutTemplate' => $activeWebContent ? $activeWebContent->layout_template : 'default-template',
                    'sections' => $sections,
                    'sectionVariances' => [],
                    'faqs' => $faqs,
                    'categories' => $categories,
                    'testimonials' => $testimonials,
                    'primary' => $colors['primary'],
                    'secondary' => $colors['secondary'],
                    'accent' => $colors['accent'],
                    'services' => $services,
                    'newsItems' => $newsItems
                ]);
            } else {
                // No facility associated with this admin
                return view('dashboard')->with('error', 'No facility associated with your account.');
            }
        } else {
            // Other roles or no specific role
            return view('dashboard')->with('error', 'You do not have access to a specific dashboard.');
        }               
        // General dashboard for authenticated users
        return view('dashboard');
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
                     $sectionVariances = is_array($activeWebContent->variances) ? $activeWebContent->variances : json_decode($activeWebContent->variances, true);
                     $layoutTemplate = $activeWebContent->layout_template;
                }

                $aboutMenuItems = collect(['about', 'services', 'testimonials'])
                    ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
                $roomsMenuItems = collect(['news', 'gallery'])
                    ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
                $contactMenuItems = collect(['contact', 'faqs', 'resources', 'careers'])
                    ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));

                $faqs = FacilityDataHelper::getFaqs($facility);
                $categories = $faqs->pluck('category')->filter()->unique()->values();
                $testimonials = FacilityDataHelper::getTestimonials($facility);
                $services = FacilityDataHelper::getServices($facility);
                $newsItems = FacilityDataHelper::getFormattedNews($facility);
                $colors = FacilityDataHelper::getColors($facility);
                $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        
                return view('welcome', [
                    'facility' => $facility,
                    'active_sections' => is_array($sections) ? $sections : [],
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
                    'newsItems' => $newsItems,
                    'aboutMenuItems' => $aboutMenuItems,
                    'roomsMenuItems' => $roomsMenuItems,
                    'contactMenuItems' => $contactMenuItems,
                ]);
            }
        }
    }
}