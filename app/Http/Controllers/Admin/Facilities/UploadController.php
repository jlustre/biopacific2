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

class UploadController extends Controller
{
    public function index(Request $request)
    {
        $query = Upload::with(['facility', 'user', 'uploadType']);
        if ($request->facility_id) $query->where('facility_id', $request->facility_id);
        if ($request->search) $query->where('original_filename', 'like', '%'.$request->search.'%');
        $uploads = $query->latest()->paginate(15);
        $facilities = Facility::orderBy('name')->get();
        $uploadTypes = UploadType::orderBy('name')->get();
        return view('admin.facilities.uploads', compact('uploads', 'facilities', 'uploadTypes'));
    }

    public function store(Request $request)
    {
        $uploadType = UploadType::find($request->upload_type_id);
        $rules = [
            'facility_id' => 'required|exists:facilities,id',
            'employee_id' => 'required|exists:bp_employees,emp_id',
            'upload_type_id' => 'required|exists:upload_types,id',
            'file' => 'required|file',
        ];
        if ($uploadType && $uploadType->requires_expiry) {
            $rules['effective_start_date'] = 'required|date';
            $rules['effective_end_date'] = 'nullable|date|after_or_equal:effective_start_date';
            $rules['expires_at'] = 'nullable|date|after_or_equal:effective_start_date';
        } else {
            $rules['effective_start_date'] = 'nullable';
            $rules['effective_end_date'] = 'nullable';
            $rules['expires_at'] = 'nullable';
        }
        $validated = $request->validate($rules);
        $file = $request->file('file');
        $path = $file->store('uploads');
        $upload = Upload::create([
            'facility_id' => $request->facility_id,
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

    public function download(Upload $upload)
    {
        return Storage::download($upload->file_path, $upload->original_filename);
    }

    public function destroy($upload)
    {
        // Always resolve the model manually to avoid property access on string
        if (!$upload instanceof Upload) {
            $upload = Upload::find($upload);
        }
        if (!$upload) {
            return redirect()->back()->with('error', 'Upload not found.');
        }
        Storage::delete($upload->file_path);
        $upload->delete();
        return redirect()->back()->with('success', 'File deleted successfully.');
    }
}
