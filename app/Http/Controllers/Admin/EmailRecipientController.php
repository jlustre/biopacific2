<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailRecipient;
use App\Models\Facility;
use Illuminate\Http\Request;

class EmailRecipientController extends Controller
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

    protected function authorizeRecipientFacility(Request $request, ?int $facilityId): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId && $facilityId && $scopedFacilityId !== (int) $facilityId) {
            abort(403, 'You do not have access to recipients for this facility.');
        }
    }

    protected function findAuthorizedRecipient(Request $request, string $id): EmailRecipient
    {
        $emailRecipient = EmailRecipient::with('facility')->findOrFail($id);
        $this->authorizeRecipientFacility($request, $emailRecipient->facility_id);

        return $emailRecipient;
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

        $query = EmailRecipient::with('facility');

        if ($scopedFacilityId) {
            $query->where('facility_id', $scopedFacilityId);
        } elseif ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                    ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        $emailRecipients = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.email-recipients.index', compact(
            'emailRecipients',
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

        return view('admin.email-recipients.create', compact('facilities', 'scopedFacility', 'scopedFacilityId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $validated = $this->applyScopedFacilityToValidated($request, $validated);
        $this->authorizeRecipientFacility($request, (int) $validated['facility_id']);

        EmailRecipient::create($validated);

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $emailRecipient = $this->findAuthorizedRecipient($request, $id);

        return view('admin.email-recipients.show', compact('emailRecipient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $emailRecipient = $this->findAuthorizedRecipient($request, $id);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacilityId = $this->scopedFacilityId($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.email-recipients.edit', compact(
            'emailRecipient',
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
        $emailRecipient = $this->findAuthorizedRecipient($request, $id);

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $validated = $this->applyScopedFacilityToValidated($request, $validated);
        $this->authorizeRecipientFacility($request, (int) $validated['facility_id']);

        $emailRecipient->update($validated);

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $emailRecipient = $this->findAuthorizedRecipient($request, $id);
        $emailRecipient->delete();

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient deleted successfully.');
    }
}
