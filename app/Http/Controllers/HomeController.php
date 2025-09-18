<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $f = request()->query('f');
        if (!$f) {
            $f = 1;
        }

        $facility = \App\Models\Facility::find($f);
        $colors = [
            'primary' => '#059669',
            'secondary' => '#064E3B',
            'accent' => '#FACC15'
        ];

        $activeWebContent = null;
        $sections = [];
        $layoutTemplate = 'default-template';

        if ($facility) {
            // Don't convert to array, keep as Eloquent model
            $colors = [
                'primary' => $facility->primary_color ?? '#059669',
                'secondary' => $facility->secondary_color ?? '#064E3B',
                'accent' => $facility->accent_color ?? '#FACC15'
            ];

            // Now you can call relationships
            $activeWebContent = $facility->webcontents()->where('is_active', true)->first();

            if ($activeWebContent && $activeWebContent->sections) {
                if (is_string($activeWebContent->sections)) {
                    $sections = json_decode($activeWebContent->sections, true) ?? [];
                } elseif (is_array($activeWebContent->sections)) {
                    $sections = $activeWebContent->sections;
                }
            }

            $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
        }

    // Fetch FAQ data for dynamic FAQ section
    $faqs = \App\Models\Faq::all();
    $categories = \App\Models\Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();
    return view('welcome', compact('facility', 'colors', 'layoutTemplate', 'sections', 'faqs', 'categories'));
    }
}
