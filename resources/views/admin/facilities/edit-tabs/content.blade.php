<div id="content-content" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Content & Branding</h3>
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="headline" class="block text-sm font-bold text-gray-700 mb-2">Hero
                        Headline</label>
                    <input type="text" id="headline" name="headline" value="{{ old('headline', $facility->headline) }}"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                    @error('headline')<p class="mt-1 text-sm text-red-600">{{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="subheadline" class="block text-sm font-bold text-gray-700 mb-2">Hero
                        Subheadline</label>
                    <input type="text" id="subheadline" name="subheadline"
                        value="{{ old('subheadline', $facility->subheadline) }}"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                    @error('subheadline')<p class="mt-1 text-sm text-red-600">{{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="about_text" class="block text-sm font-bold text-gray-700 mb-2">About
                    Text</label>
                <textarea id="about_text" name="about_text" rows="4"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">{{ old('about_text', $facility->about_text) }}</textarea>
                @error('about_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="logo_url" class="block text-sm font-bold text-gray-700 mb-2">Logo
                        URL</label>
                    <input type="text" id="logo_url" name="logo_url" value="{{ old('logo_url', $facility->logo_url) }}"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                    @error('logo_url')<p class="mt-1 text-sm text-red-600">{{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="hero_image_url" class="block text-sm font-bold text-gray-700 mb-2">Hero
                        Image</label>
                    <select id="hero_image_url" name="hero_image_url"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Select a hero image...</option>
                        @php
                        // Get all hero images dynamically from the images directory
                        $heroImages = [];
                        $imagesPath = public_path('images');

                        if (is_dir($imagesPath)) {
                        $files = scandir($imagesPath);
                        foreach ($files as $file) {
                        if (str_starts_with($file, 'hero') && (str_ends_with($file, '.png')
                        ||
                        str_ends_with($file, '.jpg') || str_ends_with($file, '.jpeg') ||
                        str_ends_with($file, '.webp'))) {
                        // Extract number/name for sorting
                        $name = pathinfo($file, PATHINFO_FILENAME);
                        $heroImages[] = [
                        'filename' => $file,
                        'name' => $name,
                        'sort_key' => $name,
                        ];
                        }
                        }
                        }

                        // Sort hero images naturally (hero1, hero2, hero10, hero11, etc.)
                        usort($heroImages, function ($a, $b) {
                        return strnatcmp($a['sort_key'], $b['sort_key']);
                        });
                        @endphp

                        @foreach($heroImages as $image)
                        <option value="{{ $image['filename'] }}" {{ old('hero_image_url', $facility->
                            hero_image_url) == $image['filename'] ? 'selected' : '' }}>
                            {{ ucwords(str_replace('-', ' ', $image['name'])) }}
                        </option>
                        @endforeach
                    </select>
                    @error('hero_image_url')<p class="mt-1 text-sm text-red-600">{{ $message
                        }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hero_video_id" class="block text-sm font-bold text-gray-700 mb-2">YouTube Video
                        ID</label>
                    <input type="text" id="hero_video_id" name="hero_video_id"
                        value="{{ old('hero_video_id', $facility->hero_video_id) }}"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                        placeholder="e.g. dQw4w9WgXcQ">
                    @error('hero_video_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Enter the YouTube video ID (the part after
                        v= in the URL).</p>
                </div>

                <div>
                    <label for="about_image_url" class="block text-sm font-medium text-gray-700 mb-2">About
                        Image URL</label>
                    <input type="text" id="about_image_url" name="about_image_url"
                        value="{{ old('about_image_url', $facility->about_image_url) }}"
                        class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                    @error('about_image_url')<p class="mt-1 text-sm text-red-600">{{
                        $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>