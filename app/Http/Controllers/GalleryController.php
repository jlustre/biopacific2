<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GalleryImage;
use App\Models\Facility;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('admin');
        $isFacilityAdmin = $user && $user->hasRole(['facility-admin', 'facility-dsd']);
        $facilityId = $isAdmin ? $request->input('facility_id') : ($user ? $user->facility_id : null);
        $facilities = $isAdmin ? Facility::all() : ($user && $user->facility ? collect([$user->facility]) : collect());
        $images = $facilityId ? GalleryImage::where('facility_id', $facilityId)->get() : collect();
        return view('gallery.index', compact('images', 'facilities', 'facilityId', 'isAdmin'));
    }

    public function create()
    {
        // Return a view for creating a new gallery image
        return view('gallery.create');
    }

    public function store(Request $request)
    {
        // Handle storing a new gallery image
        // Example: Gallery::create($request->all());
        return redirect()->route('gallery.index');
    }

    public function show($gallery)
    {
        // Return a view for showing a gallery image
        return view('gallery.show', compact('gallery'));
    }

    public function edit($gallery)
    {
        // Return a view for editing a gallery image
        return view('gallery.edit', compact('gallery'));
    }

    public function update(Request $request, $gallery)
    {
        // Handle updating a gallery image
        // Example: $gallery->update($request->all());
        return redirect()->route('gallery.index');
    }

    public function destroy($gallery)
    {
        // Handle deleting a gallery image
        // Example: $gallery->delete();
        return redirect()->route('gallery.index');
    }

    public function upload(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('admin');
        $isFacilityAdmin = $user && $user->hasRole(['facility-admin', 'facility-dsd']);
        $facilityId = $isAdmin ? $request->input('facility_id') : ($user ? $user->facility_id : null);
        if (!$facilityId) {
            return redirect()->back()->withErrors(['facility_id' => 'Facility is required.']);
        }
        $request->validate([
            'image' => 'required|image|max:4096',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $path = $request->file('image')->store('gallery', 'public');
        $image = GalleryImage::create([
            'facility_id' => $facilityId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image_url' => $path,
        ]);
        return redirect()->route('gallery.index', ['facility_id' => $facilityId])->with('success', 'Image uploaded successfully.');
    }

    public function delete($image)
    {
        // Handle deleting a specific gallery image
        // Example: $image->delete();
        return redirect()->route('gallery.index');
    }

    public function move($image, $direction)
    {
        // Handle moving a gallery image up/down
        // Example: $image->move($direction);
        return redirect()->route('gallery.index');
    }

    public function clearFacility($facility)
    {
        // Handle clearing all gallery images for a facility
        // Example: $facility->gallery()->delete();
        return redirect()->route('facilities.galleries.index', ['facility' => $facility]);
    }
}
