<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function create(Request $request)
    {
        $facilityId = $request->input('facility_id');
        return view('admin.services.create', compact('facilityId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $service = Service::create($validated);
        return redirect()->back()->with('success', 'Service added.');
    }

        public function destroy(Service $service)
    {
        $facilityCount = $service->facilities()->count();
        if ($facilityCount > 0) {
            return redirect()->back()->withErrors(["Cannot delete: This service is still assigned to $facilityCount facility(s)."]);
        }
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted.');
    }
    
    public function edit(Service $service)
    {
        $facilityId = request()->input('facility_id');
        return view('admin.services.create', [
            'facilityId' => $facilityId,
            'service' => $service
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $service->update($validated);
        return redirect()->back()->with('success', 'Service updated.');
    }
}
