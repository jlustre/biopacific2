<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\WebContent;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // ...existing methods...

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);

        $layoutTemplates = ['default-template', 'layout2', 'layout3']; // Your templates

        $activeWebContent = $facility->webContent;
        $selectedLayoutTemplate = $activeWebContent->layout_template ?? 'default-template';

        // Fetch all web contents for this facility (adjust if needed)
        $webContents = \App\Models\WebContent::where('facility_id', $facility->id)->get();

        return view('admin.facilities.edit', compact(
            'facility',
            'layoutTemplates',
            'activeWebContent',
            'selectedLayoutTemplate',
            'webContents' // <-- Add this line
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