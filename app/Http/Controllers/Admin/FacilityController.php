<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\WebContent;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    // ...existing methods...

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
        $sections = array_keys($request->input('sections', []));
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