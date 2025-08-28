<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\LayoutTemplate;
use Illuminate\Support\Str;

class FacilityAdminController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        $facilities = Facility::orderBy('name')->paginate();
=======
        $facilities = Facility::orderBy('name')->paginate(12);
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
        return view('admin.facilities.index', compact('facilities'));
    }

    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        return view('admin.facilities.show', compact('facility'));
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
<<<<<<< HEAD
        $layoutTemplates = ['default-template', 'layout2', 'layout3', 'layout4']; // Available layouts
=======
        $layoutTemplates = ['layout1', 'layout2', 'layout3', 'layout4']; // Available layouts
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

        return view('admin.facilities.edit', compact('facility', 'layoutTemplates'));
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
<<<<<<< HEAD
            'layout_template' => 'required|string|in:default-template,layout2,layout3,layout4',
=======
            'layout_template' => 'required|string|in:layout1,layout2,layout3,layout4',
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
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
<<<<<<< HEAD
            'location_map' => 'nullable|string|max:1000',
=======
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
            'sections' => 'array',
            'sections.*' => 'boolean'
        ]);

        // Ensure domain is never null - generate from slug if empty
        $domain = $validated['domain'];
        if (empty($domain)) {
            $slug = Str::slug($validated['name']);
            $domain = $slug . '.example.com';
        }

        // Handle social media links - they are individual fields in our model
        $socialData = [
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'instagram' => $request->instagram,
        ];

<<<<<<< HEAD
        // Convert Google Maps URL to embed format if needed
        $locationMap = $validated['location_map'] ?? null;
        if ($locationMap && preg_match('/^https?:\/\/maps\.google\.com\/maps\?q=/', $locationMap)) {
            $locationMap = preg_replace('/^https?:\/\/maps\.google\.com\/maps\?q=/', 'https://www.google.com/maps?q=', $locationMap);
            if (strpos($locationMap, '&output=embed') === false) {
                $locationMap .= '&output=embed';
            }
        }
=======
        // Update facility basic info
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
        $facility->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'tagline' => $validated['tagline'],
            'domain' => $domain, // Always ensure domain has a value
            'subdomain' => $validated['subdomain'],
            'is_active' => $request->has('is_active'),
            'layout_template' => $validated['layout_template'],
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
<<<<<<< HEAD
            'location_map' => $locationMap,
=======
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
        ]);

        // Update settings with active sections and social data for backward compatibility
        $settings = $facility->settings ?? [];
        $settings['social'] = array_filter($socialData); // Remove empty values
<<<<<<< HEAD
        $settings['active_sections'] = array_keys(array_filter($request->sections ?? []));
=======
        $settings['active_sections'] = $request->sections ?? [];
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

        $facility->update(['settings' => $settings]);

        return redirect()->route('admin.facilities.edit', $facility->id)
                        ->with('success', 'Facility updated successfully!');
    }

    public function layoutConfig($id)
    {
        $facility = Facility::findOrFail($id);
        return view('admin.facilities.layout-config', compact('facility'));
    }

    public function updateLayoutConfig(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $layoutConfig = $request->validate([
            'hero_variant' => 'required|string|in:default,video,split',
            'about_variant' => 'required|string|in:default,stats,timeline',
            'services_variant' => 'required|string|in:grid,cards,tabs',
            'contact_variant' => 'required|string|in:form,info,map',
            'hero_config' => 'array',
            'about_config' => 'array',
            'services_config' => 'array',
            'contact_config' => 'array'
        ]);

        $facility->update(['layout_config' => $layoutConfig]);

        return redirect()->route('admin.facilities.layout-config', $facility->id)
                        ->with('success', 'Layout configuration updated successfully!');
    }
}
