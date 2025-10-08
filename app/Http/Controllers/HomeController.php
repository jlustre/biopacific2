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
            // Prefer color scheme if set
            $colorScheme = null;
            if (!empty($facility->color_scheme_id)) {
                $colorScheme = \App\Models\ColorScheme::find($facility->color_scheme_id);
            }
            $colors = [
                'primary' => $colorScheme->primary_color ?? $facility->primary_color ?? '#059669',
                'secondary' => $colorScheme->secondary_color ?? $facility->secondary_color ?? '#064E3B',
                'accent' => $colorScheme->accent_color ?? $facility->accent_color ?? '#FACC15'
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
    $faqs = collect(); // Empty collection as default
    $categories = collect(); // Empty collection as default
    
    if ($facility) {
        // Get FAQs available to this facility (both facility-specific and default FAQs)
        $faqs = \App\Models\Faq::availableForFacility($facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get categories from the retrieved FAQs
        $categories = $faqs->pluck('category')->filter()->unique()->values();
    }
    
    // Fetch testimonials for the facility
    $testimonials = collect(); // Empty collection as default
    if ($facility) {
        $testimonials = \App\Models\Testimonial::where('facility_id', $facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    $services = \App\Models\Service::orderBy('title')->get();
    
    return view('welcome', [
        'facility' => $facility,
        'layoutTemplate' => $layoutTemplate,
        'sections' => $sections,
        'faqs' => $faqs,
        'categories' => $categories,
        'testimonials' => $testimonials,
        'services' => $services,
        'primary' => $colors['primary'],
        'secondary' => $colors['secondary'],
        'accent' => $colors['accent'],
        'newsItems' => $facility && method_exists($facility, 'news') ? $facility->news()->where('status', true)->orderBy('published_at', 'desc')->get()->map(function($item) {
            return [
                'title' => $item->title,
                'desc' => $item->content,
                'date' => $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('M d') : '',
                'year' => $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('Y') : '',
                'type' => $item->scope,
                'color' => 'bg-blue-500',
            ];
        })->toArray() : []
    ]);
    }
}
