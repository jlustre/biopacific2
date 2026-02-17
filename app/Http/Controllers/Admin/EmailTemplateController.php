<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailTemplate::query()->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $emailTemplates = $query->paginate(15)->withQueryString();

        return view('admin.email-templates.index', compact('emailTemplates'));
    }

    public function create()
    {
        return view('admin.email-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = auth()->id();

        EmailTemplate::create($validated);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template created successfully.');
    }

    public function show(EmailTemplate $emailTemplate, Request $request)
    {
        $filledSubject = $emailTemplate->subject;
        $filledBody = $emailTemplate->body;
        $jobApplication = null;
        
        // If in reply mode, fetch and use job application data to fill placeholders
        if ($request->filled('job_application_id')) {
            $jobApplication = JobApplication::find($request->input('job_application_id'));
            
            if ($jobApplication) {
                $firstName = $jobApplication->first_name ?? '';
                $lastName = $jobApplication->last_name ?? '';
                $facilityName = $jobApplication->jobOpening?->facility?->name ?? '';
                $jobTitle = $jobApplication->jobOpening?->title ?? '';
                $applicationId = $jobApplication->id ?? '';
                
                // Replace placeholders in subject
                $filledSubject = str_replace(
                    ['{first_name}', '{last_name}', '{facility_name}', '{job_title}', '{application_id}'],
                    [$firstName, $lastName, $facilityName, $jobTitle, $applicationId],
                    $filledSubject
                );
                
                // Replace placeholders in body
                $filledBody = str_replace(
                    ['{first_name}', '{last_name}', '{facility_name}', '{job_title}', '{application_id}'],
                    [$firstName, $lastName, $facilityName, $jobTitle, $applicationId],
                    $filledBody
                );
            }
        }
        
        return view('admin.email-templates.show', compact('emailTemplate', 'filledSubject', 'filledBody', 'jobApplication'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $emailTemplate->update($validated);

        return redirect()->route('admin.email-templates.show', $emailTemplate)
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('admin.email-templates.index')->with('success', 'Email template deleted successfully.');
    }
}
