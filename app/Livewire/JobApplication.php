<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\JobApplication as JobApplicationModel;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Mail\SecureJobApplicationMail;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class JobApplication extends Component
{
    public $hipaa_consent;
    use WithFileUploads;

    public $jobOpening;
    public $facility;
    public $facility_id;
    
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
    // General application field
    public $desired_position;
    public $department;
    public $employment_type;
    
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
        'hipaa_consent' => 'accepted',
    ];
    protected $generalRules = [
        'desired_position' => 'required|string|max:100',
        'department' => 'required|string|max:50',
        'employment_type' => 'required|string|max:20',
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:30',
        'cover_letter' => 'nullable|string|max:2000',
        'resume' => 'required|file|mimes:pdf,doc,docx|max:10240',
        'consent' => 'accepted',
        'hipaa_consent' => 'accepted',
    ];
    /**
     * Handle general job application submission (no job selected)
     */
    public function submitGeneral()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isSubmitting = true;

    Log::info('General JobApplication submitGeneral called', [
            'desired_position' => $this->desired_position,
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
            Log::info('DEBUG: Validating generalRules', ['data' => [
                'desired_position' => $this->desired_position,
                'department' => $this->department,
                'employment_type' => $this->employment_type,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'cover_letter' => $this->cover_letter,
                'resume' => $this->resume,
                'consent' => $this->consent,
                'hipaa_consent' => $this->hipaa_consent
            ]]);
            $this->validate($this->generalRules);

            Log::info('DEBUG: Validation passed');
            // Handle resume file upload
            $resumePath = null;
            if ($this->resume) {
                try {
                    $resumePath = $this->resume->store('resumes', 'public');
                    Log::info('DEBUG: Resume uploaded', ['resumePath' => $resumePath]);
                } catch (\Exception $e) {
                    Log::error('DEBUG: Resume upload failed', ['error' => $e->getMessage()]);
                    $this->addError('resume', 'Failed to upload resume file. Please try again.');
                    $this->isSubmitting = false;
                    $this->errorMessage = 'Resume upload failed: ' . $e->getMessage();
                    $this->dispatch('scrollToTop');
                    return;
                }
            }

            // Create general job application (no job_opening_id)
            try {
                $application = JobApplicationModel::create([
                    'job_opening_id' => null,
                    'desired_position' => $this->desired_position,
                    'department' => $this->department,
                    'employment_type' => $this->employment_type,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'cover_letter' => $this->cover_letter,
                    'resume_path' => $resumePath,
                    'status' => 'pending',
                ]);
                Log::info('DEBUG: JobApplicationModel created', ['id' => $application->id]);
            } catch (\Exception $e) {
                Log::error('DEBUG: JobApplicationModel create failed', ['error' => $e->getMessage()]);
                $this->errorMessage = 'Database save failed: ' . $e->getMessage();
                $this->dispatch('scrollToTop');
                $this->isSubmitting = false;
                return;
            }

            // Generate secure access token if needed
            if (method_exists($application, 'generateSecureAccessToken')) {
                $application->generateSecureAccessToken();
            }

            // Get all recipients for hiring category
            $facilityId = $this->facility ? $this->facility->id : null;
            $allRecipients = $facilityId ? FacilityDataHelper::getAllRecipientsForCategory($facilityId, 'hiring') : [];

            // Send secure notification emails
            try {
                $emailsSent = 0;
                $employeeEmails = $allRecipients['employee_emails'] ?? [];

                if (!empty($employeeEmails)) {
                    foreach ($employeeEmails as $employeeEmail) {
                        Mail::to($employeeEmail)->send(new SecureJobApplicationMail($application, $this->facility));
                        $emailsSent++;
                    }
                } else {
                    // Fallback to public emails if no employee emails configured
                    $publicEmails = $allRecipients['public_emails'] ?? [];
                    if (!empty($publicEmails)) {
                        Mail::to($publicEmails[0])->send(new SecureJobApplicationMail($application, $this->facility));
                        $emailsSent++;
                    }
                }

                Log::info('General job application submitted via Livewire', [
                    'application_id' => $application->id,
                    'desired_position' => $this->desired_position,
                    'facility' => $this->facility ? $this->facility->name : null,
                    'emails_sent' => $emailsSent,
                    'recipients' => $employeeEmails,
                    'secure_token_generated' => method_exists($application, 'generateSecureAccessToken'),
                    'expires_at' => $application->expires_at ?? null
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send general job application email', [
                    'error' => $e->getMessage(),
                    'application_id' => $application->id
                ]);
                // Don't fail the application if email fails
            }

            $this->successMessage = 'Your application has been submitted successfully! We\'ll review your application and get back to you soon.';

            Log::info('General application success message set', ['message' => $this->successMessage]);

            // Clear the form
            $this->clearForm();

            // Scroll to top of form to show success message
            $this->dispatch('scrollToTop');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('DEBUG: Validation failed', ['errors' => $e->errors()]);
            $this->errorMessage = 'Please fix the validation errors below.';
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            // Scroll to top to show validation errors
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            Log::error('General job application submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->errorMessage = 'There was an error submitting your application. Please try again. Error: ' . $e->getMessage();
            // Scroll to top to show error message
            $this->dispatch('scrollToTop');
        } finally {
            $this->isSubmitting = false;
        }
    }
    
    protected $messages = [
        'resume.required' => 'Please upload your resume.',
        'resume.mimes' => 'Resume must be a PDF, DOC, or DOCX file.',
        'resume.max' => 'Resume file size cannot exceed 10MB.',
        'consent.accepted' => 'You must consent to the application terms.',
        'hipaa_consent.accepted' => 'You must acknowledge and consent to HIPAA compliance.',
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
        } elseif ($this->facility_id) {
            $this->facility = Facility::find($this->facility_id);
        } else {
            // Try to get facility from request or context if available
            if (request()->has('facility_id')) {
                $facilityId = request()->input('facility_id');
                $this->facility = Facility::find($facilityId);
            } elseif (property_exists($this, 'facility') && $this->facility) {
                // Already set
            } else {
                // Optionally, set a default or leave null
                $this->facility = null;
            }
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

            // Generate secure access token
            $application->generateSecureAccessToken();
            
            // Get all recipients for hiring category
            $allRecipients = FacilityDataHelper::getAllRecipientsForCategory($this->facility->id, 'hiring');

            // Send secure notification emails
            try {
                $emailsSent = 0;
                $employeeEmails = $allRecipients['employee_emails'] ?? [];
                
                if (!empty($employeeEmails)) {
                    foreach ($employeeEmails as $employeeEmail) {
                        Mail::to($employeeEmail)->send(new SecureJobApplicationMail($application, $this->facility));
                        $emailsSent++;
                    }
                } else {
                    // Fallback to public emails if no employee emails configured
                    $publicEmails = $allRecipients['public_emails'] ?? [];
                    if (!empty($publicEmails)) {
                        Mail::to($publicEmails[0])->send(new SecureJobApplicationMail($application, $this->facility));
                        $emailsSent++;
                    }
                }
                
                Log::info('Secure job application submitted via Livewire', [
                    'application_id' => $application->id,
                    'job_opening' => $this->jobOpening->title,
                    'facility' => $this->facility->name,
                    'emails_sent' => $emailsSent,
                    'recipients' => $employeeEmails,
                    'secure_token_generated' => true,
                    'expires_at' => $application->expires_at
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to send secure job application email', [
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
            'consent',
            'desired_position',
            'department',
            'hipaa_consent'
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
