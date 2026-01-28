<?php

namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;

class QuickActionsController extends Controller
{
    protected function authorizeFacilityAccess(Facility $facility)
    {
        $user = auth()->user();
        if ($user->hasRole('admin') || $user->hasRole('hrrd')) {
            return true;
        }
        // If user is facility-admin, facility-dsd, or facility-editor, check assignment
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor'])) {
            // Adjust this logic to match your actual assignment relationship
            if (method_exists($user, 'facilities')) {
                // Many-to-many
                if ($user->facilities->contains('id', $facility->id)) {
                    return true;
                }
            } elseif (isset($user->facility_id)) {
                // Single facility assignment
                if ($user->facility_id == $facility->id) {
                    return true;
                }
            }
        }
        abort(403, 'Unauthorized facility access.');
    }

    public function hiring(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.hiring', compact('facility'));
    }

    public function termination(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.termination', compact('facility'));
    }

    public function employees(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.employees', compact('facility'));
    }

    public function attendance(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.attendance', compact('facility'));
    }

    public function documents(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.documents', compact('facility'));
    }

    public function requests(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.requests', compact('facility'));
    }
}
