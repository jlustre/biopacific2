<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;

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
        $sections = [];
        $sectionVariances = [];

        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $sections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $sections = $activeWebContent->sections;
            }
        }

        if ($activeWebContent && isset($activeWebContent->variances)) {
            if (is_string($activeWebContent->variances)) {
                $sectionVariances = json_decode($activeWebContent->variances, true) ?? [];
            } elseif (is_array($activeWebContent->variances)) {
                $sectionVariances = $activeWebContent->variances;
            }
        }

        foreach ($sections as $section) {
            if (!isset($sectionVariances[$section])) {
                $sectionVariances[$section] = 'default';
            }
        }

        $facility->layout_template = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
        $facility->layout_sections = $sections
            ? json_encode($sections)
            : json_encode([]);

        // Fetch FAQ data for dynamic FAQ section
        $faqs = \App\Models\Faq::availableForFacility($facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get categories from the retrieved FAQs
        $categories = $faqs->pluck('category')->filter()->unique()->values();

        // Fetch testimonials for the facility
        $testimonials = \App\Models\Testimonial::where('facility_id', $facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get color scheme from DB if available
        $colorScheme = null;
        if (!empty($facility->color_scheme_id)) {
            $colorScheme = \App\Models\ColorScheme::find($facility->color_scheme_id);
        }
    $primary = $colorScheme->primary_color ?? $facility->primary_color ?? '#059669';
    $secondary = $colorScheme->secondary_color ?? $facility->secondary_color ?? '#064E3B';
    $accent = $colorScheme->accent_color ?? $facility->accent_color ?? '#FACC15';
    $services = \App\Models\Service::orderBy('title')->get();
        return view('welcome', [
            'facility' => $facility,
            'layoutTemplate' => $facility->layout_template ?? 'default-template',
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'faqs' => $faqs,
            'categories' => $categories,
            'testimonials' => $testimonials,
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'services' => $services,
        ]);
    }

    
}

// Get the active webcontent for this facility
// $activeWebContent = $facility->webcontents()->where('is_active', true)->first();

// // Use the active template and sections, fallback if not found
// $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
// $sections = [];

// if ($activeWebContent && $activeWebContent->sections) {
//     if (is_string($activeWebContent->sections)) {
//         $sections = json_decode($activeWebContent->sections, true) ?? [];
//     } elseif (is_array($activeWebContent->sections)) {
//         $sections = $activeWebContent->sections;
//     }
// }


// return view('facilities.preview', compact('facility', 'layoutTemplate', 'sections'));
