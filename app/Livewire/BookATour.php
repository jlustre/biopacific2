<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SecureBookATourMail;
use App\Models\TourRequest;

class BookATour extends Component
{
    public $facility;
    public $primary;
    public $accent;
    public $secondary;
    public $neutral_dark;
    public $neutral_light;
    public $services = [];
    public $isSubmitting = false;

    public $full_name;
    public $relationship;
    public $phone;
    public $email;
    public $preferred_date;
    public $preferred_time;
    public $specific_time;
    public $interests = [];
    public $message;
    public $consent;
    public $recipient;
    public $recipients = [];
    public $allRecipients = [];

    protected $rules = [
        'full_name' => 'required|max:255',
        'phone' => 'required|max:20',
        'email' => 'required|email|max:255',
        'preferred_date' => 'required|date|after:today',
        'preferred_time' => 'required|max:255',
        'specific_time' => 'nullable|max:255', // Added validation for specific_time
        'consent' => 'accepted',
    ];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($facility)
    {
        $this->facility = $facility;
        $colors = FacilityDataHelper::getColors($facility);
        $this->primary = $colors['primary'];
        $this->accent = $colors['accent'];
        $this->secondary = $colors['secondary'];
        $this->neutral_dark = $colors['neutral_dark'];
        $this->neutral_light = $colors['neutral_light'];
        $this->services = FacilityDataHelper::getServices($facility);
        $this->recipient = FacilityDataHelper::getEmailRecipient($facility->id, 'book-a-tour');
        $this->recipients = FacilityDataHelper::getEmailRecipients($facility->id, 'book-a-tour');
        
        // Get all recipients (public + employees)
        $this->allRecipients = FacilityDataHelper::getAllRecipientsForCategory($facility->id, 'book-a-tour');

        // Initialize preferred_time to avoid null issues
        $this->preferred_time = '';

    }

    public function submit()
    {
        // Clear previous flash messages
        session()->forget(['success', 'error']);
        $this->isSubmitting = true;
        
        try {
            // Debug: Log that submit was called
            Log::info('BookATour submit called', [
                'full_name' => $this->full_name,
                'email' => $this->email,
            ]);
            
            $this->validate();

            if ($this->preferred_time === 'Other' && $this->specific_time) {
                $this->preferred_time = $this->specific_time;
            }

            $scheduledDateTime = new \DateTime($this->preferred_date . ' ' . $this->preferred_time);
            $currentDateTime = new \DateTime();
            $interval = $currentDateTime->diff($scheduledDateTime);

            if ($interval->days == 0 && $interval->h < 24) {
                session()->flash('warning', 'The scheduled time you selected is less than 24 hours from now. Please call the facility directly to confirm availability.');
                return;
            }

            // 🔒 SECURE ePHI HANDLING: Create TourRequest with encrypted data
            $tourRequest = TourRequest::create([
                'facility_id' => $this->facility['id'],
                'recipient' => $this->recipient,
                'full_name' => $this->full_name,        // Will be encrypted by EncryptsEphi trait
                'relationship' => $this->relationship,
                'phone' => $this->phone,                // Will be encrypted by EncryptsEphi trait
                'email' => $this->email,                // Will be encrypted by EncryptsEphi trait
                'preferred_date' => $this->preferred_date,
                'preferred_time' => $this->preferred_time,
                'interests' => $this->interests,
                'message' => $this->message,            // Will be encrypted by EncryptsEphi trait
                'consent' => $this->consent,
                'audit_log' => [[
                    'action' => 'created',
                    'timestamp' => now()->toISOString(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]]
            ]);

            // Generate secure access token and set expiration
            $accessToken = $tourRequest->generateSecureAccessToken();
            $tourRequest->update([
                'access_token' => $accessToken,
                'expires_at' => now()->addHours(config('app.secure_access_hours', 72))
            ]);

            // 🔒 SECURE EMAIL: Send notification without PHI
            $employeeEmails = $this->allRecipients['employee_emails'] ?? [];
            
            if (!empty($employeeEmails)) {
                foreach ($employeeEmails as $employeeEmail) {
                    Mail::to($employeeEmail)->send(new SecureBookATourMail($tourRequest, $this->facility['name']));
                }
            } else {
                // Fallback to public emails if no employee emails are configured
                if (!empty($this->recipient)) {
                    Mail::to($this->recipient)->send(new SecureBookATourMail($tourRequest, $this->facility['name']));
                }
            }

            session()->flash('success', '🔒 Your secure tour request has been submitted successfully! Staff will receive a secure notification.');
            
            // Scroll to top to show success message
            $this->dispatch('scrollToTop');
            
            // Clear the form after successful submission
            $this->clearForm();
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Please fix the validation errors below.');
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            // Scroll to top to show validation errors
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            Log::error('Error in secure BookATour submission', [
                'exception' => $e->getMessage(),
                'facility_id' => $this->facility['id']
            ]);
            session()->flash('error', 'There was an error submitting your request. Please try again later.');
            // Scroll to top to show error message
            $this->dispatch('scrollToTop');
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function clearForm()
    {
        $this->reset([
            'full_name',
            'relationship', 
            'phone',
            'email',
            'preferred_date',
            'preferred_time',
            'specific_time',
            'interests',
            'message',
            'consent'
        ]);
        
        // Clear any validation errors
        $this->resetErrorBag();
        
        // Reset preferred_time to empty to ensure dropdown shows default state
        $this->preferred_time = '';
    }

    public function updatedSpecificTime($value)
    {
        if (!empty($value)) {
            $this->preferred_time = $value;
        }
    }

    public function updated($propertyName)
    {
        $this->resetErrorBag($propertyName);
    }

    public function render()
    {
        return view('livewire.book-a-tour', [
            'facility' => $this->facility,
            'primary' => $this->primary,
            'accent' => $this->accent,
            'secondary' => $this->secondary,
            'neutral_dark' => $this->neutral_dark,
            'neutral_light' => $this->neutral_light,
            'services' => $this->services,
        ]);
    }
}
