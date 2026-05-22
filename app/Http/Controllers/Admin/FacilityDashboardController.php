<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityDashboardController extends Controller
{
    public function show(Facility $facility)
    {
        $user = auth()->user();
        // Facility-scoped roles may only access their assigned facility; admin/super-admin/rdhr may access any
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor']) && !$user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            if ((int) $user->facility_id !== (int) $facility->id) {
                abort(403, 'Unauthorized: You do not have access to this facility.');
            }
        }
        $facilityKey = $facility->getRouteKey();

        $stats = [
            'job_openings' => $facility->jobOpenings()->count(),
            'testimonials' => $facility->testimonials()->count(),
            'gallery_images' => $facility->galleryImages()->count(),
            'news_items' => $facility->news()->count(),
            'services' => $facility->services()->count(),
        ];

        return view('admin.facilities.dashboard', compact('facility', 'facilityKey', 'stats'));
    }
}
