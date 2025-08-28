<?php

namespace App\Services;

use App\Models\LayoutTemplate;
use App\Models\LayoutSection;
use Illuminate\Support\Facades\Log;

class DynamicLayoutService
{
    protected $facility;
    protected $configService;

    public function __construct(TenantConfigService $configService)
    {
        // Only try to get current facility if we're in a tenant context
        // Admin routes don't use tenant middleware, so this might not be available
        try {
            if (app()->bound('current_facility')) {
                $currentFacility = app('current_facility');
                // Only set if it's a proper Facility object
                if (is_object($currentFacility) && method_exists($currentFacility, 'getAttribute')) {
                    $this->facility = $currentFacility;
                } else {
                    $this->facility = null;
                }
            } else {
                $this->facility = null;
            }
        } catch (\Exception $e) {
            $this->facility = null;
        }
        $this->configService = $configService;
    }

    public function getLayoutSections($facility = null)
    {
        // ALWAYS prefer the passed facility parameter - never use instance facility if parameter is provided
        if ($facility !== null) {
            $targetFacility = $facility;
        } else {
            $targetFacility = $this->facility;
        }

        if (!$targetFacility) {
            Log::warning('DynamicLayoutService: No facility provided and no instance facility available');
            return [];
        }

        // Validate that we have a proper Facility object
        if (!is_object($targetFacility) || !method_exists($targetFacility, 'getAttribute')) {
            Log::error('DynamicLayoutService: Invalid facility data type', [
                'type' => gettype($targetFacility),
                'is_array' => is_array($targetFacility),
                'is_object' => is_object($targetFacility),
                'class' => is_object($targetFacility) ? get_class($targetFacility) : 'not_object',
                'data' => is_array($targetFacility) ? array_keys($targetFacility) : 'not_array',
                'facility_param_provided' => $facility !== null,
                'instance_facility_type' => gettype($this->facility),
                'instance_facility_class' => is_object($this->facility) ? get_class($this->facility) : 'not_object'
            ]);
            return [];
        }

        $template = LayoutTemplate::where('slug', $targetFacility->layout_template)
                                 ->where('is_active', true)
                                 ->first();

        if (!$template) {
            return [];
        }

        $sections = [];
        $facilityConfig = $targetFacility->layout_config ?? [];

        foreach ($template->sections as $sectionSlug) {
            $section = LayoutSection::where('slug', $sectionSlug)
                                  ->where('is_active', true)
                                  ->first();

            if ($section) {
                $sectionConfig = $facilityConfig[$sectionSlug] ?? $template->default_config[$sectionSlug] ?? [];
                $variant = $sectionConfig['variant'] ?? 'default';

                $sections[] = [
                    'section' => $section,
                    'config' => $sectionConfig,
                    'variant' => $variant,
                    'component_path' => $section->getComponentPath($variant)
                ];
            }
        }

        return $sections;
    }

    public function getSectionConfig($sectionSlug, $facility = null)
    {
        $targetFacility = $facility ?? $this->facility;

        if (!$targetFacility) {
            return [];
        }

        $facilityConfig = $targetFacility->layout_config ?? [];
        $templateConfig = $this->getTemplateDefaultConfig($sectionSlug, $targetFacility);

        return array_merge($templateConfig, $facilityConfig[$sectionSlug] ?? []);
    }

    public function setSectionConfig($sectionSlug, $config, $facility = null)
    {
        $targetFacility = $facility ?? $this->facility;

        if (!$targetFacility) {
            return false;
        }

        return $targetFacility->setLayoutConfig($sectionSlug, $config);
    }

    public function getTemplateDefaultConfig($sectionSlug, $facility = null)
    {
        $targetFacility = $facility ?? $this->facility;

        if (!$targetFacility) {
            return [];
        }

        $template = LayoutTemplate::where('slug', $targetFacility->layout_template)
                                 ->where('is_active', true)
                                 ->first();

        if (!$template) {
            return [];
        }

        return $template->default_config[$sectionSlug] ?? [];
    }

    public function getAvailableTemplates()
    {
        return LayoutTemplate::where('is_active', true)->get();
    }

    public function changeTemplate($templateSlug)
    {
        if (!$this->facility) {
            return false;
        }

        $template = LayoutTemplate::where('slug', $templateSlug)
                                 ->where('is_active', true)
                                 ->first();

        if (!$template) {
            return false;
        }

        // Update facility's template and reset layout config to defaults
        $this->facility->update([
            'layout_template' => $templateSlug,
            'layout_config' => $template->default_config
        ]);

        return true;
    }

    public function getSectionVariants($sectionSlug)
    {
        $section = LayoutSection::where('slug', $sectionSlug)
                               ->where('is_active', true)
                               ->first();

        return $section ? $section->variants : [];
    }

    public function renderSection($sectionSlug, $additionalData = [])
    {
        $sections = $this->getLayoutSections();
        $sectionData = collect($sections)->firstWhere('section.slug', $sectionSlug);

        if (!$sectionData) {
            return '';
        }

        $data = array_merge([
            'facility' => $this->facility,
            'config' => $sectionData['config'],
            'variant' => $sectionData['variant']
        ], $additionalData);

        try {
            return view($sectionData['component_path'], $data)->render();
        } catch (\Exception $e) {
            // Fallback to default component if variant doesn't exist
            $fallbackPath = str_replace(['.video', '.split', '.stats', '.timeline', '.cards', '.tabs', '.form', '.info', '.map'], '.default', $sectionData['component_path']);

            try {
                return view($fallbackPath, $data)->render();
            } catch (\Exception $e) {
                return "<!-- Section {$sectionSlug} could not be rendered: {$e->getMessage()} -->";
            }
        }
    }
}
