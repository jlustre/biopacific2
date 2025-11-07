<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inquiry;
use App\Helpers\FacilityDataHelper;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactForm extends Component
{
    public $full_name;
    public $phone;
    public $email;
    public $message;
    public $consent;
    public $no_phi;
    public $website; // Honeypot field
    public $facility_id; // Facility ID
    public $recipient; // Recipient email
    public $allRecipients; // All recipients for email routing

    public $facility; // Facility data
    public $primary;
    public $accent;
    public $secondary;
    public $neutral_dark;
    public $neutral_light;
    
    // Success/error messages
    public $successMessage = '';
    public $errorMessage = '';
    public $isSubmitting = false;

    protected $rules = [
        'full_name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'message' => 'required|string',
        'consent' => 'accepted',
        'no_phi' => 'accepted',
        'website' => 'nullable|string', // Honeypot validation
        'facility_id' => 'required|integer',
    ];
    protected $messages = [
        'no_phi.accepted' => 'You must confirm that you will not include any Protected Health Information (PHI) in this form.',
        'consent.accepted' => 'You must consent to be contacted about your inquiry.',
        'full_name.required' => 'Full name is required.',
        'phone.required' => 'Phone number is required.',
        'email.required' => 'Email address is required.',
        'email.email' => 'Please enter a valid email address.',
        'message.required' => 'Message is required.',
    ];

    public function mount($facility, $primary = null, $secondary = null, $accent = null, $neutral_dark = null, $neutral_light = null)
    {
        $this->facility = $facility;
        
        // Use passed colors or get from facility data
        if ($primary || $secondary || $accent) {
            $this->primary = $primary ?? '#0EA5E9';
            $this->secondary = $secondary ?? '#1E293B';
            $this->accent = $accent ?? '#F59E0B';
            $this->neutral_dark = $neutral_dark ?? '#1e293b';
            $this->neutral_light = $neutral_light ?? '#F3F4F6';
        } else {
            $colors = FacilityDataHelper::getColors($facility);
            $this->primary = $colors['primary'];
            $this->accent = $colors['accent'];
            $this->secondary = $colors['secondary'];
            $this->neutral_dark = $colors['neutral_dark'];
            $this->neutral_light = $colors['neutral_light'];
        }
        
        $this->facility_id = $facility['id'];
        $this->recipient = FacilityDataHelper::getEmailRecipient($facility['id'], 'inquiry');
        
        // Get all recipients (public + employees) for email routing
        $this->allRecipients = FacilityDataHelper::getAllRecipientsForCategory($facility['id'], 'inquiry');
    }

    public function submit()
    {
        // Clear previous messages
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isSubmitting = true;

        try {
            // Debug: Log that submit was called
            Log::info('ContactForm submit called', [
                'full_name' => $this->full_name,
                'email' => $this->email,
                'facility_id' => $this->facility_id
            ]);
            
            // Manual validation with custom error messages
            $hasErrors = false;
            
            if (empty(trim($this->full_name))) {
                $this->addError('full_name', 'Please enter your full name.');
                $hasErrors = true;
            }
            if (empty(trim($this->email))) {
                $this->addError('email', 'Please enter your email address.');
                $hasErrors = true;
            } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->addError('email', 'Please enter a valid email address.');
                $hasErrors = true;
            }
            if (empty(trim($this->phone))) {
                $this->addError('phone', 'Please enter your phone number.');
                $hasErrors = true;
            }
            if (empty(trim($this->message))) {
                $this->addError('message', 'Please enter your message.');
                $hasErrors = true;
            }
            if (!$this->consent) {
                $this->addError('consent', 'You must consent to be contacted.');
                $hasErrors = true;
            }
            if (!$this->no_phi) {
                $this->addError('no_phi', 'You must confirm no PHI is included.');
                $hasErrors = true;
            }
            
            // If there are validation errors, show them and stop
            if ($hasErrors) {
                // Don't set errorMessage - let the individual field errors show
                Log::info('ContactForm validation failed', [
                    'errors' => $this->getErrorBag()->toArray()
                ]);
                $this->dispatch('scroll-to-form');
                $this->isSubmitting = false;
                return;
            }
            
            // Now validate with Livewire rules for additional checks
            $validatedData = $this->validate();

            // Check honeypot field
            if (!empty($validatedData['website'])) {
                $this->errorMessage = 'Form submission failed.';
                $this->dispatch('scroll-to-form');
                $this->isSubmitting = false;
                return;
            }

            // Save inquiry to the database
            $inquiry = Inquiry::create([
                'facility_id' => $this->facility_id,
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'email' => $this->email,
                'message' => $this->message,
                'consent' => true,
                'no_phi' => true,
                'recipient' => 'inquiry'
            ]);

            // Get facility and send email using the existing system
            if ($this->facility) {
                // Use FacilityDataHelper to get the correct recipients
                $recipientData = FacilityDataHelper::getAllRecipientsForCategory($this->facility_id, 'inquiry');
                
                // Extract ONLY employee email addresses (not public-facing emails)
                $emailAddresses = $recipientData['employee_emails'] ?? [];
                
                if (!empty($emailAddresses)) {
                    // Prepare data for the existing ContactMail format
                    $emailData = [
                        'full_name' => $this->full_name,
                        'email' => $this->email,
                        'phone' => $this->phone ?? '',
                        'message' => $this->message,
                        'facility' => [
                            'name' => $this->facility['name'],
                            'slug' => $this->facility['slug']
                        ]
                    ];
                    
                    // Send email to each recipient
                    foreach ($emailAddresses as $emailAddress) {
                        Mail::to($emailAddress)->send(new ContactMail($emailData));
                    }
                    
                    Log::info('Contact form inquiry sent via Livewire', [
                        'inquiry_id' => $inquiry->id,
                        'facility' => $this->facility['name'],
                        'email_addresses' => $emailAddresses,
                        'warnings' => $recipientData['warnings'] ?? []
                    ]);
                } else {
                    Log::warning('No recipients found for inquiry via Livewire', [
                        'facility_id' => $this->facility['id'],
                        'facility_name' => $this->facility['name'],
                        'recipient_data' => $recipientData
                    ]);
                }
            }

            $this->successMessage = 'Thank you for your message! We\'ll get back to you promptly.';
            
            // Scroll to top to show success message
            $this->dispatch('scroll-to-form');
            
            // Reset form fields
            $this->reset(['full_name', 'phone', 'email', 'message', 'consent', 'no_phi', 'website']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Don't override our manual validation errors
            if (!$this->errorMessage) {
                $this->errorMessage = 'Please fix the validation errors below.';
            }
            foreach ($e->errors() as $field => $messages) {
                if (!$this->getErrorBag()->has($field)) {
                    $this->addError($field, $messages[0]);
                }
            }
            // Scroll to top to show validation errors
            $this->dispatch('scroll-to-form');
        } catch (\Exception $e) {
            Log::error('Contact form submission failed via Livewire', [
                'error' => $e->getMessage(),
                'facility_id' => $this->facility_id
            ]);
            
            $this->errorMessage = 'There was an issue submitting your message. Please try again or contact us directly.';
            // Scroll to top to show error message
            $this->dispatch('scroll-to-form');
        }
        
        $this->isSubmitting = false;
    }

    public function updated($propertyName)
    {
        // Clear error message when user starts typing
        if ($this->errorMessage) {
            $this->errorMessage = '';
        }
        
        $this->resetErrorBag($propertyName);
        
        // Real-time validation for critical fields
        if ($propertyName === 'no_phi') {
            $this->validateOnly('no_phi');
        }
        
        if ($propertyName === 'consent') {
            $this->validateOnly('consent');
        }
        
        if ($propertyName === 'email') {
            $this->validateOnly('email');
        }
        
        if ($propertyName === 'phone') {
            $this->validateOnly('phone');
        }
    }

    public function render()
    {
        return view('livewire.contact-form', [
            'facility' => $this->facility,
            'primary' => $this->primary,
            'secondary' => $this->secondary,
            'accent' => $this->accent,
            'neutral_dark' => $this->neutral_dark,
            'neutral_light' => $this->neutral_light,
        ]);
    }
}
