<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Mail\JobApplicationMail;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CareersPublicController extends Controller
{
    public function apply(Request $request)
    {
        try {
            $validated = $request->validate([
                'job_opening_id' => 'required|exists:job_openings,id',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:30',
                'cover_letter' => 'nullable|string|max:2000',
                'resume' => 'required|file|mimes:pdf,doc,docx|max:10240',
                'consent' => 'accepted',
            ]);

            // Check if job opening is still active
            $jobOpening = JobOpening::find($validated['job_opening_id']);
            if (!$jobOpening || !$jobOpening->active) {
                return back()->withErrors(['job_opening_id' => 'This job opening is no longer available.'])->withInput();
            }

            // Handle resume file upload
            $resumePath = null;
            if ($request->hasFile('resume')) {
                try {
                    $resumePath = $request->file('resume')->store('resumes', 'public');
                } catch (\Exception $e) {
                    return back()->withErrors(['resume' => 'Failed to upload resume file. Please try again.'])->withInput();
                }
            }

            // Create job application
            $application = JobApplication::create([
                'job_opening_id' => $validated['job_opening_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'cover_letter' => $validated['cover_letter'] ?? null,
                'resume_path' => $resumePath,
                'consent' => true,
                'status' => 'pending',
            ]);

            // Get facility details for email
            $facility = Facility::find($jobOpening->facility_id);
            if (!$facility) {
                return back()->withErrors(['general' => 'Unable to process application. Please contact support.'])->withInput();
            }

            // Get all recipients for hiring category
            $allRecipients = FacilityDataHelper::getAllRecipientsForCategory($facility->id, 'hiring');

            // Prepare email data
            $emailData = [
                'facility' => $facility->toArray(),
                'job_opening_id' => $jobOpening->id,
                'job_title' => $jobOpening->title,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'cover_letter' => $validated['cover_letter'] ?? null,
                'resume_path' => $resumePath,
            ];

            // Send notification emails
            try {
                $emailsSent = 0;
                $employeeEmails = $allRecipients['employee_emails'] ?? [];
                
                if (!empty($employeeEmails)) {
                    foreach ($employeeEmails as $employeeEmail) {
                        Mail::to($employeeEmail)->send(new JobApplicationMail($emailData));
                        $emailsSent++;
                    }
                } else {
                    // Fallback to public emails if no employee emails are configured
                    $publicEmails = $allRecipients['public_emails'] ?? [];
                    if (!empty($publicEmails)) {
                        Mail::to($publicEmails[0])->send(new JobApplicationMail($emailData));
                        $emailsSent++;
                    }
                }

                if ($emailsSent === 0) {
                    // Application saved but no emails sent - still success but with warning
                    return back()->with('warning', 'Your application has been submitted successfully, but we were unable to send email notifications. Our team will still review your application.');
                }

            } catch (\Exception $e) {
                // Application saved but email failed - still success but with warning
                return back()->with('warning', 'Your application has been submitted successfully, but there was an issue sending email notifications. Our team will still review your application.');
            }

            return back()->with('success', 'Your application has been submitted successfully! Our hiring team will review it and contact you soon.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laravel will automatically handle validation errors and redirect back with errors
            throw $e;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Job application submission failed: ' . $e->getMessage(), [
                'request_data' => $request->except(['resume', '_token']),
                'exception' => $e
            ]);

            return back()->withErrors(['general' => 'There was an error submitting your application. Please try again or contact us directly.'])->withInput();
        }
    }
}
