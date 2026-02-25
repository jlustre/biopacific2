@props([
'application',
])

<div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-xl shadow-lg p-8 mb-8">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-3xl font-bold mb-2">{{ $application->first_name }} {{ $application->last_name }}</h1>
            <p class="text-teal-100 text-lg">{{ $application->position_applied_for ?? 'Position Not Specified' }}</p>
            <p class="text-teal-200 text-sm mt-2">
                <i class="fas fa-envelope mr-2"></i>{{ $application->email }}
                @if($application->phone_number)
                <span class="mx-2">•</span>
                <i class="fas fa-phone mr-2"></i>{{ $application->phone_number }}
                @endif
            </p>
        </div>
        <div class="bg-white/20 rounded-lg px-4 py-2 text-right">
            <div class="text-xs text-teal-100 uppercase font-semibold">Status</div>
            <div class="text-xl font-bold">{{ ucfirst($application->status ?? 'draft') }}</div>
            <div class="text-xs text-teal-200 mt-1">Submitted {{ $application->created_at->format('M d, Y') }}</div>
        </div>
    </div>
</div>