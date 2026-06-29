<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Support\SelectedFacility;
use Illuminate\Http\Request;

class FacilitySessionController extends Controller
{
    public function select(Request $request, $facility)
    {
        $facilityModel = Facility::query()
            ->where('slug', $facility)
            ->orWhere('id', $facility)
            ->firstOrFail();

        if (! SelectedFacility::userCanAccessFacility($request->user(), $facilityModel)) {
            abort(403, 'You do not have access to this facility.');
        }

        SelectedFacility::remember($facilityModel);

        $redirect = $request->query('redirect');
        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, '/')) {
            return redirect($redirect);
        }

        return redirect()->route('member.facility.dashboard', ['facility' => $facilityModel->getRouteKey()]);
    }
}
