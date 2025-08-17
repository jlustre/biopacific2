<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LayoutTemplate;
use App\Models\LayoutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LayoutTemplateController extends Controller
{
    public function index()
    {
        $templates = LayoutTemplate::withCount('facilities')->orderBy('name')->get();
        return view('admin.layouts.index', compact('templates'));
    }

    public function show($id)
    {
        $template = LayoutTemplate::with('facilities')->findOrFail($id);
        $sections = LayoutSection::whereIn('slug', $template->sections ?? [])->get();
        return view('admin.layouts.show', compact('template', 'sections'));
    }

    public function create()
    {
        $availableSections = LayoutSection::where('is_active', true)->get();
        return view('admin.layouts.create', compact('availableSections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sections' => 'required|array|min:1',
            'sections.*' => 'string|exists:layout_sections,slug',
            'preview_image' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'default_config_json' => 'nullable|string'
        ]);

        // Parse JSON configuration
        $defaultConfig = [];
        if (!empty($validated['default_config_json'])) {
            try {
                $defaultConfig = json_decode($validated['default_config_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON: ' . json_last_error_msg());
                }
            } catch (\Exception $e) {
                return back()->withErrors(['default_config' => 'Invalid JSON format: ' . $e->getMessage()])
                            ->withInput();
            }
        }

        $template = LayoutTemplate::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'sections' => $validated['sections'],
            'default_config' => $defaultConfig,
            'preview_image' => $validated['preview_image'],
            'is_active' => $request->has('is_active')
        ]);

        if ($request->input('action') === 'save_and_preview') {
            return redirect()->route('admin.layouts.preview', $template->id)
                            ->with('success', 'Layout template created successfully!');
        }

        return redirect()->route('admin.layouts.show', $template->id)
                        ->with('success', 'Layout template created successfully!');
    }

    public function edit($id)
    {
        $template = LayoutTemplate::findOrFail($id);
        $availableSections = LayoutSection::where('is_active', true)->get();
        return view('admin.layouts.edit', compact('template', 'availableSections'));
    }

    public function update(Request $request, $id)
    {
        $template = LayoutTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sections' => 'required|array|min:1',
            'sections.*' => 'string|exists:layout_sections,slug',
            'preview_image' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'default_config' => 'nullable|array'
        ]);

        $template->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'sections' => $validated['sections'],
            'default_config' => $validated['default_config'] ?? [],
            'preview_image' => $validated['preview_image'],
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.layouts.show', $template->id)
                        ->with('success', 'Layout template updated successfully!');
    }

    public function destroy($id)
    {
        $template = LayoutTemplate::findOrFail($id);

        // Check if template is in use
        if ($template->facilities()->count() > 0) {
            return redirect()->route('admin.layouts.index')
                            ->with('error', 'Cannot delete layout template that is in use by facilities.');
        }

        $template->delete();

        return redirect()->route('admin.layouts.index')
                        ->with('success', 'Layout template deleted successfully!');
    }

    public function preview($id)
    {
        $template = LayoutTemplate::findOrFail($id);
        $sections = LayoutSection::whereIn('slug', $template->sections ?? [])->get();

        // Create a mock facility class that implements the getSetting method
        $mockFacility = new class($template) {
            public $name = 'Preview Facility';
            public $tagline = 'This is a preview of the layout template';
            public $headline = 'Welcome to Our Preview Center';
            public $subheadline = 'Experience exceptional care with compassion and dignity';
            public $primary_color = '#047857';
            public $secondary_color = '#1f2937';
            public $accent_color = '#06b6d4';
            public $hero_image_url = 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';
            public $about_text = 'Our facility is dedicated to providing the highest quality care in a warm, comfortable environment. We believe every resident deserves respect, dignity, and personalized attention.';
            public $about_image_url = 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=1969&q=80';
            public $city = 'Preview City';
            public $state = 'CA';
            public $phone = '(555) 123-4567';
            public $email = 'info@previewfacility.com';
            public $address = '123 Preview Street';
            public $logo_url = '/images/placeholders/logo.png';
            public $layout_template;
            public $layout_config;
            public $services;

            public function __construct($template) {
                $this->layout_template = $template->slug;
                $this->layout_config = $template->default_config;

                $this->services = collect([
                    (object) [
                        'name' => 'Physical Therapy',
                        'description' => 'Comprehensive rehabilitation services',
                        'icon' => 'fas fa-dumbbell',
                        'image_url' => null
                    ],
                    (object) [
                        'name' => '24/7 Nursing Care',
                        'description' => 'Round-the-clock professional nursing',
                        'icon' => 'fas fa-user-nurse',
                        'image_url' => null
                    ],
                    (object) [
                        'name' => 'Dining Services',
                        'description' => 'Nutritious meals prepared daily',
                        'icon' => 'fas fa-utensils',
                        'image_url' => null
                    ],
                    (object) [
                        'name' => 'Social Activities',
                        'description' => 'Engaging recreational programs',
                        'icon' => 'fas fa-puzzle-piece',
                        'image_url' => null
                    ],
                ]);
            }

            public function getSetting($key, $default = null) {
                // Return default values for common settings
                $settings = [
                    'favicon' => null,
                    'custom_css' => '',
                    'logo' => null
                ];

                return $settings[$key] ?? $default;
            }

            // Make it work as an array too
            public function offsetExists($offset): bool {
                return property_exists($this, $offset);
            }

            public function offsetGet($offset) {
                return $this->$offset ?? null;
            }

            public function offsetSet($offset, $value): void {
                $this->$offset = $value;
            }

            public function offsetUnset($offset): void {
                unset($this->$offset);
            }
        };

        // Convert to array for partials that use array access
        $facilityData = (array) $mockFacility;
        $facilityData['services'] = $mockFacility->services;

        return view('admin.layouts.preview', compact('template', 'sections', 'facilityData'));
    }

    public function duplicate($id)
    {
        $template = LayoutTemplate::findOrFail($id);

        $newTemplate = LayoutTemplate::create([
            'name' => $template->name . ' (Copy)',
            'slug' => Str::slug($template->name . '-copy-' . time()),
            'description' => $template->description,
            'sections' => $template->sections,
            'default_config' => $template->default_config,
            'preview_image' => $template->preview_image,
            'is_active' => false
        ]);

        return redirect()->route('admin.layouts.edit', $newTemplate->id)
                        ->with('success', 'Layout template duplicated successfully!');
    }
}
