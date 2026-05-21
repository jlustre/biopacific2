<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MemberPortalLayout;
use Illuminate\Http\Request;
use App\Models\Report;

class HrPortalReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = MemberPortalLayout::userIsSystemAdmin($user);
        $isRdhr = $user->hasRole('rdhr');
        $roles = $user->getRoleNames()->toArray();
        $userFacilityIds = collect();
        if (method_exists($user, 'facilities')) {
            $userFacilityIds = $user->facilities->pluck('id');
        } elseif (isset($user->facility_id)) {
            $userFacilityIds = collect([$user->facility_id]);
        }

        // Admins see all reports
        if ($isAdmin) {
            $reports = Report::where('is_active', true)->get();
        } else {
            $reports = Report::where('is_active', true)
                ->where(function($q) use ($roles, $userFacilityIds, $isRdhr) {
                    $q->where('visibility', 'all');
                    $q->orWhere(function($q2) use ($roles) {
                        $q2->where('visibility', 'roles')
                            ->whereJsonContains('visible_roles', $roles);
                    });
                    $q->orWhere(function($q2) use ($userFacilityIds) {
                        $q2->where('visibility', 'facilities')
                            ->where(function($q3) use ($userFacilityIds) {
                                foreach ($userFacilityIds as $fid) {
                                    $q3->orWhereJsonContains('visible_facilities', $fid);
                                }
                            });
                    });
                    if ($isRdhr) {
                        $q->orWhere('visibility', 'admin'); // RDHR can see admin reports
                    }
                })
                ->get();
        }
        return view('admin.hr-portal.reports', [
            'reports' => $reports,
            'isAdmin' => $isAdmin,
        ]);
    }
}
