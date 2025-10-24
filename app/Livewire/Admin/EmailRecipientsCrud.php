<?php

namespace App\Livewire\Admin;

use App\Models\EmailRecipient;
use Livewire\Component;
use Livewire\WithPagination;

class EmailRecipientsCrud extends Component
{
    use WithPagination;

    public $emailRecipient;
    public $isEditing = false;
    public $showModal = false;

    protected $rules = [
        'emailRecipient.facility_id' => 'required|exists:facilities,id',
        'emailRecipient.category' => 'required|max:255',
        'emailRecipient.email' => 'required|email',
    ];

    public function render()
    {
        return view('livewire.admin.email-recipients-crud', [
            'emailRecipients' => EmailRecipient::paginate(10),
        ]);
    }

    public function create()
    {
        $this->emailRecipient = new EmailRecipient();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(EmailRecipient $emailRecipient)
    {
        $this->emailRecipient = $emailRecipient;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $this->emailRecipient->save();
        $this->showModal = false;
    }

    public function delete(EmailRecipient $emailRecipient)
    {
        $emailRecipient->delete();
    }
}
