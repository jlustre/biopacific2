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

        return view('dashboard.index', compact('facilities', 'facilitiesByState'));
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

        // If activeWebContent has a variances field, use it. Otherwise, default to 'default'.
        // Example: $activeWebContent->variances = ['hero' => 'modern', 'about' => 'classic', ...]
        if ($activeWebContent && isset($activeWebContent->variances)) {
            if (is_string($activeWebContent->variances)) {
                $sectionVariances = json_decode($activeWebContent->variances, true) ?? [];
            } elseif (is_array($activeWebContent->variances)) {
                $sectionVariances = $activeWebContent->variances;
            }
        }

        // Fallback: ensure every section has a variance
        foreach ($sections as $section) {
            if (!isset($sectionVariances[$section])) {
                $sectionVariances[$section] = 'default';
            }
        }

        $facility->layout_template = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
        $facility->layout_sections = $sections
            ? json_encode($sections)
            : json_encode([]);

        app()->instance('current_facility', $facility);
        view()->share('facility', $facility->toArray());
        view()->share('layoutTemplate', $facility->layout_template ?? 'default-template');
        view()->share('sections', $sections);
        view()->share('sectionVariances', $sectionVariances);

        return view('welcome');
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
