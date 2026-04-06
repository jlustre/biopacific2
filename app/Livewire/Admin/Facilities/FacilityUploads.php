<?php

namespace App\Livewire\Admin\Facilities;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Facility;
use App\Models\BPEmployee;
use App\Models\UploadType;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;

class FacilityUploads extends Component
{
    use WithFileUploads;

    public $facility_id = '';
    public $employee_id = '';
    public $upload_type_id = '';
    public $file;
    public $effective_start_date;
    public $effective_end_date;
    public $expires_at;
    public $comments;
    public $facilities = [];
    public $employees = [];
    public $uploadTypes = [];

    public function mount()
    {
        $this->facilities = Facility::orderBy('name')->get();
        $this->uploadTypes = UploadType::orderBy('name')->get();
        $this->employees = collect();
    }

    public function updatedFacility_id($value)
    {
        $this->employee_id = '';
        $this->employees = $value ? BPEmployee::whereHas('assignments', function($q) use ($value) {
            $q->where('facility_id', $value);
        })->orderBy('last_name')->get() : collect();
    }

    public function submit()
    {
        $this->validate([
            'facility_id' => 'required|exists:facilities,id',
            'employee_id' => 'required|exists:bp_employees,emp_id',
            'upload_type_id' => 'required|exists:upload_types,id',
            'file' => 'required|file',
        ]);
        $path = $this->file->store('uploads');
        Upload::create([
            'facility_id' => $this->facility_id,
            'user_id' => Auth::id(),
            'upload_type_id' => $this->upload_type_id,
            'file_path' => $path,
            'original_filename' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'uploaded_at' => now(),
            'expires_at' => $this->expires_at,
            'effective_start_date' => $this->effective_start_date,
            'effective_end_date' => $this->effective_end_date,
            'comments' => $this->comments,
        ]);
        session()->flash('success', 'File uploaded successfully.');
        $this->reset(['employee_id','upload_type_id','file','effective_start_date','effective_end_date','expires_at','comments']);
        $this->employees = $this->facility_id ? BPEmployee::whereHas('assignments', function($q) {
            $q->where('facility_id', $this->facility_id);
        })->orderBy('last_name')->get() : collect();
    }

    public function render()
    {
        return view('livewire.admin.facilities.facility-uploads');
    }
}
