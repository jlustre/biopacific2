<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MemberPortalLayout;
use App\Support\Rbac\Permissions;

class HrPortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->can(Permissions::ACCESS_HR_PORTAL)) {
            abort(403, 'Unauthorized access to HR portal.');
        }

        // System admins and RDHR see all facilities
        if (MemberPortalLayout::userIsSystemAdmin($user) || $user->hasRole('rdhr')) {
            $facilities = \App\Models\Facility::all();
            $roles = $user->getRoleNames();
            return view('admin.hr-portal.index', compact('facilities', 'roles'));
        } elseif ($user->hasRole('facility-admin') || $user->hasRole('facility-dsd') || $user->hasRole('don')) {
            if ($user->facility_id) {
                $facility = \App\Models\Facility::find($user->facility_id);
                if ($facility) {
                    return redirect()->route('user.hr-portal', ['facility' => $facility->slug ?? $facility->id]);
                }
            }
            // If no facility assigned, show error with instructions
            return view('admin.hr-portal.no-facility', [
                'message' => 'No facility assigned yet. Please contact your administrator to assign a facility to your account.',
                'userRole' => $user->getRoleNames()->first()
            ]);
        } else {
            abort(403, 'Unauthorized access to HR portal.');
        }
    }
}
