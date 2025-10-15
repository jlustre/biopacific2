<?php

namespace App\Http\Controllers;

use App\Helpers\FacilityDataHelper;

use App\Models\Facility;
use App\Models\ColorScheme;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    /**
     * Toggle a HIPAA flag for a facility (AJAX endpoint)
     */
    public function toggleHipaaFlag(Request $request, Facility $facility)
    {
        $key = $request->input('key');
        $flags = $facility->hipaa_flags ?? [];
        // Toggle the current value (true becomes false, false/null becomes true)
        $flags[$key] = !($flags[$key] ?? false);
        $facility->update(['hipaa_flags' => $flags]);
        return response()->json([
            'success' => true,
            'flags' => $flags,
            'toggled_key' => $key,
            'new_value' => $flags[$key],
            'message' => $flags[$key] ? 'HIPAA item marked as completed!' : 'HIPAA item marked as incomplete!'
        ]);
    }
    public function index()
    {
        $facilities = Facility::all();
        $services = \App\Models\Service::orderBy('title')->get();
        return view('facilities.index', compact('facilities', 'services'));
    }

    /**
     * Public facility landing page (formerly route closure)
     */
    public function publicView(Facility $facility)
    {
        $global = DB::table('global_shutdowns')->orderByDesc('id')->first();
        if ($global && $global->is_shutdown) {
            return response()->view('shutdown', [
                'message' => $global->shutdown_message,
                'eta' => $global->shutdown_eta,
                'isGlobal' => true,
            ]);
        }
        
        if ($facility->is_shutdown) {
            return response()->view('shutdown', [
                'message' => $facility->shutdown_message,
                'eta' => $facility->shutdown_eta,
                'isGlobal' => false,
                'facilityName' => (string) $facility,
            ]);
        }

        $colors = FacilityDataHelper::getColors($facility);
       
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $sections = [];
        $sectionVariances = [];
        $layoutTemplate = '';
        if ($activeWebContent) {
            // Restore robust section decoding for main page rendering
            $rawSections = $activeWebContent->sections;
            if (is_string($rawSections)) {
                $sections = json_decode($rawSections, true) ?: [];
            } elseif (is_array($rawSections)) {
                $sections = $rawSections;
            } elseif ($rawSections instanceof \Illuminate\Support\Collection) {
                $sections = $rawSections->toArray();
            } else {
                $sections = (array) $rawSections;
            }
            $sectionVariances = is_array($activeWebContent->variances) ? $activeWebContent->variances : json_decode($activeWebContent->variances, true);
            $layoutTemplate = $activeWebContent->layout_template;
        }
        $activeSections = FacilityDataHelper::getActiveSections($facility);

        $faqs = FacilityDataHelper::getFaqs($facility);
        $categories = $faqs->pluck('category')->filter()->unique()->values();
        $testimonials = FacilityDataHelper::getTestimonials($facility);
        $services = FacilityDataHelper::getServices($facility);
        $newsItems = FacilityDataHelper::getFormattedNews($facility);

        return view('welcome', [
            'facility' => $facility,
            'active_sections' => $activeSections,
            'primary' => $colors['primary'],
            'secondary' => $colors['secondary'],
            'accent' => $colors['accent'],
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'services' => $services,
            'layoutTemplate' => $layoutTemplate,
            'faqs' => $faqs,
            'categories' => $categories,
            'testimonials' => $testimonials,
            'newsItems' => $newsItems
        ]);
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function show(Facility $facility)
    {
        return view('facilities.show', compact('facility'));
    }
}
