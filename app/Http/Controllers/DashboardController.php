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
        }
        else if ($user->hasRole('manager')) {
            // Facility admin dashboard
            $facility = $user->facility;
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

    public function facility($id)
    {
        $facility = Facility::findOrFail($id);
        $activeWebContent = $facility->webContents()->where('is_active', true)->first();

        $sections = [];
        $sectionVariances = [];
        $layoutTemplate = '';
        if ($activeWebContent) {
            // Get sections directly from web_contents table
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

        // Ensure all sections have a variance (default if missing)
        foreach ($sections as $section) {
            if (!isset($sectionVariances[$section])) {
                $sectionVariances[$section] = 'default';
            }
        }

        $facility->layout_template = $layoutTemplate;
        $facility->layout_sections = $sections ? json_encode($sections) : json_encode([]);
        $facility->active_sections = is_array($sections) ? $sections : [];

        $faqs = FacilityDataHelper::getFaqs($facility);
        $categories = $faqs->pluck('category')->filter()->unique()->values();
        $testimonials = FacilityDataHelper::getTestimonials($facility);
        $services = FacilityDataHelper::getServices($facility);
        
        $newsItems = FacilityDataHelper::getFormattedNews($facility);

        $colors = FacilityDataHelper::getColors($facility);

        return view('welcome', [
            'facility' => $facility,
            'active_sections' => is_array($sections) ? $sections : [],
            'layoutTemplate' => $facility->layout_template ?? 'default-template',
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'faqs' => $faqs,
            'categories' => $categories,
            'testimonials' => $testimonials,
            'primary' => $colors['primary'],
            'secondary' => $colors['secondary'],
            'accent' => $colors['accent'],
            'services' => $services,
            'newsItems' => $newsItems
        ]);
    }

    
}