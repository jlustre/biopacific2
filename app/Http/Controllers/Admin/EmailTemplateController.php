<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\JobApplication;
use App\Support\EmailTemplatePlaceholderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

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
        $jobApplication = $request->filled('job_application_id')
            ? JobApplication::with(['jobOpening.facility', 'activeRegistrationCode'])->find($request->input('job_application_id'))
            : null;

        [$filledSubject, $filledBody] = $this->fillTemplate($emailTemplate, $jobApplication);

        return view('admin.email-templates.show', compact('emailTemplate', 'filledSubject', 'filledBody', 'jobApplication'));
    }

    public function sendReply(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'reply_to' => ['required', 'email'],
            'job_application_id' => ['nullable', 'integer', 'exists:job_applications,id'],
        ]);

        $jobApplication = !empty($validated['job_application_id'])
            ? JobApplication::with(['jobOpening.facility', 'activeRegistrationCode'])->find($validated['job_application_id'])
            : null;

        [$filledSubject, $filledBody] = $this->fillTemplate($emailTemplate, $jobApplication);

        Mail::html($filledBody, function ($message) use ($validated, $filledSubject) {
            $message->to($validated['reply_to'])
                ->subject($filledSubject);
        });

        return redirect()
            ->route('admin.email-templates.show', [
                'email_template' => $emailTemplate,
                'reply_to' => $validated['reply_to'],
                'job_application_id' => $validated['job_application_id'] ?? null,
                'applicant_name' => $request->input('applicant_name'),
            ])
            ->with('success', 'Email sent successfully.');
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

    private function fillTemplate(EmailTemplate $emailTemplate, ?JobApplication $jobApplication): array
    {
        if (!$jobApplication) {
            return [$emailTemplate->subject, $emailTemplate->body];
        }

        return app(EmailTemplatePlaceholderService::class)->fillForJobApplication($emailTemplate, $jobApplication);
    }
}
