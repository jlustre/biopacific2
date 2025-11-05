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

    public function mount($facility, $primary = null, $secondary = null, $accent = null, $neutral_dark = null)
    {
        $this->facility = $facility;
        
        // Use passed colors or get from facility data
        if ($primary || $secondary || $accent) {
            $this->primary = $primary ?? '#0EA5E9';
            $this->secondary = $secondary ?? '#1E293B';
            $this->accent = $accent ?? '#F59E0B';
            $this->neutral_dark = $neutral_dark ?? '#1e293b';
        } else {
            $colors = FacilityDataHelper::getColors($facility);
            $this->primary = $colors['primary'];
            $this->accent = $colors['accent'];
            $this->secondary = $colors['secondary'];
            $this->neutral_dark = '#1e293b';
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
            $validatedData = $this->validate();

            // Check honeypot field
            if (!empty($validatedData['website'])) {
                $this->errorMessage = 'Form submission failed.';
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
            
            // Reset form fields
            $this->reset(['full_name', 'phone', 'email', 'message', 'consent', 'no_phi', 'website']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        } catch (\Exception $e) {
            Log::error('Contact form submission failed via Livewire', [
                'error' => $e->getMessage(),
                'facility_id' => $this->facility_id
            ]);
            
            $this->errorMessage = 'There was an issue submitting your message. Please try again or contact us directly.';
        }
        
        $this->isSubmitting = false;
    }

    public function updated($propertyName)
    {
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
        ]);
    }
}
