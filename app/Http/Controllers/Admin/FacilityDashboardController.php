<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;

class FacilityDashboardController extends Controller
{
    public function show(Facility $facility)
    {
        return redirect()->route('member.facility.dashboard', ['facility' => $facility->getRouteKey()]);
    }
}
