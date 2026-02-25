<?php

namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\EmployeeChecklist;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\PreEmploymentApplication;
use App\Models\HiringActivityLog;
use App\Models\EmployeeDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuickActionsController extends Controller
{
    protected function authorizeFacilityAccess(Facility $facility)
    {
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('hrrd')) {
            return true;
        }
        // If user is facility-admin, facility-dsd, or facility-editor, check assignment
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor'])) {
            // Adjust this logic to match your actual assignment relationship
            if (method_exists($user, 'facilities')) {
                // Many-to-many
                if ($user->facilities->contains('id', $facility->id)) {
                    return true;
                }
            } elseif (isset($user->facility_id)) {
                // Single facility assignment
                if ($user->facility_id == $facility->id) {
                    return true;
                }
            }
        }
        abort(403, 'Unauthorized facility access.');
    }

    public function hiring(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        
        // Get all job openings for this facility
        $jobOpenings = $facility->jobOpenings()->get();
        
        // Get all job applications for this facility's job openings
        $applications = \App\Models\JobApplication::whereIn('job_opening_id', $jobOpenings->pluck('id'))
            ->with(['jobOpening', 'user'])
            ->orderByDesc('created_at')
            ->get();
        
        // Get all pre-employment applications related to this facility
        $preEmploymentApplications = \App\Models\PreEmploymentApplication::with(['user'])
            ->orderByDesc('created_at')
            ->get();
        
        // Count statistics
        $stats = [
            'total_openings' => $jobOpenings->count(),
            'open_openings' => $jobOpenings->where('active', true)->count(),
            'total_applicants' => $applications->count(),
            'pending_applications' => $applications->where('status', 'pending')->count(),
            'submitted_preemployment' => $preEmploymentApplications->where('status', 'submitted')->count(),
            'completed_preemployment' => $preEmploymentApplications->where('status', 'completed')->count(),
        ];
        
        return view('admin.facilities.hiring', compact('facility', 'jobOpenings', 'applications', 'preEmploymentApplications', 'stats'));
    }

    public function reviewPreEmployment(Facility $facility, $application)
    {
        $this->authorizeFacilityAccess($facility);
        
        $application = PreEmploymentApplication::findOrFail($application);
        
        return view('admin.facilities.pre-employment-review', compact('facility', 'application'));
    }

    public function updatePreEmploymentStatus(Request $request, Facility $facility, $application)
    {
        $this->authorizeFacilityAccess($facility);

        $validated = $request->validate([
            'status' => ['required', 'in:returned,completed'],
            'form_type' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = PreEmploymentApplication::findOrFail($application);
        $status = $validated['status'];
        $formType = $validated['form_type'] ?? null;
        $notes = $validated['notes'] ?? null;
        $statusFrom = $application->status;
        
        $application->update(['status' => $status]);

        $checklistUpdates = ['status' => $status];
        if ($status === 'returned') {
            $checklistUpdates['returned_at'] = now();
            $checklistUpdates['completed_at'] = null;
            $checklistUpdates['returned_by'] = Auth::id();
        }
        if ($status === 'completed') {
            $checklistUpdates['completed_at'] = now();
        }

        EmployeeChecklist::where('user_id', $application->user_id)
            ->where('item_key', 'application_packet')
            ->update($checklistUpdates);

        // Log the activity
        HiringActivityLog::create([
            'facility_id' => $facility->id,
            'pre_employment_application_id' => $application->id,
            'performed_by' => Auth::id(),
            'recipient_id' => $application->user_id,
            'activity_type' => $status,
            'form_type' => $formType,
            'description' => $formType ? 
                'Application marked as ' . ucfirst($status) . ' (' . $this->getFormLabel($formType) . ')' :
                'Application marked as ' . ucfirst($status),
            'notes' => $notes,
            'status_from' => $statusFrom,
            'status_to' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return redirect()
            ->route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $application->id])
            ->with('success', 'Application status updated to ' . ucfirst($status) . '.');
    }

    public function createPreEmploymentPdf(Facility $facility, $application)
    {
        $this->authorizeFacilityAccess($facility);

        $application = PreEmploymentApplication::with(['user', 'position'])->findOrFail($application);
        $applicantId = $application->user_id;

        $namePart = Str::slug(trim(($application->last_name ?? 'applicant') . '-' . ($application->first_name ?? '')));
        $fileName = 'application_packet_' . $application->id . '_' . $namePart . '.pdf';
        $directory = 'documents/facility_' . $facility->id . '/applicant_' . ($applicantId ?? 'unknown');
        $filePath = $directory . '/' . $fileName;

        $pdf = Pdf::loadView('admin.facilities.application-form-pdf', [
            'facility' => $facility,
            'application' => $application,
        ])->setPaper('letter');

        $content = $pdf->output();
        Storage::disk('local')->put($filePath, $content);

        EmployeeDocument::create([
            'facility_id' => $facility->id,
            'user_id' => $applicantId,
            'pre_employment_application_id' => $application->id,
            'document_type' => 'application_packet',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => 'application/pdf',
            'file_size' => strlen($content),
            'created_by' => Auth::id(),
        ]);

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    private function getFormLabel($formType): string
    {
        $labels = [
            'application_packet' => 'Application Packet',
            'personal' => 'Personal Information',
            'position' => 'Position Desired',
            'drivers_license' => "Driver's License",
            'work_authorization' => 'Work Authorization',
            'work_experience' => 'Work Experience',
            'education' => 'Education',
            'previous_addresses' => 'Previous Addresses',
            'other' => 'Other/Multiple Sections',
        ];
        return $labels[$formType] ?? 'Unknown Form';
    }

    public function termination(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.termination', compact('facility'));
    }

    public function employees(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.employees', compact('facility'));
    }

    public function attendance(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.attendance', compact('facility'));
    }

    public function documents(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.documents', compact('facility'));
    }

    public function requests(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        return view('admin.facilities.requests', compact('facility'));
    }

    public function downloadDocument(Facility $facility, EmployeeDocument $document)
    {
        $this->authorizeFacilityAccess($facility);

        if ($document->facility_id !== $facility->id) {
            abort(403);
        }

        $path = storage_path('app/' . $document->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $document->file_name);
    }

    public function deleteDocument(Facility $facility, EmployeeDocument $document)
    {
        $this->authorizeFacilityAccess($facility);

        if ($document->facility_id !== $facility->id) {
            abort(403);
        }

        $path = storage_path('app/' . $document->file_path);
        if (file_exists($path)) {
            unlink($path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'PDF document deleted successfully.');
    }
}