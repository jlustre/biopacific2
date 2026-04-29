<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\Facility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmployeeHelper;


class UploadController extends Controller {

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
            if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $upload->file_path = $path;
            $upload->original_filename = $file->getClientOriginalName();
            $upload->file_size = $file->getSize();
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
            $rules['effective_end_date'] = 'nullable|date|after_or_equal:effective_start_date';
            $rules['expires_at'] = 'required|date|after_or_equal:effective_start_date';
        } else {
            $rules['effective_start_date'] = 'nullable';
            $rules['effective_end_date'] = 'nullable';
            $rules['expires_at'] = 'nullable';
        }
        $validated = $request->validate($rules);
        $file = $request->file('file');
        $path = $file->store('uploads', 'public');
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
                'effective_end_date' => $request->effective_end_date,
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
        // Delete the actual file from storage if it exists
        if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
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