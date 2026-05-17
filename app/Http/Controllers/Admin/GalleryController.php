<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && $user->facility_id) {
            return (int) $user->facility_id;
        }

        return null;
    }

    protected function facilitiesForUser(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return Facility::where('id', $scopedFacilityId)->orderBy('name')->get();
        }

        return Facility::orderBy('name')->get();
    }

    protected function authorizeFacilityAccess(Request $request, int $facilityId): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId && $scopedFacilityId !== $facilityId) {
            abort(403, 'You do not have access to this facility\'s gallery.');
        }
    }

    protected function authorizeImageAccess(Request $request, GalleryImage $image): void
    {
        $this->authorizeFacilityAccess($request, (int) $image->facility_id);
    }

    protected function resolveFacilityId(Request $request, $facility = null): ?int
    {
        if ($facility instanceof Facility) {
            return (int) $facility->id;
        }

        if (is_numeric($facility)) {
            return (int) $facility;
        }

        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return $scopedFacilityId;
        }

        if ($request->filled('facility_id')) {
            return (int) $request->input('facility_id');
        }

        return null;
    }

    /**
     * Gallery hub or facility gallery view.
     */
    public function index(Request $request, $facility = null)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;
        $facilityId = $this->resolveFacilityId($request, $facility);

        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        if (! $facilityId) {
            if ($scopedFacilityId) {
                return redirect()->route('admin.facilities.galleries.index', ['facility' => $scopedFacilityId]);
            }

            return view('admin.galleries.index', [
                'facilities' => $facilities,
                'facility' => null,
                'images' => collect(),
                'scopedFacility' => $scopedFacility,
                'scopedFacilityId' => $scopedFacilityId,
                'canFilterFacilities' => $canFilterFacilities,
                'stats' => null,
            ]);
        }

        $this->authorizeFacilityAccess($request, $facilityId);
        $facility = Facility::findOrFail($facilityId);

        $images = GalleryImage::where('facility_id', $facilityId)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $stats = [
            'total' => $images->count(),
            'active' => $images->where('is_active', true)->count(),
            'featured' => $images->where('is_featured', true)->count(),
        ];

        return view('admin.galleries.index', [
            'facilities' => $facilities,
            'facility' => $facility,
            'images' => $images,
            'scopedFacility' => $scopedFacility,
            'scopedFacilityId' => $scopedFacilityId,
            'canFilterFacilities' => $canFilterFacilities,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $this->authorizeFacilityAccess($request, (int) $facility->id);

        return view('admin.galleries.create', compact('facility'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
            'caption' => 'nullable|string|max:255',
            'facility_id' => 'required|integer|exists:facilities,id',
        ]);

        $facilityId = (int) $request->input('facility_id');
        $this->authorizeFacilityAccess($request, $facilityId);

        $file = $request->file('image');
        $originalFilename = $file->getClientOriginalName();
        $facilityDir = 'gallery/facility_'.$facilityId;
        $path = $file->store($facilityDir, 'public');

        $maxOrder = GalleryImage::where('facility_id', $facilityId)->max('order');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;

        GalleryImage::create([
            'facility_id' => $facilityId,
            'image_url' => $path,
            'title' => $originalFilename,
            'caption' => $request->input('caption'),
            'order' => $nextOrder,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image uploaded successfully.');
    }

    public function update(Request $request, $facilityId, $imageId)
    {
        $image = GalleryImage::findOrFail($imageId);
        $this->authorizeImageAccess($request, $image);

        $request->validate([
            'caption' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $image->caption = $request->input('caption');
        $image->is_active = $request->boolean('is_active');
        $image->save();

        return redirect()
            ->route('admin.facilities.galleries.index', ['facility' => $image->facility_id])
            ->with('success', 'Image updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $image = GalleryImage::findOrFail($id);
        $this->authorizeImageAccess($request, $image);
        $facilityId = $image->facility_id;

        if ($image->image_url) {
            Storage::disk('public')->delete($image->image_url);
        }
        $image->delete();

        return redirect()
            ->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image deleted successfully.');
    }

    public function upload(Request $request, $facility_id)
    {
        $this->authorizeFacilityAccess($request, (int) $facility_id);

        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $file = $request->file('image');
        $originalFilename = $file->getClientOriginalName();

        $duplicate = GalleryImage::where('facility_id', $facility_id)
            ->where('title', $originalFilename)
            ->first();
        if ($duplicate) {
            return redirect()->back()->with('error', 'An image with this filename already exists for this facility.');
        }

        $facilityDir = 'gallery/facility_'.$facility_id;
        $path = $file->store($facilityDir, 'public');

        $maxOrder = GalleryImage::where('facility_id', $facility_id)->max('order');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;

        GalleryImage::create([
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
        $this->authorizeImageAccess($request, $image);
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

    public function delete(Request $request, GalleryImage $image)
    {
        $this->authorizeImageAccess($request, $image);
        $facilityId = $image->facility_id;

        if ($image->image_url) {
            Storage::disk('public')->delete($image->image_url);
        }
        $image->delete();

        return redirect()
            ->route('admin.facilities.galleries.index', ['facility' => $facilityId])
            ->with('success', 'Image deleted successfully.');
    }

    public function clearFacility(Request $request, $facility_id)
    {
        $this->authorizeFacilityAccess($request, (int) $facility_id);

        $facilityDir = 'gallery/facility_'.$facility_id;
        if (Storage::disk('public')->exists($facilityDir)) {
            $files = Storage::disk('public')->files($facilityDir);
            foreach ($files as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        GalleryImage::where('facility_id', $facility_id)->delete();

        return redirect()->back()->with('success', 'All gallery images for this facility have been deleted.');
    }
}
