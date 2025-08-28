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
        view()->share('layoutTemplate', $facility->layout_template ?? 'default-template');

        return view('welcome');
    }
}
