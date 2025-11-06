<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\JobApplication as JobApplicationModel;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Mail\JobApplicationMail;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class JobApplication extends Component
{
    use WithFileUploads;
    
    public $jobOpening;
    public $facility;
    
    // Color variables
    public $primary;
    public $secondary;
    public $accent;
    public $neutral_dark;
    public $neutral_light;
    
    // Form fields
    public $job_opening_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $cover_letter;
    public $resume;
    public $consent;
    
    // UI state
    public $successMessage = '';
    public $errorMessage = '';
    public $isSubmitting = false;
    
    protected $rules = [
        'job_opening_id' => 'required|exists:job_openings,id',
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:30',
        'cover_letter' => 'nullable|string|max:2000',
        'resume' => 'required|file|mimes:pdf,doc,docx|max:10240',
        'consent' => 'accepted',
    ];
    
    protected $messages = [
        'resume.required' => 'Please upload your resume.',
        'resume.mimes' => 'Resume must be a PDF, DOC, or DOCX file.',
        'resume.max' => 'Resume file size cannot exceed 10MB.',
        'consent.accepted' => 'You must consent to the application terms.',
    ];
    
    public function mount($jobOpeningId = null, $primary = null, $secondary = null, $accent = null, $neutral_dark = null, $neutral_light = null)
    {
        // Set color variables
        $this->primary = $primary ?? '#3B82F6'; // Default blue if not provided
        $this->secondary = $secondary ?? '#1E40AF'; // Default darker blue if not provided
        $this->accent = $accent ?? '#6366F1'; // Default indigo if not provided
        $this->neutral_dark = $neutral_dark ?? '#374151'; // Default gray-700 if not provided
        $this->neutral_light = $neutral_light ?? '#F3F4F6'; // Default gray-100 if not provided
        
        if ($jobOpeningId) {
            $this->job_opening_id = $jobOpeningId;
            $this->jobOpening = JobOpening::findOrFail($jobOpeningId);
            $this->facility = Facility::findOrFail($this->jobOpening->facility_id);
        }
    }
    
    public function setJobOpening($jobOpeningId)
    {
        if (!$jobOpeningId) {
            $this->jobOpening = null;
            $this->facility = null;
            $this->job_opening_id = null;
            return;
        }
        
        try {
            $this->jobOpening = JobOpening::findOrFail($jobOpeningId);
            $this->facility = Facility::findOrFail($this->jobOpening->facility_id);
            $this->job_opening_id = $jobOpeningId;
            
            // Clear any previous messages when switching jobs
            $this->successMessage = '';
            $this->errorMessage = '';
            
            Log::info('Job opening set successfully', [
                'job_id' => $jobOpeningId,
                'job_title' => $this->jobOpening->title,
                'facility_name' => $this->facility->name
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set job opening', [
                'job_id' => $jobOpeningId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function submit()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isSubmitting = true;
        
        Log::info('JobApplication submit called', [
            'job_opening_id' => $this->job_opening_id,
            'form_data' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'resume' => $this->resume ? 'uploaded' : 'not uploaded',
                'consent' => $this->consent
            ]
        ]);
        
        try {
            $this->validate();
            
            // Check if job opening is still active
            if (!$this->jobOpening->active) {
                $this->errorMessage = 'This job opening is no longer available.';
                $this->dispatch('scrollToTop');
                $this->isSubmitting = false;
                return;
            }
            
            // Handle resume file upload
            $resumePath = null;
            if ($this->resume) {
                try {
                    $resumePath = $this->resume->store('resumes', 'public');
                } catch (\Exception $e) {
                    $this->addError('resume', 'Failed to upload resume file. Please try again.');
                    $this->isSubmitting = false;
                    return;
                }
            }
            
            // Create job application
            $application = JobApplicationModel::create([
                'job_opening_id' => $this->job_opening_id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'cover_letter' => $this->cover_letter,
                'resume_path' => $resumePath,
                'status' => 'pending',
            ]);
            
            // Get all recipients for hiring category
            $allRecipients = FacilityDataHelper::getAllRecipientsForCategory($this->facility->id, 'hiring');
            
            // Prepare email data
            $emailData = [
                'facility' => $this->facility->toArray(),
                'job_opening_id' => $this->jobOpening->id,
                'job_title' => $this->jobOpening->title,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'cover_letter' => $this->cover_letter ?? null,
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
                    // Fallback to public emails if no employee emails configured
                    $publicEmails = $allRecipients['public_emails'] ?? [];
                    if (!empty($publicEmails)) {
                        Mail::to($publicEmails[0])->send(new JobApplicationMail($emailData));
                        $emailsSent++;
                    }
                }
                
                Log::info('Job application submitted via Livewire', [
                    'application_id' => $application->id,
                    'job_opening' => $this->jobOpening->title,
                    'facility' => $this->facility->name,
                    'emails_sent' => $emailsSent,
                    'recipients' => $employeeEmails
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to send job application email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
                // Don't fail the application if email fails
            }
            
            $this->successMessage = 'Your application has been submitted successfully! We\'ll review your application and get back to you soon.';
            
            Log::info('Success message set', ['message' => $this->successMessage]);
            
            // Clear the form
            $this->clearForm();
            
            // Scroll to top of form to show success message
            $this->dispatch('scrollToTop');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = 'Please fix the validation errors below.';
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            // Scroll to top to show validation errors
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            Log::error('Job application submission failed', [
                'error' => $e->getMessage(),
                'job_opening_id' => $this->job_opening_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorMessage = 'There was an error submitting your application. Please try again. Error: ' . $e->getMessage();
            // Scroll to top to show error message
            $this->dispatch('scrollToTop');
        } finally {
            $this->isSubmitting = false;
        }
    }
    
    public function clearForm()
    {
        $this->reset([
            'first_name',
            'last_name',
            'email',
            'phone',
            'cover_letter',
            'resume',
            'consent'
        ]);
        
        $this->resetErrorBag();
    }
    
    public function updated($propertyName)
    {
        $this->resetErrorBag($propertyName);
    }
    
    public function render()
    {
        return view('livewire.job-application', [
            'primary' => $this->primary,
            'secondary' => $this->secondary,
            'accent' => $this->accent,
            'neutral_dark' => $this->neutral_dark,
            'neutral_light' => $this->neutral_light,
        ]);
    }
}
