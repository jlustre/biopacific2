<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\WebContent;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        return view('admin.facilities.create');
    }

    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'beds' => 'nullable|integer|min:0',
            'domain' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        $facility = new Facility($validated);
        $facility->is_active = (bool) $request->input('is_active');

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('facilities', 'public');
            $facility->photo_url = $path;
        }

        $facility->save();

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        $layoutTemplates = ['default-template', 'layout2', 'layout3'];
        $activeWebContent = $facility->webContent;
        $selectedLayoutTemplate = $activeWebContent->layout_template ?? 'default-template';
        $webContents = \App\Models\WebContent::where('facility_id', $facility->id)->get();
        $colorSchemes = \App\Models\ColorScheme::orderBy('name')->get();
        return view('admin.facilities.edit', compact(
            'facility',
            'layoutTemplates',
            'activeWebContent',
            'selectedLayoutTemplate',
            'webContents',
            'colorSchemes'
        ));
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        // Validate and update facility details
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            // ...other validations...
        ]);

        $facility->name = $request->input('name');
        $facility->address = $request->input('address');
        // ...update other fields...

        // Save active sections and variances
        $sections = $request->input('sections', []); // array of keys
        $variances = $request->input('variances', []);

        // Assuming you have a WebContent model related to Facility
        $webContent = $facility->webContent ?? new WebContent();
        $webContent->facility_id = $facility->id;
        $webContent->sections = $sections;
        $webContent->variances = $variances;
        $webContent->layout_template = $request->input('layout_template');
        $webContent->save();

        return redirect()->route('admin.facilities.edit', $facility->id)
            ->with('success', 'Facility updated successfully');
    }

    // ...existing methods...
}