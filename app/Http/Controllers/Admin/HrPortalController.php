<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HrPortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // HRRD and admin see all facilities
        if ($user->hasRole('admin') || $user->hasRole('hrrd')) {
            $facilities = \App\Models\Facility::all();
            $roles = $user->getRoleNames();
            return view('admin.hr-portal.index', compact('facilities', 'roles'));
        } elseif ($user->hasRole('facility-admin') || $user->hasRole('facility-dsd')) {
            // Redirect directly to their assigned facility dashboard
            if ($user->facility_id) {
                $facility = \App\Models\Facility::find($user->facility_id);
                if ($facility) {
                    return redirect()->route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]);
                }
            }
            // If no facility, show nothing or error
            abort(403, 'No assigned facility.');
        } else {
            abort(403);
        }
    }
}
