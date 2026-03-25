<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\FacilityArbitrationDocument;
use Illuminate\Support\Facades\Storage;

class ArbitrationTemplateController extends Controller
{
    public function edit($id)
    {
        $template = FacilityArbitrationDocument::findOrFail($id);
        $facilities = Facility::orderBy('name')->get();
        return view('admin.arbitration_templates.edit', compact('template', 'facilities'));
    }

    public function update(Request $request, $id)
    {
        $template = FacilityArbitrationDocument::findOrFail($id);
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'template_type' => 'required|in:docx,pdf',
            'template_file' => 'nullable|file|mimes:pdf,docx',
        ]);
        $template->facility_id = $request->facility_id;
        $template->template_type = $request->template_type;
        if ($request->hasFile('template_file')) {
            \Storage::disk('public')->delete($template->template_path);
            $file = $request->file('template_file');
            $template->original_name = $file->getClientOriginalName();
            $template->template_path = $file->store('arbitration_templates', 'public');
        }
        $template->save();
        return redirect()->route('admin.arbitration-templates.index')->with('success', 'Template updated successfully.');
    }
    public function index()
    {
        $templates = FacilityArbitrationDocument::with('facility')->get();
        return view('admin.arbitration_templates.index', compact('templates'));
    }

    public function create()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('admin.arbitration_templates.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'template_type' => 'required|in:docx,pdf',
            'template_file' => 'required|file|mimes:pdf,docx',
        ]);
        $file = $request->file('template_file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('arbitration_templates', 'public');
        FacilityArbitrationDocument::create([
            'facility_id' => $request->facility_id,
            'template_path' => $path,
            'template_type' => $request->template_type,
            'original_name' => $originalName,
        ]);
        return redirect()->route('admin.arbitration-templates.index')->with('success', 'Template uploaded successfully.');
    }

    public function destroy($id)
    {
        $doc = FacilityArbitrationDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->template_path);
        $doc->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function download($id)
    {
        $template = FacilityArbitrationDocument::findOrFail($id);
        $filename = $template->original_name ?? basename($template->template_path);
        return Storage::disk('public')->download($template->template_path, $filename);
    }

     public function view($id)
    {
        $template = FacilityArbitrationDocument::findOrFail($id);
        $file = Storage::disk('public')->get($template->template_path);
        $mime = Storage::disk('public')->mimeType($template->template_path);
        $filename = $template->original_name ?? basename($template->template_path);
        return response($file, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
