@props([
'application',
])

@php
$activities = \App\Models\HiringActivityLog::where('pre_employment_application_id', $application->id)
->with(['performedBy', 'recipient'])
->orderByDesc('created_at')
->get();

$formLabels = [
'application_form' => 'Application Form',
'personal' => 'Personal Information',
'position' => 'Position Desired',
'drivers_license' => "Driver's License",
'work_authorization' => 'Work Authorization',
'work_experience' => 'Work Experience',
'education' => 'Education',
'previous_addresses' => 'Previous Addresses',
'other' => 'Other/Multiple Sections',
];
@endphp

<div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="{
        sectionOpen: true,
        sectionStorageKey: '-section-{{ $application->id }}',
        openIndexes: [],
        storageKey: 'activity-history-{{ $application->id }}',
        init() {
            try {
                const sectionStored = localStorage.getItem(this.sectionStorageKey);
                if (sectionStored !== null) {
                    this.sectionOpen = JSON.parse(sectionStored);
                }
            } catch (e) {}
            try {
                const stored = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                if (Array.isArray(stored) && stored.length) {
                    this.openIndexes = stored;
                    return;
                }
            } catch (e) {}
            this.openIndexes = [0];
        },
        toggleSection() {
            this.sectionOpen = !this.sectionOpen;
            localStorage.setItem(this.sectionStorageKey, JSON.stringify(this.sectionOpen));
        },
        toggle(idx) {
            if (this.openIndexes.includes(idx)) {
                this.openIndexes = this.openIndexes.filter(i => i !== idx);
            } else {
                this.openIndexes = [...this.openIndexes, idx];
            }
            localStorage.setItem(this.storageKey, JSON.stringify(this.openIndexes));
        },
        isOpen(idx) {
            return this.openIndexes.includes(idx);
        }
    }" x-init="init()">
    <button type="button" @click="toggleSection()"
        class="w-full text-left flex items-center justify-between hover:opacity-70 transition">
        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-history text-teal-600"></i> Hiring Process Activity History
        </h3>
        <i class="fas fa-chevron-down text-gray-600 transition" :class="sectionOpen ? 'rotate-180' : ''"></i>
    </button>
    <div x-show="sectionOpen" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="mt-6">

        @if($activities->count() > 0)
        <div class="space-y-4">
            @foreach($activities as $activity)
            <div class="border-l-4 rounded" :class="{
                'border-orange-500 bg-orange-50': '{{ $activity->activity_type }}' === 'returned',
                'border-green-500 bg-green-50': '{{ $activity->activity_type }}' === 'completed',
                'border-blue-500 bg-blue-50': !['returned', 'completed'].includes('{{ $activity->activity_type }}'),
            }">
                <button type="button" class="w-full text-left px-4 py-3 flex items-start justify-between"
                    @click="toggle({{ $loop->index }})" :aria-expanded="isOpen({{ $loop->index }})">
                    <div>
                        <p class="font-semibold text-gray-900">
                            <i class="fas" :class="{
                                'fa-undo text-orange-600': '{{ $activity->activity_type }}' === 'returned',
                                'fa-check-circle text-green-600': '{{ $activity->activity_type }}' === 'completed',
                                'fa-eye text-blue-600': !['returned', 'completed'].includes('{{ $activity->activity_type }}'),
                            }"></i>
                            {{ $activity->description }}
                        </p>
                        @if($activity->form_type && $activity->activity_type === 'returned')
                        <p class="text-sm text-orange-700 font-medium mt-1">
                            <i class="fas fa-file-alt mr-1"></i>Form: {{ $formLabels[$activity->form_type] ??
                            ucfirst(str_replace('_', ' ', $activity->form_type)) }}
                        </p>
                        @endif
                        <p class="text-sm text-gray-600 mt-1">
                            By <strong>{{ $activity->performedBy?->name ?? 'System' }}</strong>
                            ({{ $activity->performedBy?->email ?? 'N/A' }})
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-clock"></i> {{ $activity->created_at->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="{
                            'bg-orange-200 text-orange-800': '{{ $activity->activity_type }}' === 'returned',
                            'bg-green-200 text-green-800': '{{ $activity->activity_type }}' === 'completed',
                            'bg-blue-200 text-blue-800': !['returned', 'completed'].includes('{{ $activity->activity_type }}'),
                        }">
                            {{ ucfirst($activity->activity_type) }}
                        </span>
                        <i class="fas fa-chevron-down text-gray-500 transition"
                            :class="isOpen({{ $loop->index }}) ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                <div x-show="isOpen({{ $loop->index }})" x-cloak x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="px-4 pb-4">

                    <div class="mt-2 p-3 bg-white rounded border-l-2" :class="{
                        'border-orange-300': '{{ $activity->activity_type }}' === 'returned',
                        'border-green-300': '{{ $activity->activity_type }}' === 'completed',
                        'border-blue-300': !['returned', 'completed'].includes('{{ $activity->activity_type }}'),
                    }">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-700">
                                <i class="fas fa-note-sticky"></i> Notes:
                            </p>
                            @if($loop->first)
                            <button type="button" onclick="document.getElementById('edit-recent-notes-btn')?.click();"
                                class="text-xs font-semibold text-amber-700 hover:text-amber-900 cursor-pointer">
                                <i class="fas fa-edit mr-1"></i>Edit Recent Notes
                            </button>
                            @endif
                        </div>
                        @if($activity->notes)
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $activity->notes }}</p>
                        @else
                        <p class="text-sm text-gray-500 italic">No notes were provided.</p>
                        @endif
                    </div>

                    @if($activity->status_from || $activity->status_to)
                    <div class="mt-2 text-xs text-gray-600">
                        Status changed: <strong>{{ ucfirst($activity->status_from ?? 'N/A') }}</strong>
                        →
                        <strong>{{ ucfirst($activity->status_to ?? 'N/A') }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-600">No activity history yet for this application.</p>
        </div>
        @endif
    </div>
</div>