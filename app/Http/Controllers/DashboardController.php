<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all active facilities
        $facilities = Facility::where('is_active', true)
                             ->orderBy('name')
                             ->get();

        // Group facilities by state for better organization
        $facilitiesByState = $facilities->groupBy('state');

        return view('dashboard.index', compact('facilities', 'facilitiesByState'));
    }

    public function facility($id)
    {
        $facility = Facility::findOrFail($id);

        // Temporarily bind this facility as current for preview
        app()->instance('current_facility', $facility);

        // Share facility data with views (as array for consistency)
        view()->share('facility', $facility->toArray());

        // Share layout template (it's a string field, not a relationship)
<<<<<<< HEAD
        view()->share('layoutTemplate', $facility->layout_template ?? 'default-template');
=======
        view()->share('layoutTemplate', $facility->layout_template ?? 'layout1');
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

        return view('welcome');
    }
}
