<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Support\Facades\Storage;

class CareersPublicController extends Controller
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'job_opening_id' => 'required|exists:job_openings,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'cover_letter' => 'nullable|string|max:2000',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'consent' => 'accepted',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        $application = JobApplication::create([
            'job_opening_id' => $validated['job_opening_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'resume_path' => $resumePath,
            'consent' => true,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your application has been submitted successfully!');
    }
}
