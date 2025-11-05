<?php

namespace App\Livewire\Admin;

use App\Models\EmailRecipient;
use App\Models\Facility;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class EmailRecipientsCrud extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $selectedFacility = '';
    public $selectedCategory = '';

    // Modal Properties
    public $showModal = false;
    public $editMode = false;
    public $recipient_id = null;

    // Form Properties
    public $facility_id = '';
    public $category = '';
    public $email = '';
    public $email_alt_1 = '';
    public $email_alt_2 = '';

    // Available Categories
    public $categories = [
        'book-a-tour' => 'Book a Tour',
        'inquiry' => 'General Inquiry', 
        'hiring' => 'Hiring & Careers'
    ];

    protected function rules()
    {
        return [
            'facility_id' => 'required|exists:facilities,id',
            'category' => ['required', Rule::in(array_keys($this->categories))],
            'email' => 'required|email|max:255',
            'email_alt_1' => 'nullable|email|max:255',
            'email_alt_2' => 'nullable|email|max:255',
        ];
    }

    protected $messages = [
        'facility_id.required' => 'Please select a facility.',
        'category.required' => 'Please select a category.',
        'email.required' => 'Primary email is required.',
        'email.email' => 'Please enter a valid primary email address.',
    ];

    public function render()
    {
        return view('livewire.admin.email-recipients-crud', [
            'emailRecipients' => $this->emailRecipients,
            'facilities' => $this->facilities,
            'categories' => $this->categories,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $recipient = EmailRecipient::findOrFail($id);
        
        $this->recipient_id = $recipient->id;
        $this->facility_id = $recipient->facility_id;
        $this->category = $recipient->category;
        $this->email = $recipient->email;
        $this->email_alt_1 = $recipient->email_alt_1;
        $this->email_alt_2 = $recipient->email_alt_2;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $recipient = EmailRecipient::findOrFail($this->recipient_id);
            $recipient->update([
                'facility_id' => $this->facility_id,
                'category' => $this->category,
                'email' => $this->email,
                'email_alt_1' => $this->email_alt_1,
                'email_alt_2' => $this->email_alt_2,
            ]);
            
            session()->flash('success', 'Email recipient updated successfully.');
        } else {
            EmailRecipient::create([
                'facility_id' => $this->facility_id,
                'category' => $this->category,
                'email' => $this->email,
                'email_alt_1' => $this->email_alt_1,
                'email_alt_2' => $this->email_alt_2,
            ]);
            
            session()->flash('success', 'Email recipient created successfully.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $recipient = EmailRecipient::findOrFail($id);
        $recipient->delete();
        
        session()->flash('success', 'Email recipient deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->recipient_id = null;
        $this->facility_id = '';
        $this->category = '';
        $this->email = '';
        $this->email_alt_1 = '';
        $this->email_alt_2 = '';
        $this->resetErrorBag();
    }

    public function getEmailRecipientsProperty()
    {
        $query = EmailRecipient::with('facility')
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('email', 'like', '%' . $this->search . '%')
                          ->orWhere('email_alt_1', 'like', '%' . $this->search . '%')
                          ->orWhere('email_alt_2', 'like', '%' . $this->search . '%')
                          ->orWhere('category', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedFacility, function($q) {
                $q->where('facility_id', $this->selectedFacility);
            })
            ->when($this->selectedCategory, function($q) {
                $q->where('category', $this->selectedCategory);
            })
            ->orderBy('facility_id')
            ->orderBy('category')
            ->orderBy('email');

        return $query->paginate(15);
    }

    public function getFacilitiesProperty()
    {
        return Facility::orderBy('name')->get();
    }
}
