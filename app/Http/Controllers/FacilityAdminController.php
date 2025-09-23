<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FacilityAdminController extends Controller
{
    public function index()
    {
    $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.index', compact('facilities'));
    }

    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        $faqs = \App\Models\Faq::all();
        dd($faqs);
        $categories = \App\Models\Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();
        // Get active webcontent and layout info for welcome view
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $sections = [];
        $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $sections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $sections = $activeWebContent->sections;
            }
        }
        return view('welcome', compact('facility', 'layoutTemplate', 'sections', 'faqs', 'categories'));
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        $facilities = Facility::orderBy('name')->get(); // <-- Add this line
        $layoutTemplates = ['default-template', 'layout2', 'layout3', 'layout4'];

        // Get all webcontents for reference
        $webContents = $facility->webcontents()->get();

        // Get the active webcontent directly from the database
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();

        // Get the active layout template and sections
        $selectedLayoutTemplate = $activeWebContent ? $activeWebContent->layout_template : null;
        $selectedSections = [];

        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $selectedSections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $selectedSections = $activeWebContent->sections;
            }
        }

        $faqs = \App\Models\Faq::all();
        $categories = \App\Models\Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();
        return view('admin.facilities.edit', compact(
            'facility',
            'facilities', // <-- Add this to compact
            'layoutTemplates',
            'webContents',
            'activeWebContent',
            'selectedLayoutTemplate',
            'selectedSections',
            'faqs',
            'categories'
        ));
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'domain' => 'required|string|max:255|unique:facilities,domain,' . $id,
            'subdomain' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'beds' => 'nullable|integer|min:1',
            'hours' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'headline' => 'nullable|string|max:255',
            'subheadline' => 'nullable|string|max:500',
            'about_text' => 'nullable|string',
            'hero_image_url' => 'nullable|url|max:500',
            'about_image_url' => 'nullable|url|max:500',
            'logo_url' => 'nullable|url|max:500',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'location_map' => 'nullable|string|max:1000',
            // 'webcontents' => 'array',
            'layout_template' => 'required|string|in:default-template,layout2,layout3,layout4',
            'sections' => 'array',
            'variances' => 'array', // <-- Added validation for variances
        ]);

        $domain = $validated['domain'];
        if (empty($domain)) {
            $slug = Str::slug($validated['name']);
            $domain = $slug . '.example.com';
        }

        $facility->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'tagline' => $validated['tagline'],
            'domain' => $domain,
            'subdomain' => $validated['subdomain'],
            'is_active' => $request->has('is_active'),
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'beds' => $validated['beds'],
            'hours' => $validated['hours'],
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'accent_color' => $validated['accent_color'],
            'headline' => $validated['headline'],
            'subheadline' => $validated['subheadline'],
            'about_text' => $validated['about_text'],
            'hero_image_url' => $validated['hero_image_url'],
            'about_image_url' => $validated['about_image_url'],
            'logo_url' => $validated['logo_url'],
            'facebook' => $validated['facebook'],
            'twitter' => $validated['twitter'],
            'instagram' => $validated['instagram'],
            'location_map' => $this->formatLocationMap($validated['location_map'] ?? null),
        ]);

        $layoutTemplate = $validated['layout_template'];
        $sections = $validated['sections'] ?? [];
        $variances = $validated['variances'] ?? [];

        // Inactivate all templates for this facility except the selected one
        DB::table('web_contents')
            ->where('facility_id', $facility->id)
            ->where('layout_template', '!=', $layoutTemplate)
            ->update(['is_active' => false, 'sections' => null, 'variances' => null]);

        // Activate the selected template and update its sections and variances
        DB::table('web_contents')
            ->updateOrInsert(
                [
                    'facility_id' => $facility->id,
                    'layout_template' => $layoutTemplate,
                ],
                [
                    'sections' => json_encode(array_keys($sections)),
                    'variances' => json_encode($variances),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        return redirect()->route('admin.facilities.edit', $facility->id)
                        ->with('success', 'Facility updated successfully!');
    }

    // public function preview(Facility $facility)
    // {
    //     // Get the active webcontent for this facility
    //     $activeWebContent = $facility->webcontents()->where('is_active', true)->first();

    //     // Use the active template and sections, fallback if not found
    //     $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';
    //     $sections = [];

    //     if ($activeWebContent && $activeWebContent->sections) {
    //         if (is_string($activeWebContent->sections)) {
    //             $sections = json_decode($activeWebContent->sections, true) ?? [];
    //         } elseif (is_array($activeWebContent->sections)) {
    //             $sections = $activeWebContent->sections;
    //         }
    //     }


    //     return view('facilities.preview', compact('facility', 'layoutTemplate', 'sections'));
    // }

    private function formatLocationMap($locationMap)
    {
        if ($locationMap && preg_match('/^https?:\/\/maps\.google\.com\/maps\?q=/', $locationMap)) {
            $locationMap = preg_replace('/^https?:\/\/maps\.google\.com\/maps\?q=/', 'https://www.google.com/maps?q=', $locationMap);
            if (strpos($locationMap, '&output=embed') === false) {
                $locationMap .= '&output=embed';
            }
        }
        return $locationMap;
    }
}