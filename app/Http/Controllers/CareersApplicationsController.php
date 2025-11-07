<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Facility;

class CareersApplicationsController extends Controller
{
    public function index($facilityId)
    {
        $applications = JobApplication::whereHas('jobOpening', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })->with('jobOpening')->get();
        
        // Get active job openings for this facility
        $jobOpenings = JobOpening::where('facility_id', $facilityId)
            ->where('active', true)
            ->orderBy('posted_at', 'desc')
            ->get();
        
        $facility = Facility::findOrFail($facilityId);
        return view('partials.careers.default', compact('applications', 'jobOpenings', 'facility'));
    }
}
