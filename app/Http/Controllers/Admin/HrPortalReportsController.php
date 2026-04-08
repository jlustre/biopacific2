<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;

class HrPortalReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isHrrd = $user->hasRole('hrrd');
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
                ->where(function($q) use ($roles, $userFacilityIds, $isHrrd) {
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
                    if ($isHrrd) {
                        $q->orWhere('visibility', 'admin'); // HRRD can see admin reports
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
