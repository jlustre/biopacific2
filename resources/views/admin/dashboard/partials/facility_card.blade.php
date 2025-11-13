{{-- Import Str helper for Blade --}}
@inject('strHelper', 'Illuminate\\Support\\Str')
<div
    class="relative bg-teal-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-6 px-3 shadow-sm hover:shadow-lg transition group overflow-hidden">
    <!-- Facility ID badge (top left) -->
    <div class="absolute top-2 left-2 z-10">
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-200 text-yellow-900 shadow">
            ID: {{ $facility->id }}
        </span>
    </div>
    <!-- Active/Inactive badge (top right, closer to edge) -->
    <div class="absolute top-2 right-2 z-10">
        @if($facility->is_active)
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-200 shadow">
            <flux:icon name="check-circle" class="w-4 h-4 mr-1" /> Active
        </span>
        @else
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 shadow">
            <flux:icon name="x-circle" class="w-4 h-4 mr-1" /> Inactive
        </span>
        @endif
    </div>
    <div class="flex items-center gap-2 my-2">
        <div class="flex-1 min-w-0">
            <h4 class="text-md font-bold text-gray-900 dark:text-white truncate">{{ $facility->name }}</h4>
            @if($facility->tagline)
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate text-center">
                {{ \Illuminate\Support\Str::limit($facility->tagline, 50) }}
            </p>
            @endif
        </div>

    </div>
    @if($facility->headline)
    <div class="flex items-center gap-2 my-2">
        <div class="flex-1 min-w-0">
            <h4 class="text-center text-xs font-semibold text-slate-600 dark:text-white truncate">Headline</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate text-center">
                {{ \Illuminate\Support\Str::limit($facility->headline, 50) }}
            </p>
        </div>
    </div>
    @endif
    @if($facility->subheadline)
    <div class="flex items-center gap-2 my-2">
        <div class="flex-1 min-w-0">
            <h4 class="text-center text-xs font-semibold text-slate-600 dark:text-white truncate">Sub-Headline</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate text-center">
                {{ \Illuminate\Support\Str::limit($facility->subheadline, 50) }}
            </p>
        </div>
    </div>
    @endif
    <div class="flex flex-wrap gap-2 mb-2 text-center">
        @if($facility->beds)
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            <flux:icon name="user-group" class="w-4 h-4 mr-1" /> {{ $facility->beds }}
            beds
        </span>
        @endif

        @if($facility->city || $facility->state)
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            <flux:icon name="map-pin" class="w-4 h-4 mr-1" />
            {{ $facility->city }}@if($facility->city && $facility->state), @endif{{ $facility->state }}
        </span>
        @endif

        @if($facility->region)
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
            <flux:icon name="globe-alt" class="w-4 h-4 mr-1" /> Region: {{ strtoupper($facility->region) }}
        </span>
        @endif

        @if($facility->facility_number)
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
            Facility #: {{ $facility->facility_number }}
        </span>
        @endif
    </div>
    <div class="mt-4">
        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Legal Name:</strong> {{ $facility->legal_name ??
            'N/A' }}</p>
        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Administrator:</strong> {{ $facility->administrator
            ?? 'N/A' }}</p>
        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>DON:</strong> {{ $facility->don ?? 'N/A' }}</p>
        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>DSD:</strong> {{ $facility->dsd ?? 'N/A' }}</p>
        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Staffer:</strong> {{ $facility->staffer ?? 'N/A' }}
        </p>
    </div>
    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
        <div class="flex space-x-2">
            <flux:button size="xs" href="{{ route('admin.facilities.edit', $facility) }}" icon="pencil-square"
                variant="ghost">
                Edit
            </flux:button>
            <flux:button size="xs" href="{{ route('facility.public', $facility) }}" icon="eye" variant="ghost">
                View
            </flux:button>
        </div>
        @if($facility->domain)
        <flux:button size="xs" href="http://{{ $facility->domain }}" target="_blank" icon="arrow-top-right-on-square"
            variant="ghost">
            Visit
        </flux:button>
        @endif
    </div>
</div>