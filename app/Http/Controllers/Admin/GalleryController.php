<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller {
    /**
     * Display a listing of the galleries (facility selection).
     */
    public function index(Request $request, $facilityId = null)
    {
        if ($facilityId) {
             $facilities = \App\Models\Facility::orderBy('name')->get();
            // Show gallery images for a specific facility
            $facility = \App\Models\Facility::findOrFail($facilityId);
            $images = \App\Models\GalleryImage::where('facility_id', $facilityId)->orderBy('order')->get();
            return view('admin.galleries.index', compact('facility', 'images', 'facilities'));
        } else {
            // Show facility selection
            $facilities = \App\Models\Facility::orderBy('name')->get();
            $type = 'gallery';
            return view('admin.galleries.gallery-facility-selection', compact('facilities', 'type'));
        }
    }

    /**
     * Show the form for creating a new gallery image for a facility.
     */
    public function create($facilityId)
    {
        $facility = \App\Models\Facility::findOrFail($facilityId);
        return view('admin.galleries.create', compact('facility'));
    }

    /**
     * Store a newly uploaded gallery image for a facility.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
            'caption' => 'nullable|string|max:255',
            'facility_id' => 'required|integer|exists:facilities,id',
        ]);
        $facilityId = $request->input('facility_id');
        $facility = \App\Models\Facility::findOrFail($facilityId);
        $file = $request->file('image');
        $originalFilename = $file->getClientOriginalName();
        $facilityDir = 'gallery/facility_' . $facilityId;
        $path = $file->store($facilityDir, 'public');
        $maxOrder = \App\Models\GalleryImage::where('facility_id', $facilityId)->max('order');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;
        $image = \App\Models\GalleryImage::create([
            'facility_id' => $facilityId,
            'image_url' => $path,
            'title' => $originalFilename,
            'caption' => $request->input('caption'),
            'order' => $nextOrder,
            'is_active' => true,
        ]);
        return redirect()->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image uploaded successfully.');
    }

    /**
     * Show the form for editing a gallery image.
     */
    public function edit($facilityId, $imageId)
    {
        $facility = \App\Models\Facility::findOrFail($facilityId);
        $image = \App\Models\GalleryImage::findOrFail($imageId);
        return view('admin.facilities.webcontents.gallery-edit', compact('facility', 'image'));
    }

    /**
     * Update the specified gallery image.
     */
    public function update(Request $request, $facilityId, $imageId)
    {
        $image = \App\Models\GalleryImage::findOrFail($imageId);
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        $image->caption = $request->input('caption');
        $image->is_active = $request->has('is_active');
        $image->save();
        return redirect()->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image updated successfully.');
    }

    /**
     * Remove the specified gallery image.
     */
    public function destroy($facilityId, $imageId)
    {
        $image = \App\Models\GalleryImage::findOrFail($imageId);
        if ($image->image_url) {
            \Storage::disk('public')->delete($image->image_url);
        }
        $image->delete();
        return redirect()->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image deleted successfully.');
    }

    public function upload(Request $request, $facility_id)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $facilityDir = 'gallery/facility_' . $facility_id;
        $file = $request->file('image');
        $originalFilename = $file->getClientOriginalName();

        // Prevent duplicate upload for same facility and filename
        $duplicate = GalleryImage::where('facility_id', $facility_id)
            ->where('title', $originalFilename)
            ->first();
        if ($duplicate) {
            return redirect()->back()->with('error', 'An image with this filename already exists for this facility.');
        }

        $maxOrder = GalleryImage::where('facility_id', $facility_id)->max('order');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;

        $path = $file->store($facilityDir, 'public');

        $image = GalleryImage::create([
            'facility_id' => $facility_id,
            'image_url' => $path,
            'title' => $originalFilename,
            'order' => $nextOrder,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Image uploaded successfully.');
    }

        public function move(Request $request, GalleryImage $image, $direction)
    {
        $facility_id = $image->facility_id;
        if ($direction === 'up') {
            $swap = GalleryImage::where('facility_id', $facility_id)
                ->where('order', '<', $image->order)
                ->orderByDesc('order')
                ->first();
        } else {
            $swap = GalleryImage::where('facility_id', $facility_id)
                ->where('order', '>', $image->order)
                ->orderBy('order')
                ->first();
        }
        if ($swap) {
            $temp = $image->order;
            $image->order = $swap->order;
            $swap->order = $temp;
            $image->save();
            $swap->save();
        }
        return redirect()->back();
    }

    public function delete(GalleryImage $image)
    {
        if ($image->image_url) {
            Storage::disk('public')->delete($image->image_url);
        }
        $image->delete();
        return redirect()->back()->with('success', 'Image deleted successfully.');
    }

    public function deleteByFilename(GalleryImage $image)
    {
        // Delete all files in storage for this facility with the same original filename
        $facility_id = $image->facility_id;
        $originalFilename = $image->title;
        $facilityDir = 'gallery/facility_' . $facility_id;
        $files = Storage::disk('public')->files($facilityDir);
        foreach ($files as $file) {
            if (basename($file) === $originalFilename) {
                Storage::disk('public')->delete($file);
            }
        }
        // Delete all DB records for this facility and filename
        GalleryImage::where('facility_id', $facility_id)
            ->where('title', $originalFilename)
            ->delete();
        return redirect()->back()->with('success', 'Image(s) deleted successfully.');
    }

    public function clearFacility(Request $request, $facility_id)
    {
        $facilityDir = 'gallery/facility_' . $facility_id;
        $files = Storage::disk('public')->files($facilityDir);
        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }
        GalleryImage::where('facility_id', $facility_id)->delete();
        return redirect()->back()->with('success', 'All gallery images for this facility have been deleted.');
    }
}
