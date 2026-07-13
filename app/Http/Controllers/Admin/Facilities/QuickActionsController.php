<?php

namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\EmployeeChecklist;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\PreEmploymentApplication;
use App\Models\HiringActivityLog;
use App\Models\EmployeeDocument;
use App\Models\Document;
use App\Models\Department;
use App\Models\JobApplication;
use App\Models\Position;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Support\MemberPortalLayout;

class QuickActionsController extends Controller
{
    public function viewDocument(Facility $facility, EmployeeDocument $document)
    {
        $this->authorizeFacilityAccess($facility);

        if ($document->facility_id !== $facility->id) {
            abort(403);
        }

        $path = storage_path('app/' . $document->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        $mime = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ]);
    }

    protected function authorizeFacilityAccess(Facility $facility)
    {
        $user = Auth::user();
        // ...existing code...
        if (MemberPortalLayout::userIsSystemAdmin($user) || $user->hasRole('rdhr')) {
            return true;
        }
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor', 'don'])) {
            if (isset($user->facility_id) && $user->facility_id == $facility->id) {
                // ...existing code...
                return true;
            }
            if (method_exists($user, 'facilities')) {
                if ($user->facilities->contains('id', $facility->id)) {
                    // ...existing code...
                    return true;
                }
            }
        }
        Log::warning('AUTH DENIED: aborting 403', [
            'user_id' => $user->id,
            'user_roles' => $user->getRoleNames()->toArray(),
            'user_facility_id' => $user->facility_id ?? null,
            'route_facility_id' => $facility->id,
            'user_facilities' => method_exists($user, 'facilities') ? $user->facilities->pluck('id')->toArray() : null,
            'facility_model_class' => get_class($facility),
            'facility_exists' => $facility->exists,
            'facility_name' => $facility->name ?? null,
        ]);
        abort(403, 'Unauthorized facility access.');
    }

    protected function isDonUser(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole') && $user->hasRole('don'));
    }

    protected function donDepartmentId(): ?int
    {
        if (! $this->isDonUser()) {
            return null;
        }

        $user = Auth::user();
        $employee = $user?->resolvedBpEmployee(['currentAssignment']);

        return $employee?->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;
    }

    protected function donDepartmentName(): ?string
    {
        $departmentId = $this->donDepartmentId();

        if (! $departmentId) {
            return null;
        }

        return Department::query()->whereKey($departmentId)->value('name');
    }

    /**
     * @return array<int, string>
     */
    protected function donDepartmentPositionTitles(): array
    {
        $departmentId = $this->donDepartmentId();

        if (! $departmentId) {
            return [];
        }

        return Position::query()
            ->where('department_id', $departmentId)
            ->pluck('title')
            ->filter()
            ->values()
            ->all();
    }

    protected function applyDonJobApplicationScope($query): void
    {
        $departmentName = $this->donDepartmentName();
        $positionTitles = $this->donDepartmentPositionTitles();

        if (! $departmentName && empty($positionTitles)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $normalizedDepartmentName = $departmentName ? mb_strtolower(trim($departmentName)) : null;

        $query->whereHas('jobOpening', function ($jobOpeningQuery) use ($normalizedDepartmentName, $positionTitles) {
            $jobOpeningQuery->where(function ($scopedQuery) use ($normalizedDepartmentName, $positionTitles) {
                if ($normalizedDepartmentName) {
                    $scopedQuery->whereRaw("LOWER(TRIM(COALESCE(department, ''))) = ?", [$normalizedDepartmentName]);
                }

                if (! empty($positionTitles)) {
                    if ($normalizedDepartmentName) {
                        $scopedQuery->orWhereIn('title', $positionTitles);
                    } else {
                        $scopedQuery->whereIn('title', $positionTitles);
                    }
                }
            });
        });
    }

    protected function authorizeDonApplicationScope(Facility $facility, $application): void
    {
        if (! $this->isDonUser()) {
            return;
        }

        $departmentId = $this->donDepartmentId();
        $positionTitles = array_map('mb_strtolower', $this->donDepartmentPositionTitles());

        if (! $departmentId) {
            abort(403, 'Your account is missing a department assignment.');
        }

        if ($application instanceof JobApplication) {
            $jobOpening = $application->jobOpening;

            if (! $jobOpening || (int) $jobOpening->facility_id !== (int) $facility->id) {
                abort(403, 'You do not have access to this application.');
            }

            $departmentMatch = false;
            $departmentName = $this->donDepartmentName();

            if ($departmentName && filled($jobOpening->department)) {
                $departmentMatch = mb_strtolower(trim((string) $jobOpening->department))
                    === mb_strtolower(trim((string) $departmentName));
            }

            if (! $departmentMatch && ! empty($positionTitles)) {
                $departmentMatch = in_array(mb_strtolower((string) $jobOpening->title), $positionTitles, true);
            }

            if (! $departmentMatch) {
                abort(403, 'You do not have access to this department application.');
            }

            return;
        }

        if ($application instanceof PreEmploymentApplication) {
            if ($application->position?->department_id && (int) $application->position->department_id !== $departmentId) {
                abort(403, 'You do not have access to this department application.');
            }

            if (! $application->position_id && filled($application->position_applied_for) && ! empty($positionTitles)) {
                if (! in_array(mb_strtolower((string) $application->position_applied_for), $positionTitles, true)) {
                    abort(403, 'You do not have access to this department application.');
                }
            }

            $facilityApplicationQuery = JobApplication::query()
                ->where('user_id', $application->user_id)
                ->whereHas('jobOpening', function ($query) use ($facility) {
                    $query->where('facility_id', $facility->id);
                });

            $this->applyDonJobApplicationScope($facilityApplicationQuery);

            if (! $facilityApplicationQuery->exists()) {
                abort(403, 'You do not have access to this application.');
            }
        }
    }

    public function hiring(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);
        
        // Get all job openings for this facility
        $jobOpenings = $facility->jobOpenings()->get();

        // Get all job applications for this facility's job openings
        $applicationsQuery = JobApplication::query()
            ->whereIn('job_opening_id', $jobOpenings->pluck('id'))
            ->with(['jobOpening', 'user'])
            ->orderByDesc('created_at');

        if ($this->isDonUser()) {
            $this->applyDonJobApplicationScope($applicationsQuery);
        }

        $applications = $applicationsQuery->get();

        $facilityApplicantUserIds = $applications
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $preEmploymentApplicationsQuery = PreEmploymentApplication::query()
            ->with(['user', 'position'])
            ->orderByDesc('created_at');

        if ($facilityApplicantUserIds->isEmpty()) {
            $preEmploymentApplicationsQuery->whereRaw('1 = 0');
        } else {
            $preEmploymentApplicationsQuery->whereIn('user_id', $facilityApplicantUserIds->all());
        }

        if ($this->isDonUser()) {
            $donDepartmentId = $this->donDepartmentId();
            $positionTitles = $this->donDepartmentPositionTitles();

            if (! $donDepartmentId) {
                $preEmploymentApplicationsQuery->whereRaw('1 = 0');
            } else {
                $preEmploymentApplicationsQuery->where(function ($query) use ($donDepartmentId, $positionTitles) {
                    $query->whereHas('position', function ($positionQuery) use ($donDepartmentId) {
                        $positionQuery->where('department_id', $donDepartmentId);
                    });

                    if (! empty($positionTitles)) {
                        $query->orWhereIn('position_applied_for', $positionTitles);
                    }
                });
            }
        }

        $preEmploymentApplications = $preEmploymentApplicationsQuery->get();

        // Count statistics
        $stats = [
            'total_openings' => $jobOpenings->count(),
            'open_openings' => $jobOpenings->where('active', true)->count(),
            'total_applicants' => $applications->count(),
            'pending_applications' => $applications->where('status', 'pending')->count(),
            'submitted_preemployment' => $preEmploymentApplications->where('status', 'submitted')->count(),
            'completed_preemployment' => $preEmploymentApplications->where('status', 'completed')->count(),
        ];

        // Provide select list data for modal

        $positions = \App\Models\Position::with(['department', 'reportsToPosition'])
            ->orderBy('title')
            ->get();
        $positionDefaults = $positions->mapWithKeys(function ($position) {
            return [
                $position->title => [
                    'department' => $position->department?->name,
                    'reporting_to' => $position->reportsToPosition?->title,
                ],
            ];
        })->all();
        $departments = \App\Models\Department::orderBy('name')->pluck('name');
        $employmentTypes = ['Full Time', 'Part Time', 'Per Diem', 'Temporary', 'Contractor', 'Internship'];
        // For reportingTo, use only positions with supervisor_role = 1
        $reportingTo = \App\Models\Position::where('supervisor_role', 1)->orderBy('title')->pluck('title');

        return view('admin.facilities.hiring', compact('facility', 'jobOpenings', 'applications', 'preEmploymentApplications', 'stats', 'positions', 'positionDefaults', 'departments', 'employmentTypes', 'reportingTo'));
    }

    public function reviewPreEmployment(Facility $facility, $application)
    {
        // ...existing code...
        try {
            $this->authorizeFacilityAccess($facility);
            // ...existing code...
        } catch (\Exception $e) {
            Log::error('REVIEW AUTHORIZE FACILITY ERROR', ['error' => $e->getMessage(), 'facility_id' => $facility->id, 'user_id' => Auth::id()]);
            throw $e;
        }
        try {
            $preApp = PreEmploymentApplication::find($application);
            if ($preApp) {
                $application = $preApp;
            } else {
                // If not a pre-employment record, treat as JobApplication for viewing only
                $jobApp = \App\Models\JobApplication::find($application);
                if ($jobApp) {
                    $application = $jobApp;
                } else {
                    throw new \Exception('No query results for model [App\\Models\\PreEmploymentApplication] or [App\\Models\\JobApplication] ' . $application);
                }
            }
            // ...existing code...
        } catch (\Exception $e) {
            Log::error('REVIEW FINDORFAIL ERROR', ['error' => $e->getMessage(), 'application_param' => $application, 'user_id' => Auth::id()]);
            throw $e;
        }
        // ...existing code...
        try {
            if ($this->isDonUser()) {
                $this->authorizeDonApplicationScope($facility, $application);
            } else {
                \Illuminate\Support\Facades\Gate::authorize('view', $application);
            }
            // ...existing code...
        } catch (\Exception $e) {
            Log::error('REVIEW AUTHORIZE POLICY ERROR', ['error' => $e->getMessage(), 'application_id' => $application->id, 'user_id' => Auth::id()]);
            throw $e;
        }
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

        $application = PreEmploymentApplication::with('position')->findOrFail($application);
        $this->authorizeDonApplicationScope($facility, $application);
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
            ->where('item_key', 'application_form')
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

    public function createPreEmploymentPdf(Request $request, Facility $facility, $application)
    {
        $this->authorizeFacilityAccess($facility);

        $application = PreEmploymentApplication::with(['user', 'position'])->findOrFail($application);
        $this->authorizeDonApplicationScope($facility, $application);
        $applicantId = $application->user_id;

        $namePart = Str::slug(trim(($application->last_name ?? 'applicant') . '-' . ($application->first_name ?? '')));
        $defaultFileName = 'application_form_' . $application->id . '_' . $namePart . '.pdf';
        $fileName = $request->input('file_name', $defaultFileName);
        $directory = 'documents/facility_' . $facility->id . '/applicant_' . ($applicantId ?? 'unknown');
        $filePath = $directory . '/' . $fileName;

        $mode = $request->input('mode', 'view');

        // Check for duplicate file name if saving
        if ($mode === 'save') {
            $existing = \App\Models\EmployeeDocument::where('facility_id', $facility->id)
                ->where('file_name', $fileName)
                ->first();
            if ($existing) {
                return back()->withErrors(['file_name' => 'A document with this file name already exists. Please update the file name or save as another version.'])->withInput();
            }
        }

        $pdf = Pdf::loadView('admin.facilities.application-form-pdf', [
            'facility' => $facility,
            'application' => $application,
        ])->setPaper('letter');

        $content = $pdf->output();

        if ($mode === 'save') {
            Storage::disk('local')->put($filePath, $content);
            \App\Models\EmployeeDocument::create([
                'facility_id' => $facility->id,
                'user_id' => $applicantId,
                'pre_employment_application_id' => $application->id,
                'document_type' => 'application_form',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => 'application/pdf',
                'file_size' => strlen($content),
                'created_by' => Auth::id(),
            ]);
            try {
                $doc = \App\Models\Document::create([
                    'facility_id' => $facility->id,
                    'user_id' => $applicantId,
                    'document_type' => 'application_form',
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => 'application/pdf',
                    'file_size' => strlen($content),
                    'created_by' => Auth::id(),
                ]);
                \Log::info('Document created', ['doc_id' => $doc->id, 'file_name' => $fileName, 'file_path' => $filePath]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create Document', ['error' => $e->getMessage(), 'file_name' => $fileName, 'file_path' => $filePath]);
            }
        }

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    private function getFormLabel($formType): string
    {
        $labels = [
            'application_form' => 'Application Form',
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

    // public function attendance(Facility $facility)
    // {
    //     $this->authorizeFacilityAccess($facility);
    //     return view('admin.facilities.attendance', compact('facility'));
    // }

    public function documents(Facility $facility, \Illuminate\Http\Request $request)
    {
        $this->authorizeFacilityAccess($facility);
        // Get employees for this facility from bp_employees via assignments
        $employees = \App\Models\BPEmployee::whereHas('assignments', function($q) use ($facility) {
            $q->where('facility_id', $facility->id);
        })->orderedByName()->get();
        $uploadTypes = \App\Models\UploadType::query()->orderedForDisplay()->get();

        $query = \App\Models\Upload::with(['facility','user','uploadType']);
        if ($request->facility_id) $query->where('facility_id', $request->facility_id);
        if ($request->search) $query->where('original_filename', 'like', '%'.$request->search.'%');
        $uploads = $query->latest()->paginate(15);

        $editUpload = null;
        if ($request->has('edit')) {
            $editUpload = \App\Models\Upload::find($request->input('edit'));
        }

        return view('admin.facilities.documents', compact('facility', 'employees', 'uploadTypes', 'uploads', 'editUpload'));
    }

    public function reports(Facility $facility)
    {
        $this->authorizeFacilityAccess($facility);

        \App\Support\SelectedFacility::remember($facility);

        return redirect()->route('admin.reports.index', array_filter([
            'facility_id' => $facility->id,
            'department_id' => request('department_id') ?: null,
            'position_id' => request('position_id') ?: null,
            'reports_to' => request('reports_to') ?: null,
            'search' => request('search') ?: null,
            'category_id' => request('category_id') ?: null,
        ], fn ($value) => $value !== null && $value !== ''));
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