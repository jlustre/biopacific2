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

class DashboardController extends Controller
{
    public function index()
    {
        // Get all active facilities
        $facilities = Facility::where('is_active', true)
                                ->orderBy('name')
                                ->get();

        // Group facilities by state for better organization
        $facilitiesByState = $facilities->groupBy('state');

        return view('admin.dashboard.index', compact('facilities', 'facilitiesByState'));
    }

    public function facility($id)
    {
        $facility = Facility::findOrFail($id);
        $activeWebContent = $facility->webContents()->where('is_active', true)->first();
        // Debug: log raw sections from web_contents
        if ($activeWebContent) {
            \Log::info('RAW web_contents.sections', ['sections' => $activeWebContent->sections]);
        }
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