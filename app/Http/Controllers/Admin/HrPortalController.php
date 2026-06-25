<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
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

        // System admins and RDHR use the HR hub with management cards (/hr-portal).
        if (MemberPortalLayout::userIsSystemAdmin($user) || $user->hasRole('rdhr')) {
            return redirect()->route('user.hr-portal');
        }

        if ($user->hasRole('facility-admin') || $user->hasRole('facility-dsd') || $user->hasRole('don')) {
            if ($user->facility_id) {
                $facility = Facility::find($user->facility_id);
                if ($facility) {
                    return redirect()->route('user.hr-portal', ['facility' => $facility->slug ?? $facility->id]);
                }
            }

            return view('admin.hr-portal.no-facility', [
                'message' => 'No facility assigned yet. Please contact your administrator to assign a facility to your account.',
                'userRole' => $user->getRoleNames()->first(),
            ]);
        }

        abort(403, 'Unauthorized access to HR portal.');
    }
}
