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
        $faqs = \App\Models\Faq::all();
        $categories = \App\Models\Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();

        return view('welcome', [
            'facility' => $facility,
            'layoutTemplate' => $facility->layout_template ?? 'default-template',
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'faqs' => $faqs,
            'categories' => $categories,
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
