<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BPEmployee;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\Facility;
use App\Mail\FacilityUploadNotificationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmployeeHelper;
use App\Support\MemberPortalLayout;
use App\Support\UploadNotificationContext;


class UploadController extends Controller {

    protected function authorizeFacilityAccess(Facility $facility): void
    {
        $user = Auth::user();
        if (MemberPortalLayout::userIsSystemAdmin($user) || $user->hasRole('rdhr')) {
            return;
        }
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor'])) {
            if (isset($user->facility_id) && (int) $user->facility_id === (int) $facility->id) {
                return;
            }
            if (method_exists($user, 'facilities') && $user->facilities->contains('id', $facility->id)) {
                return;
            }
        }
        abort(403, 'Unauthorized facility access.');
    }

    /**
     * @return array{upload: Upload, facility: Facility, email: string, expiryTier: string}
     */
    protected function resolveUploadNotificationContext(Facility $facility, Upload $upload): array
    {
        $this->authorizeFacilityAccess($facility);

        return UploadNotificationContext::resolve($upload, $facility);
    }

    public function previewUploadNotification(Facility $facility, Upload $upload)
    {
        try {
            $context = $this->resolveUploadNotificationContext($facility, $upload);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json(
            FacilityUploadNotificationMail::previewPayload(
                $context['upload'],
                $facility,
                Auth::user(),
                $context['expiryTier'],
                $context['email'],
            )
        );
    }

    public function sendUploadNotification(Request $request, Facility $facility, Upload $upload)
    {
        try {
            $context = $this->resolveUploadNotificationContext($facility, $upload);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $upload = $context['upload'];
        $email = $context['email'];
        $expiryTier = $context['expiryTier'];

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:10000',
        ]);

        $customSubject = trim((string) ($validated['subject'] ?? ''));
        $customMessage = trim((string) ($validated['message'] ?? ''));

        try {
            Mail::to($email)->send(new FacilityUploadNotificationMail(
                $upload,
                $facility,
                Auth::user(),
                $expiryTier,
                $customSubject !== '' ? $customSubject : null,
                $customMessage !== '' ? $customMessage : null,
            ));

            return redirect()->back()->with(
                'success',
                'Notification sent to ' . $upload->employee->last_name . ', ' . $upload->employee->first_name . ' (' . $email . ').'
            );
        } catch (\Throwable $e) {
            Log::error('Facility upload notification failed', [
                'upload_id' => $upload->id,
                'facility_id' => $facility->id,
                'email' => $email,
                'exception' => $e,
            ]);

            $message = config('app.debug')
                ? 'Failed to send notification: ' . $e->getMessage()
                : 'Failed to send notification. Please try again.';

            return redirect()->back()->with('error', $message);
        }
    }

    public function update(Request $request, $facility, $upload)
    {
        $upload = Upload::findOrFail($upload);
        $uploadType = UploadType::find($request->upload_type_id);
        $rules = [
            'facility_id' => 'required|exists:facilities,id',
            'upload_type_id' => 'required|exists:upload_types,id',
        ];
        if ($uploadType && $uploadType->requires_expiry) {
            $rules['effective_start_date'] = 'required|date';
            $rules['expires_at'] = 'required|date|after_or_equal:effective_start_date';
        } else {
            $rules['effective_start_date'] = 'nullable';
            $rules['expires_at'] = 'nullable';
        }
        // Only require file if reupload is checked
        if ($request->has('reupload')) {
            $rules['file'] = 'required|file';
        }
        $validated = $request->validate($rules);

        $upload->facility_id = $request->facility_id;
        $upload->employee_num = $request->employee_num;
        $upload->upload_type_id = $request->upload_type_id;
        $upload->expires_at = $request->expires_at;
        $upload->effective_start_date = $request->effective_start_date;
        $upload->comments = $request->comments;

        if ($request->has('reupload') && $request->hasFile('file')) {
            // Delete old file from storage if it exists
            $upload->deleteStoredFile();
            $file = $request->file('file');
            $path = Upload::storeEmployeeFile($file, $request->employee_num);
            $upload->file_path = $path;
            $upload->original_filename = $file->getClientOriginalName();
            $upload->file_size = $file->getSize();
            $upload->user_id = Auth::id();
            $upload->uploaded_at = now();
        }

        $upload->save();
        return redirect()->route('admin.facility.documents', ['facility' => $facility])->with('success', 'Upload updated successfully.');
    }

    public function index(Request $request, $facility, $editUploadId = null)
    {
        $query = Upload::with(['facility', 'user', 'uploadType']);
        if ($request->facility_id) $query->where('facility_id', $request->facility_id);
        if ($request->search) $query->where('original_filename', 'like', '%'.$request->search.'%');
        $uploads = $query->latest()->paginate(15);
        $facilities = Facility::orderBy('name')->get();
        $uploadTypes = UploadType::orderBy('name')->get();
        $editUpload = null;
        if ($editUploadId) {
            $editUpload = Upload::find($editUploadId);
        }
        // Find the current facility
        if (is_numeric($facility)) {
            $facility = Facility::find($facility);
        }
        // Get employees for the current facility
        $employees = [];
        if ($facility && $facility->id) {
            $employees = EmployeeHelper::getAllEmployeesByFacility($facility->id);
        }
        return view('admin.facilities.uploads', compact('uploads', 'facilities', 'uploadTypes', 'editUpload', 'facility', 'employees'));
    }

    public function store(Request $request)
    {
        $uploadType = UploadType::find($request->upload_type_id);
            $rules = [
                'facility_id' => 'required|exists:facilities,id',
                'employee_num' => 'required|exists:bp_employees,employee_num',
                'upload_type_id' => 'required|exists:upload_types,id',
                'file' => 'required|file',
            ];
        if ($uploadType && $uploadType->requires_expiry) {
            $rules['effective_start_date'] = 'required|date';
            $rules['expires_at'] = 'required|date|after_or_equal:effective_start_date';
        } else {
            $rules['effective_start_date'] = 'nullable';
            $rules['expires_at'] = 'nullable';
        }
        $validated = $request->validate($rules);
        $file = $request->file('file');
        $path = Upload::storeEmployeeFile($file, $request->employee_num);
            $upload = Upload::create([
                'facility_id' => $request->facility_id,
                'employee_num' => $request->employee_num,
                'user_id' => Auth::id(),
                'upload_type_id' => $request->upload_type_id,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_at' => now(),
                'expires_at' => $request->expires_at,
                'effective_start_date' => $request->effective_start_date,
                'comments' => $request->comments,
            ]);
        return redirect()->back()->with('success', 'File uploaded successfully.');
    }

    public function download($facility, $upload)
    {
        $upload = Upload::findOrFail($upload);
        return Storage::disk('public')->download($upload->file_path, $upload->original_filename);
    }

    public function view($facility, $upload)
    {
        $upload = Upload::findOrFail($upload);
        $exists = Storage::disk('public')->exists($upload->file_path);
        $fullPath = Storage::disk('public')->path($upload->file_path);
        // Log::info('View method file check', [
        //     'upload_id' => $upload->id,
        //     'file_path' => $upload->file_path,
        //     'exists' => $exists,
        //     'full_path' => $fullPath
        // ]);
        if (!$exists) {
            abort(404, 'File not found on disk');
        }
        $mime = Storage::mimeType('public/'.$upload->file_path);
        $fullPath = Storage::disk('public')->path($upload->file_path);
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $upload->original_filename . '"'
        ]);
    }

    public function destroy($facility, $upload)
    {
        // Always resolve the model manually to avoid property access on string
        $upload = Upload::find($upload);
        if (!$upload) {
            return redirect()->back()->with('error', 'Upload not found.');
        }
        $upload->delete();
        return redirect()->back()->with('success', 'File deleted successfully.');
    }

    public function edit($facility, $upload)
    {
        // Redirect to index with editUploadId as a query param
        return redirect()->route('admin.facility.uploads.index', ['facility' => $facility, 'edit' => $upload]);
                $upload->facility_id = $request->facility_id;
                $upload->employee_num = $request->employee_num;
                $upload->upload_type_id = $request->upload_type_id;
                $upload->expires_at = $request->expires_at;
                $upload->effective_start_date = $request->effective_start_date;
                $upload->comments = $request->comments;
                
    }

}