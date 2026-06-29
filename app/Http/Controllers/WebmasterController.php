<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebmasterContactService;
use App\Support\FacilityShutdown;

class WebmasterController extends Controller
{
    public function show($facility)
    {
        // Load the facility data
        $facilityModel = \App\Models\Facility::where('slug', $facility)->firstOrFail();

        if ($response = FacilityShutdown::responseFor($facilityModel)) {
            return $response;
        }
        
        // Get colors using the same helper as other controllers
        $colors = \App\Helpers\FacilityDataHelper::getColors($facilityModel);
        
        // Get the facility's web content to determine sections
        $activeWebContent = $facilityModel->webcontents()->where('is_active', true)->first();
        $sections = ['topbar']; // Always include topbar for navigation
        $sectionVariances = ['topbar' => 'default'];
        
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
        
        $activeSections = \App\Helpers\FacilityDataHelper::getActiveSections($facilityModel);
        
        return view('webmaster.contact', [
            'facilityModel' => $facilityModel,
            'facility' => $facilityModel->toArray(),
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

    public function submit(Request $request, WebmasterContactService $service, $facility = null)
    {
        if ($facility) {
            $facilityModel = \App\Models\Facility::where('slug', $facility)->first();
            if ($facilityModel && ($response = FacilityShutdown::responseFor($facilityModel))) {
                return $response;
            }
        }

        $validated = $request->validate($service->validationRules());

        $service->createSubmission([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'urgent' => $request->boolean('urgent'),
            'facility_id' => $request->input('facility_id'),
            'category' => WebmasterContactService::CATEGORY_ISSUE,
            'source' => WebmasterContactService::SOURCE_PUBLIC_WEBSITE,
        ], $service->screenshotFilesFromRequest($request));

        return back()->with('success', 'Your message has been sent to the webmaster and stored for review. Thank you!');
    }
}
