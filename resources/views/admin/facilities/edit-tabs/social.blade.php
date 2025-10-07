<div id="social-content" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Social Media</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">Facebook
                    URL</label>
                <input type="url" id="facebook" name="facebook" value="{{ old('facebook', $facility->facebook) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('facebook')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter
                    URL</label>
                <input type="url" id="twitter" name="twitter" value="{{ old('twitter', $facility->twitter) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('twitter')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram
                    URL</label>
                <input type="url" id="instagram" name="instagram" value="{{ old('instagram', $facility->instagram) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @error('instagram')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>