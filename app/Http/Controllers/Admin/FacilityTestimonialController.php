<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Testimonial;

class FacilityTestimonialController extends Controller
{
    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return response()->json([
            'success' => true,
            'testimonial' => $testimonial
        ]);
    }
    public function index($facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $testimonials = $facility->testimonials()->orderByDesc('created_at')->get();
        return view('admin.testimonials.index', compact('facility', 'testimonials'));
    }

    public function create($facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        return view('admin.testimonials.create', compact('facility'));
    }

    public function store(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'relationship' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title_header' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'story' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);
        $testimonial = new Testimonial($validated);
        $testimonial->facility_id = $facility->id;
        // Handle avatar upload and save to photo_url
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('testimonials', 'public');
            $testimonial->photo_url = '/storage/' . $path;
        }
        $testimonial->save();
        return redirect()->route('admin.facilities.testimonials.index', $facility->id)
            ->with('success', 'Testimonial added successfully!');
    }

    public function edit($facilityId, $id)
    {
        $facility = Facility::findOrFail($facilityId);
        $testimonial = Testimonial::findOrFail($id);
        return view('admin.testimonials.edit', compact('facility', 'testimonial'));
    }

    public function update(Request $request, $facilityId, $id)
    {
        $facility = Facility::findOrFail($facilityId);
        $testimonial = Testimonial::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'relationship' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title_header' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'story' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);
        $testimonial->fill($validated);
        // Handle avatar upload and save to photo_url
        if ($request->hasFile('photo')) {
            // Delete old avatar if exists and is a local file
            if ($testimonial->photo_url && str_starts_with($testimonial->photo_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $testimonial->photo_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('testimonials', 'public');
            $testimonial->photo_url = '/storage/' . $path;
        }
        $testimonial->save();
        return redirect()->route('admin.facilities.testimonials.index', $facility->id)
            ->with('success', 'Testimonial updated successfully!');
    }

    public function destroy($facilityId, $id)
    {
        $facility = Facility::findOrFail($facilityId);
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();
        return redirect()->route('admin.facilities.testimonials.index', $facility->id)
            ->with('success', 'Testimonial deleted successfully!');
    }
}
