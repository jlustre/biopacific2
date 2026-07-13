<?php

namespace App\Livewire\Admin;

use App\Models\EmployeeEmailMapping;
use App\Models\Facility;
use App\Models\User;
use App\Services\PortalHelpRecipientService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeEmailMappings extends Component
{
    use WithPagination;

    public string $activeTab = 'website';

    public $search = '';

    public $selectedFacility = '';

    public $selectedCategory = '';

    public $employee_id = null;

    public $facility_id = '';

    public $category = '';

    public $contact_role = '';

    public $user_id = '';

    public $employee_name = '';

    public $employee_email = '';

    public $title = '';

    public $is_primary = false;

    public $is_active = true;

    public $on_vacation = false;

    public $vacation_starts_at = '';

    public $vacation_ends_at = '';

    public $showModal = false;

    public $editMode = false;

    public ?int $scopedFacilityId = null;

    public bool $canFilterFacilities = true;

    protected $queryString = [
        'activeTab' => ['except' => 'website'],
        'selectedCategory' => ['except' => ''],
        'selectedFacility' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    protected function rules()
    {
        $isPortalHelp = $this->activeTab === 'portal-help' || EmployeeEmailMapping::isPortalHelpCategory((string) $this->category);

        if ($isPortalHelp) {
            return [
                'contact_role' => ['required', Rule::in(array_keys($this->contactRoles))],
                'facility_id' => 'nullable|exists:facilities,id',
                'user_id' => 'nullable|exists:users,id',
                'employee_name' => 'required|string|max:255',
                'employee_email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('employee_email_mappings', 'employee_email')
                        ->where(fn ($q) => $q->where('category', $this->category)
                            ->where('facility_id', $this->facility_id ?: null))
                        ->ignore($this->employee_id),
                ],
                'title' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'on_vacation' => 'boolean',
                'vacation_starts_at' => 'nullable|date',
                'vacation_ends_at' => 'nullable|date|after_or_equal:vacation_starts_at',
            ];
        }

        return [
            'facility_id' => 'required|exists:facilities,id',
            'category' => ['required', Rule::in(array_keys(EmployeeEmailMapping::websiteCategories()))],
            'employee_name' => 'required|string|max:255',
            'employee_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employee_email_mappings', 'employee_email')
                    ->where('facility_id', $this->facility_id)
                    ->where('category', $this->category)
                    ->ignore($this->employee_id),
            ],
            'title' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'facility_id.required' => 'Please select a facility.',
        'category.required' => 'Please select a category.',
        'contact_role.required' => 'Please select a contact role.',
        'employee_name.required' => 'Name is required.',
        'employee_email.required' => 'Email is required.',
        'employee_email.email' => 'Please enter a valid email address.',
        'employee_email.unique' => 'This email is already assigned for this role/category.',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole(['admin', 'super-admin', 'rdhr']) && $user->facility_id) {
            $this->scopedFacilityId = (int) $user->facility_id;
            $this->canFilterFacilities = false;
            $this->selectedFacility = (string) $this->scopedFacilityId;
        }

        if (! in_array($this->activeTab, ['website', 'portal-help'], true)) {
            $this->activeTab = 'website';
        }
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->selectedCategory = '';
        $this->closeModal();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['website', 'portal-help'], true) ? $tab : 'website';
        $this->updatedActiveTab();
    }

    protected function enforceScopedFacilityOnForm(): void
    {
        if ($this->scopedFacilityId && $this->activeTab === 'website') {
            $this->facility_id = $this->scopedFacilityId;
        }
    }

    protected function authorizeMappingFacility(?int $facilityId): void
    {
        if ($this->scopedFacilityId && $facilityId && (int) $facilityId !== $this->scopedFacilityId) {
            abort(403, 'You do not have access to email mappings for this facility.');
        }
    }

    protected function findAuthorizedMapping(int $id): EmployeeEmailMapping
    {
        $mapping = EmployeeEmailMapping::findOrFail($id);
        $this->authorizeMappingFacility($mapping->facility_id);

        return $mapping;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedFacility()
    {
        if ($this->scopedFacilityId) {
            $this->selectedFacility = (string) $this->scopedFacilityId;
        }
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatedUserId($value): void
    {
        if (! $value) {
            return;
        }

        $user = User::query()->find($value);
        if (! $user) {
            return;
        }

        $this->employee_name = $user->name;
        $this->employee_email = $user->email;
    }

    public function updatedContactRole($value): void
    {
        $meta = $this->contactRoles[$value] ?? null;
        if (! $meta) {
            return;
        }

        $this->category = $meta['channel'];
        $this->is_primary = ($meta['responsibility'] ?? 'secondary') === 'primary';
        $this->title = $meta['short_label'] ?? $meta['label'] ?? '';
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $mapping = $this->findAuthorizedMapping($id);

        $this->employee_id = $mapping->id;
        $this->facility_id = $mapping->facility_id ? (string) $mapping->facility_id : '';
        $this->category = $mapping->category;
        $this->contact_role = $mapping->contact_role ?? '';
        $this->user_id = $mapping->user_id ? (string) $mapping->user_id : '';
        $this->employee_name = $mapping->employee_name;
        $this->employee_email = $mapping->employee_email;
        $this->title = $mapping->title;
        $this->is_primary = $mapping->is_primary;
        $this->is_active = $mapping->is_active;
        $this->on_vacation = $mapping->on_vacation;
        $this->vacation_starts_at = optional($mapping->vacation_starts_at)->format('Y-m-d') ?? '';
        $this->vacation_ends_at = optional($mapping->vacation_ends_at)->format('Y-m-d') ?? '';

        $this->activeTab = EmployeeEmailMapping::isPortalHelpCategory($mapping->category) ? 'portal-help' : 'website';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->enforceScopedFacilityOnForm();

        if ($this->activeTab === 'portal-help') {
            $meta = $this->contactRoles[$this->contact_role] ?? null;
            if ($meta) {
                $this->category = $meta['channel'];
                $this->is_primary = ($meta['responsibility'] ?? 'secondary') === 'primary';
                if (! filled($this->title)) {
                    $this->title = $meta['short_label'] ?? '';
                }
            }
        }

        $this->validate();

        DB::transaction(function () {
            $facilityId = $this->facility_id !== '' ? (int) $this->facility_id : null;

            if ($this->is_primary) {
                $query = EmployeeEmailMapping::query()
                    ->where('category', $this->category)
                    ->where('is_primary', true)
                    ->when($this->employee_id, fn ($q) => $q->where('id', '!=', $this->employee_id));

                if ($facilityId === null) {
                    $query->whereNull('facility_id');
                } else {
                    $query->where('facility_id', $facilityId);
                }

                $updated = $query->update(['is_primary' => false]);
                if ($updated > 0) {
                    session()->flash('info', "Removed primary status from {$updated} existing primary contact(s).");
                }
            }

            $payload = [
                'facility_id' => $facilityId,
                'category' => $this->category,
                'contact_role' => $this->activeTab === 'portal-help' ? ($this->contact_role ?: null) : null,
                'user_id' => $this->user_id !== '' ? (int) $this->user_id : null,
                'employee_name' => $this->employee_name,
                'employee_email' => $this->employee_email,
                'title' => $this->title,
                'is_primary' => (bool) $this->is_primary,
                'is_active' => (bool) $this->is_active,
                'on_vacation' => $this->activeTab === 'portal-help' ? (bool) $this->on_vacation : false,
                'vacation_starts_at' => $this->activeTab === 'portal-help' && $this->vacation_starts_at
                    ? $this->vacation_starts_at
                    : null,
                'vacation_ends_at' => $this->activeTab === 'portal-help' && $this->vacation_ends_at
                    ? $this->vacation_ends_at
                    : null,
            ];

            if ($this->editMode) {
                $mapping = $this->findAuthorizedMapping((int) $this->employee_id);
                $mapping->update($payload);
                session()->flash('success', 'Role mapping updated successfully.');
            } else {
                EmployeeEmailMapping::create($payload);
                session()->flash('success', 'Role mapping created successfully.');
            }
        });

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $mapping = $this->findAuthorizedMapping($id);
        $mapping->delete();
        session()->flash('success', 'Mapping deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $mapping = $this->findAuthorizedMapping($id);
        $newStatus = ! $mapping->is_active;

        if (! $newStatus && $mapping->is_primary) {
            $effectivePrimary = EmployeeEmailMapping::getEffectivePrimary($mapping->facility_id, $mapping->category);
            if ($effectivePrimary && $effectivePrimary->id !== $mapping->id) {
                session()->flash('warning', "Primary contact deactivated. {$effectivePrimary->employee_name} will serve as effective primary.");
            } else {
                $fallback = EmployeeEmailMapping::query()
                    ->where('category', $mapping->category)
                    ->where('id', '!=', $mapping->id)
                    ->active()
                    ->when(
                        $mapping->facility_id === null,
                        fn ($q) => $q->whereNull('facility_id'),
                        fn ($q) => $q->where('facility_id', $mapping->facility_id)
                    )
                    ->first();

                if ($fallback) {
                    session()->flash('warning', "Primary contact deactivated. {$fallback->employee_name} will serve as effective primary.");
                } else {
                    session()->flash('error', 'Warning: No other active contacts found for this role. Assign another person before deactivating.');

                    return;
                }
            }
        }

        $mapping->update(['is_active' => $newStatus]);
        session()->flash('success', 'Mapping '.($newStatus ? 'activated' : 'deactivated').' successfully.');
    }

    public function toggleVacation($id)
    {
        $mapping = $this->findAuthorizedMapping($id);
        $mapping->update(['on_vacation' => ! $mapping->on_vacation]);
        $label = $mapping->on_vacation ? 'marked on vacation' : 'cleared from vacation';
        session()->flash('success', "{$mapping->employee_name} {$label}. Delivery will use the next most responsible available contact.");
    }

    public function makePrimary($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $mapping = $this->findAuthorizedMapping($id);

                if ($mapping->is_primary) {
                    session()->flash('info', "{$mapping->employee_name} is already the primary contact.");

                    return;
                }

                $query = EmployeeEmailMapping::query()
                    ->where('category', $mapping->category)
                    ->where('is_primary', true);

                if ($mapping->facility_id === null) {
                    $query->whereNull('facility_id');
                } else {
                    $query->where('facility_id', $mapping->facility_id);
                }

                $query->update(['is_primary' => false]);

                $mapping->update([
                    'is_primary' => true,
                    'is_active' => true,
                    'on_vacation' => false,
                ]);

                $scope = $mapping->facility?->name ?? 'Organization';
                session()->flash('success', "{$mapping->employee_name} is now the primary contact for {$mapping->categoryLabel()} ({$scope}).");
            });
        } catch (\Exception $e) {
            Log::error('Error in makePrimary: '.$e->getMessage());
            session()->flash('error', 'Error updating primary contact: '.$e->getMessage());
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
        $this->facility_id = $this->scopedFacilityId && $this->activeTab === 'website'
            ? (string) $this->scopedFacilityId
            : '';
        $this->category = '';
        $this->contact_role = '';
        $this->user_id = '';
        $this->employee_name = '';
        $this->employee_email = '';
        $this->title = '';
        $this->is_primary = false;
        $this->is_active = true;
        $this->on_vacation = false;
        $this->vacation_starts_at = '';
        $this->vacation_ends_at = '';
        $this->resetErrorBag();
    }

    public function getContactRolesProperty(): array
    {
        return app(PortalHelpRecipientService::class)->flattenedContactRoles();
    }

    public function getCandidateUsersProperty()
    {
        return app(PortalHelpRecipientService::class)->candidateUsers();
    }

    public function getEmployeeMappingsProperty()
    {
        $portalCategories = array_keys(EmployeeEmailMapping::portalHelpCategories());
        $websiteCategories = array_keys(EmployeeEmailMapping::websiteCategories());

        return EmployeeEmailMapping::with(['facility', 'user'])
            ->when($this->activeTab === 'portal-help', fn ($q) => $q->whereIn('category', $portalCategories))
            ->when($this->activeTab === 'website', fn ($q) => $q->whereIn('category', $websiteCategories))
            ->when($this->scopedFacilityId && $this->activeTab === 'website', function ($q) {
                $q->where('facility_id', $this->scopedFacilityId);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('employee_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_email', 'like', '%'.$this->search.'%')
                        ->orWhere('title', 'like', '%'.$this->search.'%')
                        ->orWhere('contact_role', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedFacility !== '', function ($q) {
                $q->where('facility_id', $this->selectedFacility);
            })
            ->when($this->selectedCategory !== '', function ($q) {
                $q->where('category', $this->selectedCategory);
            })
            ->orderByRaw('facility_id is null')
            ->orderBy('facility_id')
            ->orderBy('category')
            ->orderByDesc('is_primary')
            ->orderBy('employee_name')
            ->paginate(15);
    }

    public function getFacilitiesProperty()
    {
        if ($this->scopedFacilityId) {
            return Facility::where('id', $this->scopedFacilityId)->orderBy('name')->get();
        }

        return Facility::orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return $this->activeTab === 'portal-help'
            ? EmployeeEmailMapping::portalHelpCategories()
            : EmployeeEmailMapping::websiteCategories();
    }

    public function getWarningsProperty()
    {
        $warnings = collect();

        if ($this->activeTab === 'portal-help') {
            foreach (EmployeeEmailMapping::portalHelpCategories() as $category => $label) {
                $status = EmployeeEmailMapping::getPrimaryStatus(null, $category);
                if ($status['status'] === 'no_primary') {
                    $warnings->push([
                        'facility' => 'Organization',
                        'category' => $label,
                        'message' => 'No primary role holder mapped',
                        'type' => 'danger',
                    ]);
                } elseif ($status['status'] === 'primary_inactive') {
                    $warnings->push([
                        'facility' => 'Organization',
                        'category' => $label,
                        'message' => $status['message'],
                        'type' => 'warning',
                    ]);
                }
            }

            return $warnings;
        }

        $facilities = $this->scopedFacilityId
            ? Facility::where('id', $this->scopedFacilityId)->get()
            : Facility::all();

        foreach ($facilities as $facility) {
            foreach (array_keys(EmployeeEmailMapping::websiteCategories()) as $category) {
                $status = EmployeeEmailMapping::getPrimaryStatus($facility->id, $category);
                if ($status['status'] === 'primary_inactive') {
                    $warnings->push([
                        'facility' => $facility->name,
                        'category' => EmployeeEmailMapping::websiteCategories()[$category],
                        'message' => $status['message'],
                        'type' => 'warning',
                    ]);
                }
                if ($status['status'] === 'no_primary') {
                    $warnings->push([
                        'facility' => $facility->name,
                        'category' => EmployeeEmailMapping::websiteCategories()[$category],
                        'message' => $status['message'],
                        'type' => 'danger',
                    ]);
                }
            }
        }

        return $warnings;
    }

    public function render()
    {
        $scopedFacility = $this->scopedFacilityId
            ? Facility::find($this->scopedFacilityId)
            : null;

        return view('livewire.admin.employee-email-mappings', [
            'employeeMappings' => $this->employeeMappings,
            'facilities' => $this->facilities,
            'categories' => $this->categories,
            'warnings' => $this->warnings,
            'scopedFacility' => $scopedFacility,
            'contactRoles' => $this->contactRoles,
            'candidateUsers' => $this->candidateUsers,
            'allCategoryLabels' => EmployeeEmailMapping::allCategories(),
        ]);
    }
}
