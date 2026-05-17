<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\TourRequest;
use Illuminate\Http\Request;

class TourRequestController extends Controller
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

    protected function authorizeTourRequestFacility(Request $request, ?int $facilityId): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId && $facilityId && $scopedFacilityId !== (int) $facilityId) {
            abort(403, 'You do not have access to tour requests for this facility.');
        }
    }

    protected function findAuthorizedTourRequest(Request $request, string $id): TourRequest
    {
        $tourRequest = TourRequest::with('facility')->findOrFail($id);
        $this->authorizeTourRequestFacility($request, $tourRequest->facility_id);

        return $tourRequest;
    }

    protected function applyScopedFacilityToValidated(Request $request, array $validated): array
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            $validated['facility_id'] = $scopedFacilityId;
        }

        return $validated;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;

        $query = TourRequest::with('facility')->orderByDesc('created_at');

        if ($scopedFacilityId) {
            $query->where('facility_id', $scopedFacilityId);
        } elseif ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $tourRequests = $query->paginate(10)->withQueryString();
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.tour-requests.index', compact(
            'tourRequests',
            'facilities',
            'canFilterFacilities',
            'scopedFacility'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.tour-requests.create', compact('facilities', 'scopedFacility', 'scopedFacilityId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'full_name' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:20',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|max:255',
            'message' => 'nullable',
        ]);

        $validated = $this->applyScopedFacilityToValidated($request, $validated);
        $this->authorizeTourRequestFacility($request, (int) $validated['facility_id']);

        TourRequest::create($validated);

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $tourRequest = $this->findAuthorizedTourRequest($request, $id);

        return view('admin.tour-requests.show', compact('tourRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $tourRequest = $this->findAuthorizedTourRequest($request, $id);
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.tour-requests.edit', compact(
            'tourRequest',
            'facilities',
            'scopedFacility',
            'scopedFacilityId'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tourRequest = $this->findAuthorizedTourRequest($request, $id);

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'full_name' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:20',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|max:255',
            'message' => 'nullable',
        ]);

        $validated = $this->applyScopedFacilityToValidated($request, $validated);
        $this->authorizeTourRequestFacility($request, (int) $validated['facility_id']);

        $tourRequest->update($validated);

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $tourRequest = $this->findAuthorizedTourRequest($request, $id);
        $tourRequest->delete();

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request deleted successfully.');
    }
}
