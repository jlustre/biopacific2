<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
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
