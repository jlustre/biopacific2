<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;

class FacilitySessionController extends Controller
{
    public function select($facility)
    {
        $facilityModel = Facility::where('slug', $facility)->orWhere('id', $facility)->firstOrFail();
        session(['facility_id' => $facilityModel->id]);
        // Redirect to dashboard or documents page as needed
        return redirect()->route('admin.facility.dashboard', ['facility' => $facilityModel->slug ?? $facilityModel->id]);
    }
}
