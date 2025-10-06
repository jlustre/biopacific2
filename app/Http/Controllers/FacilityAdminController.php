<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Testimonial;
use App\Models\Faq;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FacilityAdminController extends Controller
{
    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        return view('admin.facilities.create');
    }

    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'beds' => 'nullable|integer|min:0',
            'domain' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        $facility = new \App\Models\Facility($validated);
        $facility->is_active = (bool) $request->input('is_active');

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('facilities', 'public');
            $facility->photo_url = $path;
        }

        $facility->save();

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
    }
    public function index()
    {
    $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.index', compact('facilities'));
    }

    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        $faqs = \App\Models\Faq::availableForFacility($facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        $testimonials = \App\Models\Testimonial::where('facility_id', $facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $categories = $faqs->pluck('category')->filter()->unique()->values();
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
        // Get color scheme values
        $scheme = $facility->colorScheme ?? null;
        $primary = $scheme->primary_color ?? '#0EA5E9';
        $secondary = $scheme->secondary_color ?? '#1E293B';
        $accent = $scheme->accent_color ?? '#F59E0B';
        return view('welcome', compact('facility', 'layoutTemplate', 'sections', 'faqs', 'categories', 'testimonials', 'primary', 'secondary', 'accent'));
    }

    public function edit($identifier)
    {
        // Try to find facility by ID first (if it's numeric), then by slug
        if (is_numeric($identifier)) {
            $facility = Facility::findOrFail($identifier);
        } else {
            $facility = Facility::where('slug', $identifier)->firstOrFail();
        }
        $facilities = Facility::orderBy('name')->get();
        $layoutTemplates = ['default-template', 'layout2', 'layout3', 'layout4'];
        $webContents = $facility->webcontents()->get();
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
        $selectedLayoutTemplate = $activeWebContent ? $activeWebContent->layout_template : null;
        $selectedSections = [];
        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $selectedSections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $selectedSections = $activeWebContent->sections;
            }
        }
        $faqs = \App\Models\Faq::availableForFacility($facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        $categories = $faqs->pluck('category')->filter()->unique()->values();
        $colorSchemes = \App\Models\ColorScheme::orderBy('name')->get();
        return view('admin.facilities.edit', compact(
            'facility',
            'facilities', 
            'layoutTemplates',
            'webContents',
            'activeWebContent',
            'selectedLayoutTemplate',
            'selectedSections',
            'faqs',
            'categories',
            'colorSchemes'
        ));
    }

    public function update(Request $request, Facility $facility)
    {
        // Debug: Log the request data
        Log::info('Facility Update Request', [
            'facility_id' => $facility->id,
            'facility_slug' => $facility->slug,
            'sections' => $request->input('sections'),
            'variances' => $request->input('variances'),
            'all_data' => $request->all()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'domain' => 'required|string|max:255|unique:facilities,domain,' . $facility->id,
            'subdomain' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'beds' => 'nullable|integer|min:1',
            'hours' => 'nullable|string|max:255',
            'color_scheme_id' => 'required|exists:color_schemes,id',
            'headline' => 'nullable|string|max:255',
            'subheadline' => 'nullable|string|max:500',
            'about_text' => 'nullable|string',
            'hero_image_url' => 'nullable|max:500',
            'about_image_url' => 'nullable|max:500',
            'logo_url' => 'nullable|max:500',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'location_map' => 'nullable|string|max:1000',
            'hero_video_id' => 'nullable|string|max:50',
            // 'webcontents' => 'array',
            'layout_template' => 'required|string|in:default-template,layout2,layout3,layout4',
            'sections' => 'array',
            'variances' => 'array', // <-- Added validation for variances
            // Shutdown fields
            'is_shutdown' => 'nullable|boolean',
            'shutdown_message' => 'nullable|string|max:255',
            'shutdown_eta' => 'nullable|date',
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
            'color_scheme_id' => $validated['color_scheme_id'],
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
            'hero_video_id' => $validated['hero_video_id'] ?? null,
            // Shutdown fields
            'is_shutdown' => $request->has('is_shutdown'),
            'shutdown_message' => $validated['shutdown_message'] ?? null,
            'shutdown_eta' => $validated['shutdown_eta'] ?? null,
        ]);

        $layoutTemplate = $validated['layout_template'];
        $sections = $validated['sections'] ?? [];
        $variances = $validated['variances'] ?? [];

        Log::info('Processing layout update', [
            'layout_template' => $layoutTemplate,
            'sections' => $sections,
            'variances' => $variances
        ]);

        // Inactivate all templates for this facility except the selected one
        DB::table('web_contents')
            ->where('facility_id', $facility->id)
            ->where('layout_template', '!=', $layoutTemplate)
            ->update(['is_active' => false, 'sections' => null, 'variances' => null]);

        // Activate the selected template and update its sections and variances
        $result = DB::table('web_contents')
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

        Log::info('WebContent update result', ['result' => $result]);

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

    // Web Content Methods
    public function testimonials()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.testimonials', compact('facilities'));
    }

    public function getTestimonials($facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $testimonials = $facility->testimonials()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->get();
        
        return response()->json([
            'success' => true,
            'testimonials' => $testimonials,
            'count' => $testimonials->count()
        ]);
    }

    public function storeTestimonial(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'title_header' => 'nullable|string|max:100',
            'quote' => 'required|string',
            'story' => 'nullable|string',
            'relationship' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        $testimonial = Testimonial::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully!',
            'testimonial' => $testimonial
        ]);
    }

    public function showTestimonial($testimonialId)
    {
        // Find testimonial without global scope constraints
        $testimonial = Testimonial::withoutGlobalScope('tenant')->find($testimonialId);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'testimonial' => $testimonial
        ]);
    }

    public function updateTestimonial(Request $request, $testimonialId)
    {
        // Find testimonial without global scope constraints
        $testimonial = Testimonial::withoutGlobalScope('tenant')->find($testimonialId);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'title_header' => 'nullable|string|max:100',
            'quote' => 'required|string',
            'story' => 'nullable|string',
            'relationship' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ]);


        $testimonial->fill($validated);
        // Handle photo upload after fill so it is not overwritten
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists and is in storage
            if ($testimonial->photo_url && strpos($testimonial->photo_url, '/storage/testimonials/') === 0) {
                $oldPath = str_replace('/storage/', '', $testimonial->photo_url);
                Storage::disk('public')->delete($oldPath);
            }
            $photo = $request->file('photo');
            $path = $photo->store('testimonials', 'public');
            $testimonial->photo_url = '/storage/' . $path;
        } else if ($request->has('current_photo_url')) {
            $testimonial->photo_url = $request->input('current_photo_url');
        }
        $testimonial->save();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully!',
            'testimonial' => $testimonial
        ]);
    }

    public function destroyTestimonial($testimonialId)
    {
        // Find testimonial without global scope constraints
        $testimonial = Testimonial::withoutGlobalScope('tenant')->find($testimonialId);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }

        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully!'
        ]);
    }

    public function faqs()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.faqs', compact('facilities'));
    }

    public function getFaqs($facilityId)
    {
        // Get both facility-specific FAQs and default FAQs available to this facility
        $faqs = Faq::availableForFacility($facilityId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'faqs' => $faqs,
            'count' => $faqs->count()
        ]);
    }

    public function showFaq($faqId)
    {
        // Find FAQ without any scope constraints for editing
        $faq = Faq::find($faqId);
        
        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'faq' => $faq
        ]);
    }

    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'nullable|exists:facilities,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        // If it's a default FAQ, facility_id should be null
        if ($validated['is_default'] ?? false) {
            $validated['facility_id'] = null;
        }

        $faq = Faq::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully!',
            'faq' => $faq
        ]);
    }

    public function updateFaq(Request $request, $faqId)
    {
        // Find FAQ without scope constraints
        $faq = Faq::find($faqId);
        
        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        $validated = $request->validate([
            'facility_id' => 'nullable|exists:facilities,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        // If it's a default FAQ, facility_id should be null
        if ($validated['is_default'] ?? false) {
            $validated['facility_id'] = null;
        }

        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully!',
            'faq' => $faq
        ]);
    }

    public function destroyFaq($faqId)
    {
        // Find FAQ without scope constraints
        $faq = Faq::find($faqId);
        
        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found'
            ], 404);
        }

        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully!'
        ]);
    }

    public function getDefaultFaqs()
    {
        // Get all default FAQs that can be used by multiple facilities
        $faqs = Faq::default()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'faqs' => $faqs,
            'count' => $faqs->count()
        ]);
    }

    public function galleries()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.galleries', compact('facilities'));
    }

    public function newsEvents()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.news-events', compact('facilities'));
    }

    public function blogs()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.blogs', compact('facilities'));
    }

    public function manageNewsEvents($facilityId)
    {
        $facility = \App\Models\Facility::findOrFail($facilityId);
        $news = \App\Models\News::where('facility_id', $facilityId)->orderByDesc('published_at')->get();
        $events = \App\Models\Event::where('facility_id', $facilityId)->orderByDesc('event_date')->get();
        return view('admin.facilities.webcontents.manage-news-events', compact('facility', 'news', 'events'));
    }

    public function careers()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.facilities.webcontents.careers', compact('facilities'));
    }
}