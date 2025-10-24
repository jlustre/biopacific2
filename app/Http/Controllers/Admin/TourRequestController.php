<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TourRequest;
use App\Models\Facility;

class TourRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TourRequest::with('facility');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        $tourRequests = $query->paginate(10);
        $facilities = Facility::all();

        return view('admin.tour-requests.index', compact('tourRequests', 'facilities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tour-requests.create');
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

        TourRequest::create($validated);

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tourRequest = TourRequest::findOrFail($id);
        return view('admin.tour-requests.show', compact('tourRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tourRequest = TourRequest::findOrFail($id);
        return view('admin.tour-requests.edit', compact('tourRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

        $tourRequest = TourRequest::findOrFail($id);
        $tourRequest->update($validated);

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tourRequest = TourRequest::findOrFail($id);
        $tourRequest->delete();

        return redirect()->route('admin.tour-requests.index')->with('success', 'Tour Request deleted successfully.');
    }
}
