<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Testimonial;

class FacilityTestimonialController extends Controller
{
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
            'role' => 'nullable|string|max:100',
            'avatar' => 'nullable|url|max:500',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'text' => 'required|string',
        ]);
        $testimonial = new Testimonial($validated);
        $testimonial->facility_id = $facility->id;
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
            'role' => 'nullable|string|max:100',
            'avatar' => 'nullable|url|max:500',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'text' => 'required|string',
        ]);
        $testimonial->update($validated);
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
