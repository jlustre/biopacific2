<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
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

    protected function resolveFilterFacilityId(Request $request): ?int
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return $scopedFacilityId;
        }

        if ($request->filled('facility_id')) {
            return (int) $request->input('facility_id');
        }

        return null;
    }

    protected function applyScopeToQuery($query, Request $request, ?int $filterFacilityId = null)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilityId = $scopedFacilityId ?? $filterFacilityId;

        if (! $facilityId) {
            return $query;
        }

        return $query->where(function ($q) use ($facilityId) {
            $q->where('is_global', true)
                ->orWhereHas('facilities', fn ($f) => $f->where('facilities.id', $facilityId));
        });
    }

    protected function canManageService(Request $request, Service $service): bool
    {
        if (! $this->scopedFacilityId($request)) {
            return true;
        }

        if ($service->is_global) {
            return false;
        }

        $facilityId = $this->scopedFacilityId($request);

        return $service->facilities()->where('facilities.id', $facilityId)->exists();
    }

    public function index(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;
        $filterFacilityId = $this->resolveFilterFacilityId($request);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;
        $filterFacility = $filterFacilityId ? Facility::find($filterFacilityId) : null;

        $query = Service::with('facilities')->orderBy('order')->orderBy('name');
        $this->applyScopeToQuery($query, $request, $filterFacilityId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('scope')) {
            if ($request->input('scope') === 'global') {
                $query->where('is_global', true);
            } elseif ($request->input('scope') === 'local') {
                $query->where('is_global', false);
            }
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $services = $query->get();

        $allScoped = Service::query();
        $this->applyScopeToQuery($allScoped, $request, $filterFacilityId);

        $stats = [
            'total' => (clone $allScoped)->count(),
            'global' => (clone $allScoped)->where('is_global', true)->count(),
            'active' => (clone $allScoped)->where('is_active', true)->count(),
            'featured' => (clone $allScoped)->where('is_featured', true)->count(),
        ];

        return view('admin.services.index', compact(
            'services',
            'stats',
            'facilities',
            'scopedFacility',
            'scopedFacilityId',
            'canFilterFacilities',
            'filterFacilityId',
            'filterFacility'
        ));
    }

    public function create(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilities = $this->facilitiesForUser($request);

        return view('admin.services.create', compact('scopedFacilityId', 'facilities'));
    }

    public function store(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'detailed_description' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        if ($scopedFacilityId) {
            $data['is_global'] = false;
            $data['facility_id'] = $scopedFacilityId;
        } else {
            $data['is_global'] = $request->boolean('is_global');
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);

        $service = Service::create(collect($data)->except('facility_id')->all());

        $attachFacilityId = $scopedFacilityId ?? ($request->filled('facility_id') ? (int) $request->facility_id : null);
        if ($attachFacilityId && ! $service->is_global) {
            $service->facilities()->syncWithoutDetaching([$attachFacilityId]);
        }

        return redirect()
            ->route('admin.services.index', $attachFacilityId ? ['facility_id' => $attachFacilityId] : [])
            ->with('success', 'Service created successfully.');
    }

    public function edit(Request $request, Service $service)
    {
        if (! $this->canManageService($request, $service)) {
            abort(403, 'You cannot edit global services.');
        }

        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilities = $this->facilitiesForUser($request);

        return view('admin.services.edit', compact('service', 'scopedFacilityId', 'facilities'));
    }

    public function update(Request $request, Service $service)
    {
        if (! $this->canManageService($request, $service)) {
            abort(403, 'You cannot edit global services.');
        }

        $scopedFacilityId = $this->scopedFacilityId($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'detailed_description' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($scopedFacilityId) {
            $data['is_global'] = false;
        } else {
            $data['is_global'] = $request->boolean('is_global');
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        $service->update($data);

        return redirect()
            ->route('admin.services.index', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : [])
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Request $request, Service $service)
    {
        if (! $this->canManageService($request, $service)) {
            abort(403, 'You cannot delete global services.');
        }

        $facilityCount = $service->facilities()->count();
        if ($facilityCount > 0 && $service->is_global) {
            return redirect()
                ->route('admin.services.index')
                ->with('error', "Cannot delete: this service is assigned to {$facilityCount} facility(ies).");
        }

        $scopedFacilityId = $this->scopedFacilityId($request);
        $service->delete();

        return redirect()
            ->route('admin.services.index', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : [])
            ->with('success', 'Service deleted successfully.');
    }
}
