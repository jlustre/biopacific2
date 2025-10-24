<?php

namespace App\Livewire\Admin;

use App\Models\TourRequest;
use Livewire\Component;
use Livewire\WithPagination;

class TourRequestsCrud extends Component
{
    use WithPagination;

    public $tourRequest;
    public $isEditing = false;
    public $showModal = false;

    protected $rules = [
        'tourRequest.full_name' => 'required|max:255',
        'tourRequest.email' => 'required|email',
        'tourRequest.phone' => 'required',
        'tourRequest.preferred_date' => 'required|date',
        'tourRequest.preferred_time' => 'required',
    ];

    public function render()
    {
        return view('livewire.admin.tour-requests-crud', [
            'tourRequests' => TourRequest::paginate(10),
        ]);
    }

    public function create()
    {
        $this->tourRequest = new TourRequest();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(TourRequest $tourRequest)
    {
        $this->tourRequest = $tourRequest;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $this->tourRequest->save();
        $this->showModal = false;
    }

    public function delete(TourRequest $tourRequest)
    {
        $tourRequest->delete();
    }
}
