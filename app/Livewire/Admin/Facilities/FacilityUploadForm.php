<?php

namespace App\Livewire\Admin\Facilities;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Facility;
use App\Models\BPEmployee;
use App\Models\UploadType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacilityUploadForm extends Component
{
    use WithFileUploads;

    public $facility_id;
    public $employee_id;
    public $upload_type_id;
    public $file;
    public $effective_start_date;
    // public $effective_end_date; // Removed
    public $expires_at;
    public $comments;
    public $uploadTypes = [];
    public $facilities = [];
    public $employees = [];

    public function mount($facilityId = null)
    {
        $this->facilities = Facility::orderBy('name')->get();
        $this->uploadTypes = UploadType::orderBy('name')->get();
        $this->facility_id = $facilityId;
        $this->updateEmployees();
    }

    public function updated($property, $value)
    {
        if ($property === 'facility_id') {
            // Cast to int for consistency
            $this->facility_id = $value ? (int)$value : null;
            // Debug: log to Laravel log
            Log::debug('Livewire: facility_id changed', [
                'property' => $property,
                'value' => $value,
                'casted_facility_id' => $this->facility_id,
                'old' => $this->facility_id,
            ]);
            $this->employee_id = '';
            $this->updateEmployees();
        }
    }

    public function updateEmployees()
    {
        Log::debug('updateEmployees called', [
            'facility_id' => $this->facility_id,
            'facility_id_type' => gettype($this->facility_id),
        ]);
        if ($this->facility_id) {
            $this->employees = BPEmployee::whereHas('assignments', function($q) {
                    $q->where('facility_id', $this->facility_id);
                })
                ->orderBy('last_name')
                ->get();
        } else {
            $this->employees = collect();
        }
    }

    public function submit()
    {
        $this->validate([
            'facility_id' => 'required|exists:facilities,id',
            'employee_id' => 'required|exists:bp_employees,id',
            'upload_type_id' => 'required|exists:upload_types,id',
            'file' => 'required|file',
        ]);
        $path = $this->file->store('uploads');
        // Save upload (simplified, add more fields as needed)
        \App\Models\Upload::create([
            'facility_id' => $this->facility_id,
            'user_id' => Auth::id(),
            'upload_type_id' => $this->upload_type_id,
            'file_path' => $path,
            'original_filename' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'uploaded_at' => now(),
            'expires_at' => $this->expires_at,
            'effective_start_date' => $this->effective_start_date,
            // 'effective_end_date' => $this->effective_end_date, // Removed
            'comments' => $this->comments,
        ]);
        session()->flash('success', 'File uploaded successfully.');
        $this->reset(['employee_id','upload_type_id','file','effective_start_date','expires_at','comments']);
        $this->updateEmployees();
    }

    public function render()
    {
        Log::debug('Livewire: FacilityUploadForm render() called', [
            'facility_id' => $this->facility_id,
            'employee_id' => $this->employee_id,
        ]);
        return view('livewire.admin.facilities.facility-upload-form');
    }
}
