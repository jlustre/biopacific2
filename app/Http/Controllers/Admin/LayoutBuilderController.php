<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\LayoutTemplate;
use App\Models\LayoutSection;
use App\Services\DynamicLayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LayoutBuilderController extends Controller
{
    protected $layoutService;

    public function __construct(DynamicLayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    public function index()
    {
        $facilities = Facility::select('id', 'name', 'domain', 'layout_template')->get();
        $templates = LayoutTemplate::where('is_active', true)->get();

        return view('admin.layout-builder.index', compact('facilities', 'templates'));
    }

    public function getFacilityLayout($facilityId)
    {
        // Manual facility lookup instead of model binding
        $facility = Facility::find($facilityId);

        if (!$facility) {
            Log::error('LayoutBuilder: Facility not found', [
                'facility_id' => $facilityId
            ]);
            return response()->json(['error' => 'Facility not found'], 404);
        }

        Log::info('LayoutBuilder: Loading facility layout', [
            'facility_id' => $facility->id,
            'facility_name' => $facility->name,
            'layout_template' => $facility->layout_template
        ]);

        $template = LayoutTemplate::where('slug', $facility->layout_template)
                                 ->where('is_active', true)
                                 ->first();

        if (!$template) {
            Log::error('LayoutBuilder: No template found', [
                'facility_id' => $facility->id,
                'layout_template' => $facility->layout_template
            ]);
            return response()->json(['error' => 'No layout template found for this facility'], 404);
        }

        Log::info('LayoutBuilder: Template found', [
            'template_name' => $template->name,
            'template_sections' => $template->sections
        ]);

        if (!$template) {
            return response()->json(['error' => 'No layout template found for this facility'], 404);
        }

        $sections = [];
        $facilityConfig = $facility->layout_config ?? [];
        $availableSections = LayoutSection::where('is_active', true)->get()->keyBy('slug');

        foreach ($template->sections as $sectionSlug) {
            if (isset($availableSections[$sectionSlug])) {
                $section = $availableSections[$sectionSlug];
                $sectionConfig = $facilityConfig[$sectionSlug] ?? $template->default_config[$sectionSlug] ?? [];
                $variant = $sectionConfig['variant'] ?? 'default';

                $sections[] = [
                    'id' => $section->id,
                    'slug' => $section->slug,
                    'name' => $section->name,
                    'description' => $section->description,
                    'current_variant' => $variant,
                    'available_variants' => array_keys($section->variants ?? []),
                    'config' => $sectionConfig,
                    'order' => count($sections)
                ];
            }
        }

        return response()->json([
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'domain' => $facility->domain,
                'layout_template' => $facility->layout_template
            ],
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description
            ],
            'sections' => $sections,
            'available_sections' => $availableSections->values()->map(function($section) {
                return [
                    'id' => $section->id,
                    'slug' => $section->slug,
                    'name' => $section->name,
                    'description' => $section->description,
                    'variants' => array_keys($section->variants ?? [])
                ];
            })
        ]);
    }

    public function updateLayout(Request $request, $facilityId)
    {
        // Manual facility lookup instead of model binding
        $facility = Facility::find($facilityId);

        if (!$facility) {
            Log::error('LayoutBuilder: Facility not found for update', [
                'facility_id' => $facilityId
            ]);
            return response()->json(['error' => 'Facility not found'], 404);
        }

        Log::info('LayoutBuilder: Updating layout', [
            'facility_id' => $facility->id,
            'facility_name' => $facility->name,
            'request_data' => $request->all()
        ]);

        try {
            Log::info('LayoutBuilder: Starting validation');

            $validated = $request->validate([
                'sections' => 'required|array',
                'sections.*.slug' => 'required|string|exists:layout_sections,slug',
                'sections.*.variant' => 'required|string',
                'sections.*.config' => 'sometimes|array',
                'template_name' => 'sometimes|string|max:255',
                'template_description' => 'sometimes|string|max:500'
            ]);

            Log::info('LayoutBuilder: Validation passed', [
                'validated_data' => $validated
            ]);

            // Update facility layout configuration
            $layoutConfig = [];
            $sectionOrder = [];

            foreach ($validated['sections'] as $index => $sectionData) {
                $sectionSlug = $sectionData['slug'];
                $sectionOrder[] = $sectionSlug;

                $layoutConfig[$sectionSlug] = [
                    'variant' => $sectionData['variant'],
                    'order' => $index
                ];

                if (isset($sectionData['config'])) {
                    $layoutConfig[$sectionSlug] = array_merge(
                        $layoutConfig[$sectionSlug],
                        $sectionData['config']
                    );
                }
            }

            // Update facility's layout configuration
            $facility->update([
                'layout_config' => $layoutConfig
            ]);

            // If creating a new template, update the template sections order
            if ($request->has('template_name')) {
                $template = LayoutTemplate::where('slug', $facility->layout_template)->first();
                if ($template) {
                    $template->update([
                        'sections' => $sectionOrder
                    ]);
                }
            }

            Log::info('LayoutBuilder: Layout updated successfully', [
                'facility_id' => $facility->id,
                'sections_count' => count($validated['sections'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Layout updated successfully',
                'facility' => $facility->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('LayoutBuilder: Validation failed', [
                'facility_id' => $facility->id,
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('LayoutBuilder: Error updating layout', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to update layout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicateLayout(Request $request, $facilityId)
    {
        // Manual facility lookup instead of model binding
        $facility = Facility::find($facilityId);

        if (!$facility) {
            return response()->json(['error' => 'Facility not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string|max:500'
        ]);

        $currentTemplate = LayoutTemplate::where('slug', $facility->layout_template)->first();

        if (!$currentTemplate) {
            return response()->json(['error' => 'Current template not found'], 404);
        }

        // Create new template with unique slug
        $baseSlug = Str::slug($validated['name']);
        $slug = $this->generateUniqueSlug($baseSlug);

        // Get current facility layout configuration
        $facilityConfig = $facility->layout_config ?? [];

        // Build sections data from current template and facility config
        $sectionsData = [];
        foreach ($currentTemplate->sections as $sectionSlug) {
            $sectionsData[] = [
                'slug' => $sectionSlug,
                'variant' => $facilityConfig[$sectionSlug]['variant'] ?? 'default'
            ];
        }

        $newTemplate = LayoutTemplate::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? "Duplicated from {$currentTemplate->name}",
            'sections' => array_column($sectionsData, 'slug'),
            'default_config' => $this->buildDefaultConfig($sectionsData),
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layout duplicated successfully',
            'template' => $newTemplate
        ]);
    }

    public function saveAsTemplate(Request $request, $facilityId)
    {
        // Manual facility lookup instead of model binding
        $facility = Facility::find($facilityId);

        if (!$facility) {
            return response()->json(['error' => 'Facility not found'], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'sometimes|string|max:500',
                'sections' => 'required|array',
                'sections.*.slug' => 'required|string',
                'sections.*.variant' => 'required|string',
                'apply_to_facility' => 'sometimes|boolean'
            ]);

            // Create new template with unique slug
            $baseSlug = Str::slug($validated['name']);
            $slug = $this->generateUniqueSlug($baseSlug);

            $template = LayoutTemplate::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? "Custom layout for {$facility->name}",
                'sections' => array_column($validated['sections'], 'slug'),
                'default_config' => $this->buildDefaultConfig($validated['sections']),
                'is_active' => true
            ]);

            // Optionally apply the new template to the facility
            if ($validated['apply_to_facility'] ?? false) {
                $facility->update([
                    'layout_template' => $template->slug
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('LayoutBuilder: Error saving template', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to save template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function preview(Request $request, $facilityId)
    {
        // Manual facility lookup instead of model binding
        $facility = Facility::find($facilityId);

        if (!$facility) {
            return response()->json(['error' => 'Facility not found'], 404);
        }

        $sections = $request->input('sections', []);

        // Validate and ensure sections is an array
        if (!is_array($sections)) {
            Log::error('LayoutBuilder: Sections data is not an array', [
                'facility_id' => $facility->id,
                'sections_type' => gettype($sections),
                'sections_data' => $sections
            ]);

            // Try to decode if it's a JSON string
            if (is_string($sections)) {
                $decoded = json_decode($sections, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $sections = $decoded;
                } else {
                    return response()->json(['error' => 'Invalid sections data format'], 400);
                }
            } else {
                return response()->json(['error' => 'Sections must be an array'], 400);
            }
        }

        Log::info('LayoutBuilder: Preview request', [
            'facility_id' => $facility->id,
            'sections_count' => count($sections),
            'sections_data' => $sections
        ]);

        // Temporarily set the layout configuration for preview
        $originalConfig = $facility->layout_config;

        $previewConfig = [];
        foreach ($sections as $index => $sectionData) {
            // Validate section data structure
            if (!is_array($sectionData) || !isset($sectionData['slug']) || !isset($sectionData['variant'])) {
                Log::error('LayoutBuilder: Invalid section data structure', [
                    'index' => $index,
                    'section_data' => $sectionData
                ]);
                continue;
            }

            $previewConfig[$sectionData['slug']] = [
                'variant' => $sectionData['variant'],
                'order' => $index
            ];
        }

        $facility->layout_config = $previewConfig;

        // Debug: Check facility object before passing to service
        Log::info('LayoutBuilder: About to call getLayoutSections', [
            'facility_type' => gettype($facility),
            'is_object' => is_object($facility),
            'class' => is_object($facility) ? get_class($facility) : 'not_object',
            'has_layout_template' => is_object($facility) ? isset($facility->layout_template) : 'not_object'
        ]);

        // Get sections for preview - pass facility to the service
        $layoutSections = $this->layoutService->getLayoutSections($facility);

        // Debug: Log all section paths being generated
        Log::info('LayoutBuilder: Preview sections debug', [
            'facility_id' => $facility->id,
            'sections_count' => count($layoutSections),
            'section_paths' => array_map(function($section) {
                return [
                    'slug' => $section['section']->slug,
                    'variant' => $section['variant'],
                    'component_path' => $section['component_path'],
                    'view_exists' => view()->exists($section['component_path'])
                ];
            }, $layoutSections)
        ]);

        // Restore original configuration
        $facility->layout_config = $originalConfig;

        // Convert facility model to array for the views
        $facilityData = $facility->toArray();

        // Add default values for missing fields that views expect
        $facilityData['hours'] = $facilityData['hours'] ?? '9:00 AM - 8:00 PM Daily';
        $facilityData['tagline'] = $facilityData['tagline'] ?? 'Quality care for your loved ones';

        // Add social media data if not present
        if (!isset($facilityData['social'])) {
            $facilityData['social'] = [
                'facebook' => $facility->facebook ?? null,
                'linkedin' => null,
                'youtube' => null
            ];
        }

        return view('admin.layout-builder.preview', [
            'facility' => $facilityData,
            'sections' => $layoutSections,
            'isPreview' => true
        ]);
    }

    protected function buildDefaultConfig($sections)
    {
        $config = [];

        foreach ($sections as $section) {
            $config[$section['slug']] = [
                'variant' => $section['variant']
            ];

            if (isset($section['config'])) {
                $config[$section['slug']] = array_merge(
                    $config[$section['slug']],
                    $section['config']
                );
            }
        }

        return $config;
    }

    /**
     * Generate a unique slug for layout templates
     */
    private function generateUniqueSlug($baseSlug)
    {
        $slug = $baseSlug;
        $counter = 1;

        while (LayoutTemplate::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
