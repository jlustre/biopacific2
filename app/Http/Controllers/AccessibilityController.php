<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Helpers\FacilityDataHelper;
use Illuminate\Http\Request;

class AccessibilityController extends Controller
{
    public function show(Facility $facility)
    {
        // Format facility data like the welcome view does
        $facilityData = $facility->toArray();
        $colors = FacilityDataHelper::getColorsFromColorScheme($facility->color_scheme_id);
        
        // Get the facility's web content to determine sections
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $sections = ['topbar']; // Always include topbar for navigation
        $sectionVariances = ['topbar' => 'legal'];
        
        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $additionalSections = $activeWebContent->sections;
            }
            
            if (!empty($additionalSections) && is_array($additionalSections)) {
                $sections = array_merge($sections, $additionalSections);
            }
        }
        
        if ($activeWebContent && isset($activeWebContent->variances)) {
            if (is_string($activeWebContent->variances)) {
                $additionalVariances = json_decode($activeWebContent->variances, true) ?? [];
            } elseif (is_array($activeWebContent->variances)) {
                $additionalVariances = $activeWebContent->variances;
            }
            
            if (!empty($additionalVariances) && is_array($additionalVariances)) {
                $sectionVariances = array_merge($sectionVariances, $additionalVariances);
            }
        }
        
        // Force legal topbar variant for legal pages (must be after merging)
        $sectionVariances['topbar'] = 'legal';

        $activeSections = [];
        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $activeSections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $activeSections = $activeWebContent->sections;
            }
        }
        
        return view('accessibility', [
            'facility' => $facility->toArray(), // Ensure facility data is passed
            'primary' => $colors['primary'],
            'secondary' => $colors['secondary'],
            'accent' => $colors['accent'],
            'neutral_light' => $colors['neutral_light'],
            'neutral_dark' => $colors['neutral_dark'],
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'activeSections' => $activeSections
        ]);
    }
}