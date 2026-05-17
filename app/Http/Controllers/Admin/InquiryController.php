<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
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

    protected function authorizeInquiryFacility(Request $request, ?int $facilityId): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId && $facilityId && $scopedFacilityId !== (int) $facilityId) {
            abort(403, 'You do not have access to inquiries for this facility.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;

        $query = Inquiry::with('facility')->orderByDesc('created_at');

        if ($scopedFacilityId) {
            $query->where('facility_id', $scopedFacilityId);
        } elseif ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $inquiries = $query->paginate(15)->withQueryString();
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.inquiries.index', compact(
            'inquiries',
            'facilities',
            'canFilterFacilities',
            'scopedFacility'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Inquiry $inquiry)
    {
        $inquiry->load('facility');
        $this->authorizeInquiryFacility($request, $inquiry->facility_id);

        return view('admin.inquiries.show', compact('inquiry'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Inquiry $inquiry)
    {
        $this->authorizeInquiryFacility($request, $inquiry->facility_id);
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry deleted successfully.');
    }
}
