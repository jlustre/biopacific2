<div id="contact-content" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Contact Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-bold text-gray-700 mb-2">Address</label>
                <input type="text" id="address" name="address" value="{{ old('address', $facility->address) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="city" class="block text-sm font-bold text-gray-700 mb-2">City</label>
                <input type="text" id="city" name="city" value="{{ old('city', $facility->city) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="state" class="block text-sm font-bold text-gray-700 mb-2">State</label>
                <input type="text" id="state" name="state" value="{{ old('state', $facility->state) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $facility->phone) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $facility->email) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="beds" class="block text-sm font-bold text-gray-700 mb-2">Number of Beds</label>
                <input type="number" id="beds" name="beds" value="{{ old('beds', $facility->beds ?? 0) }}" min="1"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                    @if(empty($facility->rooms) || !$facility->rooms) disabled min="0" @endif>
                @error('beds')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="hours" class="block text-sm font-bold text-gray-700 mb-2">Recommended Visiting
                    Hours</label>
                <input type="text" id="hours" name="hours" value="{{ old('hours', $facility->hours) }}"
                    placeholder="24/7 or specific hours"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('hours')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>