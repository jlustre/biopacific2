<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LayoutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LayoutSectionController extends Controller
{
    public function index()
    {
        $sections = LayoutSection::orderBy('name')->paginate(12);
        return view('admin.sections.index', compact('sections'));
    }

    public function show($id)
    {
        $section = LayoutSection::findOrFail($id);
        return view('admin.sections.show', compact('section'));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'component_path' => 'required|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:100',
            'variants.*.description' => 'nullable|string|max:500',
            'config_schema' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $section = LayoutSection::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'component_path' => $validated['component_path'],
            'variants' => $validated['variants'] ?? [],
            'config_schema' => $validated['config_schema'] ?? [],
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.sections.show', $section->id)
                        ->with('success', 'Layout section created successfully!');
    }

    public function edit($id)
    {
        $section = LayoutSection::findOrFail($id);
        return view('admin.sections.edit', compact('section'));
    }

    public function update(Request $request, $id)
    {
        $section = LayoutSection::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'component_path' => 'required|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:100',
            'variants.*.description' => 'nullable|string|max:500',
            'config_schema' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $section->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'component_path' => $validated['component_path'],
            'variants' => $validated['variants'] ?? [],
            'config_schema' => $validated['config_schema'] ?? [],
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.sections.show', $section->id)
                        ->with('success', 'Layout section updated successfully!');
    }

    public function destroy($id)
    {
        $section = LayoutSection::findOrFail($id);

        // Check if section is in use by any templates
        $templatesUsingSection = \App\Models\LayoutTemplate::whereJsonContains('sections', $section->slug)->count();

        if ($templatesUsingSection > 0) {
            return redirect()->route('admin.sections.index')
                            ->with('error', "Cannot delete section '{$section->name}' as it's being used by {$templatesUsingSection} layout template(s).");
        }

        $section->delete();

        return redirect()->route('admin.sections.index')
                        ->with('success', 'Layout section deleted successfully!');
    }

    public function duplicate($id)
    {
        $section = LayoutSection::findOrFail($id);

        $newSection = LayoutSection::create([
            'name' => $section->name . ' (Copy)',
            'slug' => Str::slug($section->name . '-copy-' . time()),
            'description' => $section->description,
            'component_path' => $section->component_path,
            'variants' => $section->variants,
            'config_schema' => $section->config_schema,
            'is_active' => false
        ]);

        return redirect()->route('admin.sections.edit', $newSection->id)
                        ->with('success', 'Section duplicated successfully! Please review and modify as needed.');
    }

    public function preview($id, $variant = 'default')
    {
        $section = LayoutSection::findOrFail($id);

        // Create mock facility data for preview
        $mockFacility = [
            'name' => 'Preview Facility',
            'tagline' => 'Sample tagline for preview',
            'headline' => 'Welcome to Our Preview Center',
            'subheadline' => 'Experience exceptional care with compassion and dignity',
            'primary_color' => '#047857',
            'secondary_color' => '#1f2937',
            'accent_color' => '#06b6d4',
            'hero_image_url' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f',
            'about_text' => 'Our facility provides exceptional care in a warm environment.',
            'about_image_url' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56',
            'phone' => '(555) 123-4567',
            'email' => 'info@previewfacility.com',
            'address' => '123 Preview Street, Preview City, CA 90210',
            'logo_url' => '/images/placeholders/logo.png',
        ];

        // Mock services for services sections
        $mockServices = collect([
            (object) ['name' => 'Physical Therapy', 'description' => 'Comprehensive rehabilitation', 'icon' => 'fas fa-dumbbell', 'image_url' => null],
            (object) ['name' => '24/7 Nursing Care', 'description' => 'Round-the-clock care', 'icon' => 'fas fa-user-nurse', 'image_url' => null],
            (object) ['name' => 'Dining Services', 'description' => 'Nutritious meals daily', 'icon' => 'fas fa-utensils', 'image_url' => null],
        ]);

        $mockFacility['services'] = $mockServices;

        return view('admin.sections.preview', compact('section', 'variant', 'mockFacility'));
    }
}
