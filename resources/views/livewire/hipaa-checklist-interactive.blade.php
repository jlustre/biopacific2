<div class="rounded-2xl border border-slate-200 bg-white/80 backdrop-blur p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900">HIPAA Website Readiness</h3>
            <p class="mt-1 text-sm text-slate-600">Internal checklist for {{ $facility->name }}.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($showCompletedMessage) && $showCompletedMessage)
            <div class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm animate-pulse">
                ✓ Saved!
            </div>
            @endif
            <div class="text-sm">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full ring-1 ring-slate-200 bg-slate-50">
                    <span
                        class="inline-block h-2 w-2 rounded-full @if($completedCount == $totalCount) bg-emerald-500 @else bg-amber-500 @endif"></span>
                    <span>{{ $completedCount ?? 0 }} / {{ $totalCount ?? 0 }} Passed</span>
                </span>
            </div>
        </div>
    </div>

    <ul class="divide-y divide-slate-200">
        @foreach($rows as $row)
        <li class="py-4 grid gap-3 md:grid-cols-[1fr,auto] md:items-center">
            <div class="flex items-start gap-3">
                @if($row['passed'])
                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                @else
                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-4 w-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M12 9v4m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z" />
                    </svg>
                </span>
                @endif

                {{-- Define key variable first --}}
                @php $key = $row['key']; @endphp

                <div class="flex-1">
                    <div class="font-medium text-slate-900" id="label-{{ $key }}" @if($flags[$key] ?? false)
                        style="text-decoration: line-through; color: #059669;" @else style="color: #1f2937;" @endif>{{
                        $row['label'] }}</div>
                    @if(!$row['passed'])
                    <div class="text-sm text-slate-600 mt-1">{{ $row['help'] }}</div>
                    @endif
                </div>
            </div>

            {{-- Toggle bound to flags --}}
            <div class="inline-flex items-center gap-2 justify-self-end">
                <span class="text-xs text-slate-600" id="button-{{ $key }}">
                    {{ ($flags[$key] ?? false) ? 'Undo' : 'Mark as done' }}
                </span>

                {{-- Original toggle switch --}}
                <button type="button" wire:click="toggleFlag('{{ $key }}')" id="toggle-{{ $key }}"
                    class="relative inline-flex h-6 w-10 items-center rounded-full cursor-pointer transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    style="background-color: {{ ($flags[$key] ?? false) ? ($facility->primary_color ?? '#10B981') : '#e5e7eb' }}">
                    <span
                        class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform duration-200"
                        style="transform: translateX({{ ($flags[$key] ?? false) ? '16px' : '0px' }});"></span>
                </button>
            </div>
        </li>
        @endforeach
    </ul>

    {{-- NPP URL Section --}}
    @if($facility->npp_url)
    <div class="mt-6 pt-4 border-t border-slate-200">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                </path>
            </svg>
            <span>NPP URL: </span>
            <a href="{{ $facility->npp_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                {{ $facility->npp_url }}
            </a>
        </div>
    </div>
    @endif

    {{-- Admin Controls --}}
    @can('manage', $facility)
    <div class="mt-6 pt-4 border-t border-slate-200">
        <div class="flex justify-between items-center">
            <button type="button" wire:click="resetHipaaFlags"
                wire:confirm="Are you sure you want to reset all HIPAA checklist items?"
                class="text-sm text-red-600 hover:text-red-800 border border-red-300 px-3 py-1 rounded">
                Reset All Items
            </button>

            @if($completedCount == $totalCount)
            <div class="bg-emerald-50 text-emerald-800 px-4 py-2 rounded-lg text-sm font-medium">
                🎉 All HIPAA requirements completed!
            </div>
            @endif
        </div>
    </div>
    @endcan <div class="mt-4 text-xs text-slate-500">
        *This is an internal readiness checklist (not legal advice). Keep BAAs and policies on file.
    </div>
</div>


@push('scripts')
<script>
    // Listen for browser events from Livewire
    window.addEventListener('notify', event => {
        // Show success notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 bg-green-500 text-white';
        // In Livewire 3, dispatched data is available directly on event.detail
        toast.textContent = event.detail.msg || event.detail[0]?.msg || 'HIPAA checklist updated!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });

    // Handle Livewire/AJAX fallback for toggle functionality
    document.addEventListener('click', (e) => {
        // Check if the clicked element or its parent has wire:click
        const clickedElement = e.target.closest('[wire\\:click]');
        if (clickedElement) {
            const wireAction = clickedElement.getAttribute('wire:click');
            
            // Try Livewire first - let it handle the click naturally
            if (typeof Livewire !== 'undefined') {
                const components = Livewire.all();
                
                if (components.length > 0) {
                    // Don't prevent default - let Livewire handle it
                    return;
                }
            }
            
            // Only use AJAX fallback if Livewire is completely unavailable
            e.preventDefault();
            e.stopPropagation();
            
            if (wireAction.startsWith('toggleFlag(')) {
                const match = wireAction.match(/toggleFlag\('(.+)'\)/);
                if (match) {
                    const key = match[1];
                    
                    fetch(`/admin/facilities/{{ $facility->id }}/hipaa/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            key: key
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update the toggle switch visual state
                            const toggleButton = document.getElementById(`toggle-${key}`);
                            const buttonText = document.getElementById(`button-${key}`);
                            const toggleLabel = document.getElementById(`label-${key}`);
                            
                            if (toggleButton) {
                                // Update button background color and switch position
                                toggleButton.style.backgroundColor = data.new_value ? '{{ $facility->primary_color ?? "#10B981" }}' : '#e5e7eb';
                                const switchElement = toggleButton.querySelector('span');
                                if (switchElement) {
                                    switchElement.style.transform = `translateX(${data.new_value ? '16px' : '0px'})`;
                                }
                            }
                            
                            // Update button text based on new state
                            if (buttonText) {
                                buttonText.textContent = data.new_value ? 'Undo' : 'Mark as done';
                            }
                            
                            // Update label styling
                            if (toggleLabel) {
                                if (data.new_value) {
                                    toggleLabel.style.textDecoration = 'line-through';
                                    toggleLabel.style.color = '#059669';
                                } else {
                                    toggleLabel.style.textDecoration = 'none';
                                    toggleLabel.style.color = '#1f2937';
                                }
                            }
                            
                            // Update progress
                            const completed = Object.values(data.flags).filter(Boolean).length;
                            const total = Object.keys(data.flags).length;
                            const percentage = Math.round((completed / total) * 100);
                            
                            const progressText = document.querySelector('.text-sm.text-gray-600');
                            if (progressText) {
                                progressText.textContent = `${completed} of ${total} items completed (${percentage}%)`;
                            }
                            
                            const progressBar = document.querySelector('.bg-green-600');
                            if (progressBar) {
                                progressBar.style.width = `${percentage}%`;
                            }
                            
                            // Show success message
                            const toast = document.createElement('div');
                            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 bg-green-500 text-white`;
                            toast.textContent = data.message;
                            document.body.appendChild(toast);
                            setTimeout(() => toast.remove(), 3000);
                        }
                    })
                    .catch(error => {
                        const toast = document.createElement('div');
                        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 bg-red-500 text-white`;
                        toast.textContent = `Error: ${error.message}`;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 5000);
                    });
                }
            }
        }
    });
</script>
@endpush