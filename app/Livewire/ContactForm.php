<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Inquiry;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\Log;
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

    public $facility; // Facility data
    public $primary;
    public $accent;
    public $secondary;

    protected $rules = [
        'full_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'required|email|max:255',
        'message' => 'required|string',
        'consent' => 'accepted',
        'no_phi' => 'accepted',
        'website' => 'nullable|string', // Honeypot validation
        'facility_id' => 'required|integer',
    ];
    protected $messages = [
        'no_phi.accepted' => 'Please confirm that you are not submitting personal health information (PHI).',
    ];

    public function mount($facility)
    {
        $this->facility = $facility;
        $colors = FacilityDataHelper::getColors($facility);
        $this->primary = $colors['primary'];
        $this->accent = $colors['accent'];
        $this->secondary = $colors['secondary'];
        $this->facility_id = $facility['id'];
        $this->recipient = FacilityDataHelper::getEmailRecipient($facility['id'], 'inquiry');
    }

    public function submit()
    {
        Log::info('Submit method triggered'); // Debug statement

        try {
            $validatedData = $this->validate();

            // Check honeypot field
            if (!empty($validatedData['website'])) {
                return; // Possible spam, do not process
            }

            // Save inquiry to the database
            Inquiry::create([
                'facility_id' => $this->facility_id,
                'recipient' => $this->recipient,
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'email' => $this->email,
                'message' => $this->message,
                'consent' => $this->consent,
                'no_phi' => $this->no_phi,
            ]);

            session()->flash('success', 'Your inquiry has been submitted successfully.');

            $this->emit('scrollToTop'); // Emit event to scroll to top

            $this->reset();
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error submitting your inquiry. Please try again later.');
        }
    }

    public function updated($propertyName)
    {
        $this->resetErrorBag($propertyName);
    }

    public function render()
    {
        return view('livewire.contact-form', [
            'facility' => $this->facility,
        ]);
    }
}
