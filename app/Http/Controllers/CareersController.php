<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CareersController extends Controller
{
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && $user->facility_id) {
            return (int) $user->facility_id;
        }

        return null;
    }

    protected function facilitiesForUser(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return Facility::where('id', $scopedFacilityId)->orderBy('name')->get();
        }

        return Facility::orderBy('name')->get();
    }

    protected function authorizeFacilityAccess(Request $request, int $facilityId): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId && $scopedFacilityId !== $facilityId) {
            abort(403, 'You do not have access to this facility\'s careers.');
        }
    }

    protected function authorizeJobOpeningAccess(Request $request, JobOpening $jobOpening): void
    {
        $this->authorizeFacilityAccess($request, (int) $jobOpening->facility_id);
    }

    protected function resolveFacilityId(Request $request): ?int
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return $scopedFacilityId;
        }

        if ($request->filled('facility_id')) {
            return (int) $request->input('facility_id');
        }

        if ($request->filled('facility')) {
            return (int) $request->input('facility');
        }

        return null;
    }

    public function indexAll(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;
        $facilityId = $this->resolveFacilityId($request);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        $facility = null;
        $jobOpenings = collect();
        $stats = [
            'total' => 0,
            'open' => 0,
            'active' => 0,
            'applications' => 0,
        ];

        if ($facilityId) {
            $this->authorizeFacilityAccess($request, $facilityId);
            $facility = Facility::findOrFail($facilityId);

            $query = JobOpening::where('facility_id', $facilityId)
                ->withCount('applications')
                ->orderByDesc('posted_at')
                ->orderByDesc('created_at');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('active')) {
                if ($request->input('active') === '1') {
                    $query->where('active', true);
                } elseif ($request->input('active') === '0') {
                    $query->where('active', false);
                }
            }

            $jobOpenings = $query->get();

            $allForFacility = JobOpening::where('facility_id', $facilityId);
            $stats = [
                'total' => (clone $allForFacility)->count(),
                'open' => (clone $allForFacility)->where('status', 'open')->count(),
                'active' => (clone $allForFacility)->where('active', true)->count(),
                'applications' => JobApplication::whereIn(
                    'job_opening_id',
                    (clone $allForFacility)->pluck('id')
                )->count(),
            ];
        }

        return view('admin.facilities.webcontents.careers', compact(
            'facilities',
            'facility',
            'facilityId',
            'jobOpenings',
            'stats',
            'scopedFacility',
            'scopedFacilityId',
            'canFilterFacilities'
        ));
    }

    public function templates(Request $request)
    {
        return view('admin.facilities.webcontents.careers-templates', [
            'facilityId' => $this->resolveFacilityId($request),
        ]);
    }

    public function index(Request $request, $facility = null)
    {
        if ($facility) {
            $request->merge(['facility_id' => is_object($facility) ? $facility->id : $facility]);
        }

        return $this->indexAll($request);
    }

    public function store(Request $request, $facility = null)
    {
        if ($facility) {
            $request->merge(['facility_id' => is_object($facility) ? $facility->id : $facility]);
        }

        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $this->authorizeFacilityAccess($request, (int) $data['facility_id']);
        JobOpening::create($data);

        return redirect()
            ->route('admin.facilities.webcontents.careers', ['facility_id' => $data['facility_id']])
            ->with('success', 'Job opening created successfully.');
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $this->authorizeJobOpeningAccess($request, $jobOpening);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $jobOpening->update($data);

        return redirect()
            ->route('admin.facilities.webcontents.careers', ['facility_id' => $jobOpening->facility_id])
            ->with('success', 'Job opening updated successfully.');
    }

    public function destroy(Request $request, JobOpening $jobOpening)
    {
        $this->authorizeJobOpeningAccess($request, $jobOpening);
        $facilityId = $jobOpening->facility_id;
        $jobOpening->delete();

        return redirect()
            ->route('admin.facilities.webcontents.careers', ['facility_id' => $facilityId])
            ->with('success', 'Job opening deleted successfully.');
    }

    public function applications(Request $request, JobOpening $jobOpening)
    {
        $this->authorizeJobOpeningAccess($request, $jobOpening);
        $applications = $jobOpening->applications()->latest()->get();

        return view('admin.facilities.webcontents.career_applications', compact('jobOpening', 'applications'));
    }

    public function updateApplication(Request $request, JobOpening $jobOpening, JobApplication $jobApplication)
    {
        $this->authorizeJobOpeningAccess($request, $jobOpening);

        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
        ]);

        $jobApplication->update(['status' => $data['status']]);

        return back()->with('success', 'Application status updated.');
    }

    public function destroyApplication(Request $request, JobOpening $jobOpening, JobApplication $jobApplication)
    {
        $this->authorizeJobOpeningAccess($request, $jobOpening);
        $jobApplication->delete();

        return back()->with('success', 'Application deleted.');
    }

    public function applicationDetails(JobApplication $jobApplication)
    {
        return view('admin.facilities.webcontents.partials.application_details', compact('jobApplication'));
    }

    public function downloadResume(JobOpening $jobOpening, JobApplication $jobApplication)
    {
        if (! $jobApplication->resume_path || ! Storage::disk('public')->exists($jobApplication->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }

        $filePath = Storage::disk('public')->path($jobApplication->resume_path);
        $fileName = strtolower($jobApplication->first_name).'_'.strtolower($jobApplication->last_name).'_resume.pdf';

        return response()->download($filePath, $fileName);
    }

    public function previewResume(JobOpening $jobOpening, JobApplication $jobApplication)
    {
        if (! $jobApplication->resume_path || ! Storage::disk('public')->exists($jobApplication->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }

        $filePath = Storage::disk('public')->path($jobApplication->resume_path);
        $extension = pathinfo($jobApplication->resume_path, PATHINFO_EXTENSION);

        $mimeType = match (strtolower($extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($jobApplication->resume_path).'"',
        ]);
    }
}
