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
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $layoutData = FacilityDataHelper::getLayoutData($activeWebContent);
        $sections = $layoutData['sections'];
        $sectionVariances = $layoutData['sectionVariances'];
        $layoutTemplate = $layoutData['layoutTemplate'];

        // Ensure all sections have a variance (default if missing)
        foreach ($sections as $section) {
            if (!isset($sectionVariances[$section])) {
                $sectionVariances[$section] = 'default';
            }
        }

        $facility->layout_template = $layoutTemplate;
        $facility->layout_sections = $sections ? json_encode($sections) : json_encode([]);

        $faqs = FacilityDataHelper::getFaqs($facility);
        $categories = $faqs->pluck('category')->filter()->unique()->values();
        $testimonials = FacilityDataHelper::getTestimonials($facility);
        $services = FacilityDataHelper::getServices($facility);
        $newsItems = FacilityDataHelper::getFormattedNews($facility);

        $colors = FacilityDataHelper::getColors($facility);
         

        return view('welcome', [
            'facility' => $facility,
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