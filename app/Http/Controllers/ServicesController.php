<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    // List all services
    public function index()
    {
    $services = Service::orderBy('order')->get();
        return view('admin.services.index', compact('services'));
    }

    // Show create form
    public function create()
    {
        return view('admin.services.create');
    }

    // Store new service
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'detailed_description' => 'nullable|string',
        ]);
        $data['is_global'] = $request->has('is_global');
        Service::create($data);
        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    // Show edit form
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    // Update service
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'is_global' => 'boolean',
            'detailed_description' => 'nullable|string',
        ]);
        $data['is_global'] = $request->has('is_global');
        $service->update($data);
        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    // Delete service
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}
