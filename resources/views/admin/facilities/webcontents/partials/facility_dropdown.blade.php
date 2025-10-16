<div class="mb-8">
    <label for="facilitySelect" class="block text-sm font-semibold text-gray-700 mb-2">Select Facility</label>
    <div class="relative">
        <select id="facilitySelect" name="facility_id"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 bg-white appearance-none cursor-pointer">
            <option value="">Choose a facility...</option>
            @foreach($facilities as $facility)
            <option value="{{ $facility->id }}" data-name="{{ $facility->name }}" data-city="{{ $facility->city }}"
                data-state="{{ $facility->state }}" data-phone="{{ $facility->phone }}">
                {{ $facility->name }} ({{ $facility->city }}, {{ $facility->state }})
            </option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
</div>