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

        $layoutData = FacilityDataHelper::getLayoutData($activeWebContent);
        $sections = $layoutData['sections'];
        $sectionVariances = $layoutData['sectionVariances'];
        $layoutTemplate = $layoutData['layoutTemplate'];

        $faqs = FacilityDataHelper::getFaqs($facility);
        $categories = $faqs->pluck('category')->filter()->unique()->values();
        $testimonials = FacilityDataHelper::getTestimonials($facility);
        $services = FacilityDataHelper::getServices($facility);
        $newsItems = FacilityDataHelper::getFormattedNews($facility);

        return view('welcome', [
            'facility' => $facility,
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
        return view('facilities.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'slug' => 'required|string|max:255|unique:facilities',
    //         'city' => 'required|string|max:255',
    //         'state' => 'required|string|max:255',
    //         'beds' => 'nullable|integer|min:0',
    //         'hero_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'description' => 'nullable|string',
    //     ]);

    //     $data = $request->all();

    //     // Handle hero image upload
    //     if ($request->hasFile('hero_image')) {
    //         $imagePath = $request->file('hero_image')->store('facilities', 'public');
    //         $data['hero_image_url'] = '/storage/' . $imagePath;
    //     }

    //     Facility::create($data);

    //     return redirect()->route('facilities.index')->with('success', 'Facility created successfully!');
    // }

    public function show(Facility $facility)
    {
        return view('facilities.show', compact('facility'));
    }

    // public function edit(Facility $facility)
    // {
    //     return view('facilities.edit', compact('facility'));
    // }

    // public function update(Request $request, Facility $facility)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'slug' => 'required|string|max:255|unique:facilities,slug,' . $facility->id,
    //         'city' => 'required|string|max:255',
    //         'state' => 'required|string|max:255',
    //         'beds' => 'nullable|integer|min:0',
    //         'hero_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'description' => 'nullable|string',
    //     ]);

    //     $data = $request->all();

    //     // Handle hero image upload
    //     if ($request->hasFile('hero_image')) {
    //         $imagePath = $request->file('hero_image')->store('facilities', 'public');
    //         $data['hero_image_url'] = '/storage/' . $imagePath;
    //     }

    //     $facility->update($data);

    //     return redirect()->route('facilities.index')->with('success', 'Facility updated successfully!');
    // }

    // public function destroy(Facility $facility)
    // {
    //     $facility->delete();

    //     return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully!');
    // }
}
