<div class="mx-auto max-w-6xl">
    <div class="mb-6">
        <p class="text-sm text-slate-600">
            Map people to communication <strong>roles</strong>. Website forms stay facility-based;
            Contact HR and Technical Support use portal help roles with primary / secondary coverage and vacation fallback.
        </p>
    </div>

    @if (session()->has('success'))
    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
    @endif
    @if (session()->has('warning'))
    <div class="mb-4 rounded border border-amber-400 bg-amber-50 px-4 py-3 text-amber-800">{{ session('warning') }}</div>
    @endif
    @if (session()->has('error'))
    <div class="mb-4 rounded border border-red-400 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
    @endif
    @if (session()->has('info'))
    <div class="mb-4 rounded border border-blue-400 bg-blue-50 px-4 py-3 text-blue-800">{{ session('info') }}</div>
    @endif

    @if(isset($scopedFacility) && $scopedFacility)
    <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        Managing website mappings for <strong>{{ $scopedFacility->name }}</strong> only.
    </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <button type="button" wire:click="setTab('website')"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeTab === 'website' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            Website forms
        </button>
        <button type="button" wire:click="setTab('portal-help')"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeTab === 'portal-help' ? 'bg-teal-700 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' }}">
            Portal Help (HR &amp; Technical Support)
        </button>
    </div>

    @if($activeTab === 'portal-help')
    <div class="mb-6 grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-teal-200 bg-teal-50/70 p-4 text-sm text-teal-950">
            <p class="font-bold">Contact HR roles</p>
            <p class="mt-1 text-teal-900/80">Assign people to Primary / Secondary HR roles. If the primary is on vacation, messages default to the secondary.</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 text-sm text-indigo-950">
            <p class="font-bold">Technical Support roles</p>
            <p class="mt-1 text-indigo-900/80">Same primary / secondary pattern. All active role holders also see these messages in My Messages.</p>
        </div>
    </div>
    @endif

    @if($warnings->isNotEmpty())
    <div class="mb-4 rounded border-l-4 border-yellow-400 bg-yellow-50 p-4">
        <h3 class="text-sm font-medium text-yellow-800">Configuration warnings ({{ $warnings->count() }})</h3>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-yellow-700">
            @foreach($warnings as $warning)
            <li>
                <strong>{{ $warning['facility'] }}</strong> — {{ $warning['category'] }}: {{ $warning['message'] }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mb-6 rounded-lg border bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by name, email, or role..."
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500" />
                @if($canFilterFacilities && $activeTab === 'website')
                <select wire:model.live="selectedFacility" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm sm:w-48">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                    @endforeach
                </select>
                @endif
                <select wire:model.live="selectedCategory" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm sm:w-56">
                    <option value="">All {{ $activeTab === 'portal-help' ? 'channels' : 'categories' }}</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="create" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                {{ $activeTab === 'portal-help' ? 'Map person to role' : 'Add employee mapping' }}
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ $activeTab === 'portal-help' ? 'Role' : 'Category' }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Scope</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($employeeMappings as $mapping)
                    @php
                        $effectivePrimary = \App\Models\EmployeeEmailMapping::getEffectivePrimary($mapping->facility_id, $mapping->category);
                        $isEffectivePrimary = $effectivePrimary && $effectivePrimary->id === $mapping->id && ! $mapping->is_primary;
                        $categoryLabel = $allCategoryLabels[$mapping->category] ?? $mapping->categoryLabel();
                    @endphp
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $mapping->displayName() }}</div>
                            @if($mapping->title)
                            <div class="text-xs text-gray-500">{{ $mapping->title }}</div>
                            @endif
                            <div class="mt-1 flex flex-wrap gap-1">
                                @if($mapping->is_primary)
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-800">Primary</span>
                                @else
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">Secondary</span>
                                @endif
                                @if($isEffectivePrimary)
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-800">Effective primary</span>
                                @endif
                                @if($mapping->isAway())
                                <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase text-rose-700">On vacation</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($mapping->category === 'hr_inquiry') bg-teal-100 text-teal-800
                                @elseif($mapping->category === 'support') bg-indigo-100 text-indigo-800
                                @elseif($mapping->category === 'book-a-tour') bg-green-100 text-green-800
                                @elseif($mapping->category === 'inquiry') bg-blue-100 text-blue-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ $activeTab === 'portal-help' ? $mapping->contactRoleLabel() : $categoryLabel }}
                            </span>
                            @if($activeTab === 'portal-help')
                            <div class="mt-1 text-[11px] text-gray-500">{{ $categoryLabel }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-900">
                            {{ $mapping->facility?->name ?? 'Organization-wide' }}
                        </td>
                        <td class="px-6 py-4 text-gray-900">{{ $mapping->resolvedEmail() }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleStatus({{ $mapping->id }})"
                                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-medium {{ $mapping->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                {{ $mapping->is_active ? 'Active' : 'Inactive' }}
                            </button>
                            @if($activeTab === 'portal-help')
                            <button wire:click="toggleVacation({{ $mapping->id }})"
                                    class="ml-1 inline-flex rounded-full px-3 py-1.5 text-xs font-medium {{ $mapping->on_vacation ? 'bg-rose-100 text-rose-800 hover:bg-rose-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ $mapping->on_vacation ? 'Vacation' : 'Available' }}
                            </button>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if(! $mapping->is_primary && $mapping->is_active)
                                <button wire:click="makePrimary({{ $mapping->id }})"
                                        onclick="return confirm('Make {{ $mapping->employee_name }} the primary contact?')"
                                        class="rounded-lg p-2 text-green-600 hover:bg-green-50" title="Make primary">★</button>
                                @endif
                                <button wire:click="edit({{ $mapping->id }})" class="rounded-lg p-2 text-indigo-600 hover:bg-indigo-50" title="Edit">✎</button>
                                <button wire:click="delete({{ $mapping->id }})"
                                        onclick="return confirm('Delete this mapping?')"
                                        class="rounded-lg p-2 text-red-600 hover:bg-red-50" title="Delete">🗑</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <p class="font-medium text-gray-900">No mappings found</p>
                            <p class="mt-1 text-sm">{{ $activeTab === 'portal-help' ? 'Map a person to an HR or Technical Support role to get started.' : 'Create your first employee email mapping.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="block lg:hidden">
            @forelse($employeeMappings as $mapping)
            <div class="border-b border-gray-200 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-medium text-gray-900">{{ $mapping->displayName() }}</p>
                        <p class="text-xs text-gray-500">{{ $mapping->contactRoleLabel() }} · {{ $mapping->facility?->name ?? 'Organization-wide' }}</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $mapping->resolvedEmail() }}</p>
                    </div>
                    <button wire:click="edit({{ $mapping->id }})" class="text-sm font-semibold text-indigo-600">Edit</button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm text-gray-500">No mappings found.</div>
            @endforelse
        </div>

        @if($employeeMappings->hasPages())
        <div class="border-t border-gray-200 px-6 py-3">{{ $employeeMappings->links() }}</div>
        @endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModal"></div>
            <div class="relative inline-block w-full transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:max-w-lg sm:align-middle">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            {{ $editMode ? 'Edit mapping' : ($activeTab === 'portal-help' ? 'Map person to role' : 'Add employee mapping') }}
                        </h3>

                        @if($activeTab === 'portal-help')
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Contact role *</label>
                            <select wire:model.live="contact_role" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select role…</option>
                                @foreach($contactRoles as $roleKey => $meta)
                                <option value="{{ $roleKey }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            @error('contact_role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            @if($contact_role && !empty($contactRoles[$contact_role]['description']))
                            <p class="mt-1 text-xs text-gray-500">{{ $contactRoles[$contact_role]['description'] }}</p>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Portal user</label>
                            <select wire:model.live="user_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">— Optional: pick user to auto-fill —</option>
                                @foreach($candidateUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Facility scope</label>
                            <select wire:model="facility_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Organization-wide</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Leave organization-wide for company HR / tech roles.</p>
                        </div>
                        @else
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Facility *</label>
                            @if($scopedFacilityId ?? false)
                            <input type="text" readonly value="{{ $scopedFacility->name ?? '' }}" class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm" />
                            @else
                            <select wire:model="facility_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select Facility</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                            @endif
                            @error('facility_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Category *</label>
                            <select wire:model="category" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" wire:model="employee_name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Jane Doe">
                            @error('employee_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" wire:model="employee_email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="jane@biopacific.com">
                            @error('employee_email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Title / notes</label>
                            <input type="text" wire:model="title" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Optional">
                        </div>

                        @if($activeTab === 'portal-help')
                        <div class="mb-4 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Vacation start</label>
                                <input type="date" wire:model="vacation_starts_at" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Vacation end</label>
                                <input type="date" wire:model="vacation_ends_at" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600"> Active
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="on_vacation" class="rounded border-gray-300 text-amber-600"> Currently on vacation
                            </label>
                        </div>
                        <p class="mb-2 text-xs text-gray-500">Primary / secondary is set by the selected role. Vacation routes email to the next most responsible available person.</p>
                        @else
                        <div class="mb-4 flex flex-wrap gap-6">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="is_primary" class="rounded border-gray-300 text-blue-600"> Primary contact
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600"> Active
                            </label>
                        </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto">
                            {{ $editMode ? 'Update' : 'Save' }}
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
