<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeEmailMapping;
use App\Models\Facility;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeEmailMappings extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedFacility = '';
    public $selectedCategory = '';
    
    // Form fields
    public $employee_id = null;
    public $facility_id = '';
    public $category = '';
    public $employee_name = '';
    public $employee_email = '';
    public $title = '';
    public $is_primary = false;
    public $is_active = true;
    
    // Modal control
    public $showModal = false;
    public $editMode = false;
    
    protected $categories = [
        'book-a-tour' => 'Book a Tour',
        'inquiry' => 'General Inquiry',
        'hiring' => 'Hiring/Careers'
    ];

    protected function rules()
    {
        $rules = [
            'facility_id' => 'required|exists:facilities,id',
            'category' => ['required', Rule::in(array_keys($this->categories))],
            'employee_name' => 'required|string|max:255',
            'employee_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employee_email_mappings', 'employee_email')
                    ->where('facility_id', $this->facility_id)
                    ->where('category', $this->category)
                    ->ignore($this->employee_id)
            ],
            'title' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];

        // Add custom validation for primary contact uniqueness
        if ($this->is_primary) {
            $existingPrimary = EmployeeEmailMapping::where('facility_id', $this->facility_id)
                ->where('category', $this->category)
                ->where('is_primary', true)
                ->when($this->employee_id, function($query) {
                    return $query->where('id', '!=', $this->employee_id);
                })
                ->exists();

            if ($existingPrimary) {
                // We'll handle this in the save method with automatic removal
                // This is just for additional validation if needed
            }
        }

        return $rules;
    }

    protected $messages = [
        'facility_id.required' => 'Please select a facility.',
        'category.required' => 'Please select a category.',
        'employee_name.required' => 'Employee name is required.',
        'employee_email.required' => 'Employee email is required.',
        'employee_email.email' => 'Please enter a valid email address.',
        'employee_email.unique' => 'This email is already assigned to this category for this facility.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedFacility()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $mapping = EmployeeEmailMapping::findOrFail($id);
        
        $this->employee_id = $mapping->id;
        $this->facility_id = $mapping->facility_id;
        $this->category = $mapping->category;
        $this->employee_name = $mapping->employee_name;
        $this->employee_email = $mapping->employee_email;
        $this->title = $mapping->title;
        $this->is_primary = $mapping->is_primary;
        $this->is_active = $mapping->is_active;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Database transaction to ensure data consistency
        DB::transaction(function () {
            // If setting as primary, unset other primary contacts for the same facility/category
            if ($this->is_primary) {
                // Use bulk update to remove primary status from others
                $updated = EmployeeEmailMapping::where('facility_id', $this->facility_id)
                    ->where('category', $this->category)
                    ->where('id', '!=', $this->employee_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);

                if ($updated > 0) {
                    session()->flash('info', "Removed primary status from {$updated} existing primary contact(s) to maintain single primary rule.");
                }
            }

            if ($this->editMode) {
            $mapping = EmployeeEmailMapping::findOrFail($this->employee_id);
            $mapping->update([
                'facility_id' => $this->facility_id,
                'category' => $this->category,
                'employee_name' => $this->employee_name,
                'employee_email' => $this->employee_email,
                'title' => $this->title,
                'is_primary' => $this->is_primary,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Employee email mapping updated successfully.');
        } else {
            EmployeeEmailMapping::create([
                'facility_id' => $this->facility_id,
                'category' => $this->category,
                'employee_name' => $this->employee_name,
                'employee_email' => $this->employee_email,
                'title' => $this->title,
                'is_primary' => $this->is_primary,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Employee email mapping created successfully.');
            }
        });

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $mapping = EmployeeEmailMapping::findOrFail($id);
        $mapping->delete();
        
        session()->flash('success', 'Employee email mapping deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $mapping = EmployeeEmailMapping::findOrFail($id);
        $newStatus = !$mapping->is_active;
        
        // If we're deactivating a primary contact, warn about the consequences
        if (!$newStatus && $mapping->is_primary) {
            $effectivePrimary = EmployeeEmailMapping::where('facility_id', $mapping->facility_id)
                ->where('category', $mapping->category)
                ->where('id', '!=', $mapping->id)
                ->active()
                ->first();
                
            if ($effectivePrimary) {
                session()->flash('warning', "Primary contact deactivated. {$effectivePrimary->employee_name} will serve as effective primary.");
            } else {
                session()->flash('error', "Warning: No other active employees found for this category. Consider assigning another primary before deactivating.");
                return;
            }
        }
        
        $mapping->update(['is_active' => $newStatus]);
        
        $status = $mapping->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Employee email mapping {$status} successfully.");
    }

    public function makePrimary($id)
    {
        try {
            Log::info("makePrimary called with ID: {$id}");
            
            DB::transaction(function () use ($id) {
                $mapping = EmployeeEmailMapping::findOrFail($id);
                Log::info("Found mapping: {$mapping->employee_name} - Current primary status: " . ($mapping->is_primary ? 'true' : 'false'));
                
                // Check if this mapping is already primary
                if ($mapping->is_primary) {
                    Log::info("Mapping is already primary, returning early");
                    session()->flash('info', "{$mapping->employee_name} is already the primary contact.");
                    return;
                }
                
                // First, remove primary status from ANY current primary for this facility/category
                // Use a single update to avoid constraint issues
                $updated = EmployeeEmailMapping::where('facility_id', $mapping->facility_id)
                    ->where('category', $mapping->category)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);

                Log::info("Removed primary status from {$updated} existing primary contact(s)");

                // Now set new primary (also ensure it's active)
                $result = $mapping->update([
                    'is_primary' => true, 
                    'is_active' => true
                ]);
                
                Log::info("Update result: " . ($result ? 'success' : 'failed'));
                
                session()->flash('success', "{$mapping->employee_name} is now the primary contact for {$this->categories[$mapping->category]} at {$mapping->facility->name}.");
            });
        } catch (\Exception $e) {
            Log::error("Error in makePrimary: " . $e->getMessage());
            session()->flash('error', 'Error updating primary contact: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->employee_id = null;
        $this->facility_id = '';
        $this->category = '';
        $this->employee_name = '';
        $this->employee_email = '';
        $this->title = '';
        $this->is_primary = false;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function getEmployeeMappingsProperty()
    {
        $query = EmployeeEmailMapping::with('facility')
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('employee_name', 'like', '%' . $this->search . '%')
                          ->orWhere('employee_email', 'like', '%' . $this->search . '%')
                          ->orWhere('title', 'like', '%' . $this->search . '%');
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
            ->orderByDesc('is_primary')
            ->orderBy('employee_name');

        return $query->paginate(15);
    }

    public function getFacilitiesProperty()
    {
        return Facility::orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return $this->categories;
    }

    public function getWarningsProperty()
    {
        $warnings = collect();
        
        // Get all facilities and categories
        $facilities = Facility::all();
        $categories = ['book-a-tour', 'inquiry', 'hiring'];
        
        foreach ($facilities as $facility) {
            foreach ($categories as $category) {
                $status = EmployeeEmailMapping::getPrimaryStatus($facility->id, $category);
                
                if ($status['status'] === 'primary_inactive') {
                    $warnings->push([
                        'facility' => $facility->name,
                        'category' => $this->categories[$category],
                        'message' => $status['message'],
                        'type' => 'warning'
                    ]);
                }
                
                if ($status['status'] === 'no_primary') {
                    $warnings->push([
                        'facility' => $facility->name,
                        'category' => $this->categories[$category],
                        'message' => $status['message'],
                        'type' => 'danger'
                    ]);
                }

                // Check if category has no active employees at all
                $activeCount = EmployeeEmailMapping::where('facility_id', $facility->id)
                    ->where('category', $category)
                    ->active()
                    ->count();
                    
                if ($activeCount === 0) {
                    $warnings->push([
                        'facility' => $facility->name,
                        'category' => $this->categories[$category],
                        'message' => 'No active employees for this category',
                        'type' => 'danger'
                    ]);
                }
            }
        }
        
        return $warnings;
    }

    public function render()
    {
        return view('livewire.admin.employee-email-mappings', [
            'employeeMappings' => $this->employeeMappings,
            'facilities' => $this->facilities,
            'categories' => $this->categories,
            'warnings' => $this->warnings,
        ]);
    }
}
