<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\FacilityDataHelper;
use Illuminate\Support\Facades\DB;

class BookATour extends Component
{
    public $facility;
    public $primary;
    public $accent;
    public $secondary;
    public $services = [];

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
        $this->services = FacilityDataHelper::getServices($facility);
        $this->recipient = FacilityDataHelper::getEmailRecipient($facility->id, 'book-a-tour');

        // Initialize preferred_time to avoid null issues
        $this->preferred_time = '';

    }

    public function submit()
    {
        try {
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

            DB::table('tour_requests')->insert([
                'facility_id' => $this->facility['id'],
                'recipient' => $this->recipient,
                'full_name' => $this->full_name,
                'relationship' => $this->relationship,
                'phone' => $this->phone,
                'email' => $this->email,
                'preferred_date' => $this->preferred_date,
                'preferred_time' => $this->preferred_time,
                'interests' => json_encode($this->interests),
                'message' => $this->message,
                'consent' => $this->consent,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('success', 'Your tour request has been submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error submitting your request. Please try again later.');
        }
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
            'services' => $this->services,
        ]);
    }
}
