@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.facilities.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Facility</h1>
                        <p class="text-gray-600">{{ $facility->name }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('dashboard.facility', $facility->id) }}"
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                        Preview Site
                    </a>
                    <a href="{{ route('admin.facilities.layout-config', $facility->id) }}"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/80 transition-colors text-sm font-medium">
                        Configure Layout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Facility Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $facility->name) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            required>
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="tagline" class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                        <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $facility->tagline) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('tagline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                        <input type="text" id="domain" name="domain" value="{{ old('domain', $facility->domain) }}"
                            placeholder="example.com"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('domain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">Subdomain</label>
                        <input type="text" id="subdomain" name="subdomain"
                            value="{{ old('subdomain', $facility->subdomain) }}" placeholder="facility-name"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('subdomain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',
                                $facility->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-primary focus:border-primary focus:ring-primary">
                            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Facility is
                                active</label>
                        </div>
                        <div class="mb-4">
                            <label for="location_map" class="block text-sm font-medium text-gray-700 mb-2">Location
                                Map</label>
                            @if($facility->location_map)
                            @if(Str::startsWith($facility->location_map, ['http://', 'https://']))
                            <div class="mb-2">
                                <iframe src="{{ $facility->location_map }}" width="100%" height="200" style="border:0;"
                                    allowfullscreen loading="lazy"></iframe>
                            </div>
                            @else
                            <div class="mb-2">
                                {!! $facility->location_map !!}
                            </div>
                            @endif
                            @endif
                            <input type="text" id="location_map" name="location_map"
                                value="{{ old('location_map', $facility->location_map) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                placeholder="Paste Google Maps embed code or URL here">
                            @error('location_map')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $facility->address) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $facility->city) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                        <input type="text" id="state" name="state" value="{{ old('state', $facility->state) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $facility->phone) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $facility->email) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="beds" class="block text-sm font-medium text-gray-700 mb-2">Number of Beds</label>
                        <input type="number" id="beds" name="beds" value="{{ old('beds', $facility->beds) }}" min="1"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('beds')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="hours" class="block text-sm font-medium text-gray-700 mb-2">Operating Hours</label>
                        <input type="text" id="hours" name="hours" value="{{ old('hours', $facility->hours) }}"
                            placeholder="24/7 or specific hours"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('hours')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Content & Branding -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Content & Branding</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="headline" class="block text-sm font-medium text-gray-700 mb-2">Hero
                                Headline</label>
                            <input type="text" id="headline" name="headline"
                                value="{{ old('headline', $facility->headline) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('headline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="subheadline" class="block text-sm font-medium text-gray-700 mb-2">Hero
                                Subheadline</label>
                            <input type="text" id="subheadline" name="subheadline"
                                value="{{ old('subheadline', $facility->subheadline) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('subheadline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="about_text" class="block text-sm font-medium text-gray-700 mb-2">About Text</label>
                        <textarea id="about_text" name="about_text" rows="4"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('about_text', $facility->about_text) }}</textarea>
                        @error('about_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="logo_url" class="block text-sm font-medium text-gray-700 mb-2">Logo URL</label>
                            <input type="url" id="logo_url" name="logo_url"
                                value="{{ old('logo_url', $facility->logo_url) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('logo_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="hero_image_url" class="block text-sm font-medium text-gray-700 mb-2">Hero Image
                                URL</label>
                            <input type="url" id="hero_image_url" name="hero_image_url"
                                value="{{ old('hero_image_url', $facility->hero_image_url) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('hero_image_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="about_image_url" class="block text-sm font-medium text-gray-700 mb-2">About
                                Image URL</label>
                            <input type="url" id="about_image_url" name="about_image_url"
                                value="{{ old('about_image_url', $facility->about_image_url) }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            @error('about_image_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colors -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Brand Colors</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">Primary
                            Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="primary_color" name="primary_color"
                                value="{{ old('primary_color', $facility->primary_color ?? '#047857') }}"
                                class="h-10 w-16 rounded border border-gray-300">
                            <input type="text" value="{{ old('primary_color', $facility->primary_color ?? '#047857') }}"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                onchange="document.getElementById('primary_color').value = this.value">
                        </div>
                        @error('primary_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">Secondary
                            Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="secondary_color" name="secondary_color"
                                value="{{ old('secondary_color', $facility->secondary_color ?? '#1f2937') }}"
                                class="h-10 w-16 rounded border border-gray-300">
                            <input type="text"
                                value="{{ old('secondary_color', $facility->secondary_color ?? '#1f2937') }}"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                onchange="document.getElementById('secondary_color').value = this.value">
                        </div>
                        @error('secondary_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="accent_color" class="block text-sm font-medium text-gray-700 mb-2">Accent
                            Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="accent_color" name="accent_color"
                                value="{{ old('accent_color', $facility->accent_color ?? '#06b6d4') }}"
                                class="h-10 w-16 rounded border border-gray-300">
                            <input type="text" value="{{ old('accent_color', $facility->accent_color ?? '#06b6d4') }}"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                onchange="document.getElementById('accent_color').value = this.value">
                        </div>
                        @error('accent_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Social Media</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                        <input type="url" id="facebook" name="facebook"
                            value="{{ old('facebook', $facility->facebook) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('facebook')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter URL</label>
                        <input type="url" id="twitter" name="twitter" value="{{ old('twitter', $facility->twitter) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('twitter')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram
                            URL</label>
                        <input type="url" id="instagram" name="instagram"
                            value="{{ old('instagram', $facility->instagram) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('instagram')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Active Sections -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="bg-gray-50 px-6 py-2 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Active Sections</h3>
                        <p class="text-sm text-gray-600">Choose which sections to display on the website</p>
                    </div>
                    <div>
                        <label for="layout_template" class="block text-sm font-medium text-gray-700 mb-1">Layout
                            Template *</label>
                        <select id="layout_template" name="layout_template"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            required>
                            @foreach($layoutTemplates as $template)
                            <option value="{{ $template }}" {{ old('layout_template',
                                $selectedLayoutTemplate)===$template ? 'selected' : '' }}>
                                {{ ucfirst($template) }}
                            </option>
                            @endforeach
                        </select>
                        @error('layout_template')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="p-6">
                    @php
                    $activeSections = $activeWebContent ? $activeWebContent->sections : [];
                    $availableSections = [
                    'topbar' => 'Topbar Section',
                    'hero' => 'Hero Section',
                    'about' => 'About Section',
                    'services' => 'Services Section',
                    'rooms' => 'Rooms Section',
                    'gallery' => 'Gallery Section',
                    'news' => 'News & Events Section',
                    'testimonials' => 'Testimonials',
                    'careers' => 'Careers Section',
                    'book' => 'Book a Tour Section',
                    'contact' => 'Contact Section',
                    'faqs' => 'FAQs Section',
                    'resources' => 'Resources Section',
                    'footer' => 'Footer Section'
                    ];

                    $sectionVariances = [];

                    foreach ($availableSections as $key => $label) {
                    $dir = resource_path("views/partials/{$key}");
                    $sectionVariances[$key] = ['default'];
                    if (is_dir($dir)) {
                    foreach (glob($dir . '/*.blade.php') as $file) {
                    $name = basename($file, '.blade.php');
                    if (!in_array($name, $sectionVariances[$key])) {
                    $sectionVariances[$key][] = $name;
                    }
                    }
                    }

                    }
                    @endphp

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($availableSections as $key => $label)
                        @php
                        $isActive = in_array($key, $activeSections ?? []);
                        $selectedVariance = $activeWebContent->variances[$key] ?? 'default';
                        @endphp
                        <div class="flex flex-col">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="section_{{ $key }}" name="sections[{{ $key }}]" value="1" {{
                                    $isActive ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary focus:border-primary focus:ring-primary section-toggle"
                                    data-section="{{ $key }}">
                                <label for="section_{{ $key }}" class="ml-2 text-sm font-medium text-gray-700">
                                    {{ $label }}
                                </label>
                            </div>
                            <select name="variances[{{ $key }}]" id="variance_{{ $key }}"
                                class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary" {{
                                $isActive ? '' : 'disabled' }}>
                                @foreach($sectionVariances[$key] ?? ['default'] as $variance)
                                <option value="{{ $variance }}" {{ $selectedVariance===$variance ? 'selected' : '' }}>
                                    {{ ucfirst($variance) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                    <script>
                        document.querySelectorAll('.section-toggle').forEach(function(checkbox) {
                            checkbox.addEventListener('change', function() {
                                var section = this.getAttribute('data-section');
                                var select = document.getElementById('variance_' + section);
                                if (this.checked) {
                                    select.removeAttribute('disabled');
                                } else {
                                    select.setAttribute('disabled', 'disabled');
                                }
                            });
                        });

                        const templateDefaults = {
    'default-template': { hero: 'default', about: 'default', services: 'grid' },
    'layout2': { hero: 'video', about: 'stats', services: 'cards' },
    // ...other templates
};

document.getElementById('layout_template').addEventListener('change', function() {
    const selected = this.value;
    const defaults = templateDefaults[selected] || {};
    Object.keys(defaults).forEach(section => {
        const select = document.querySelector(`select[name='variances[${section}]']`);
        if (select) {
            // If the default is not in the options, add it
            let found = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === defaults[section]) {
                    found = true;
                    break;
                }
            }
            if (!found && defaults[section]) {
                const opt = document.createElement('option');
                opt.value = defaults[section];
                opt.text = defaults[section].charAt(0).toUpperCase() + defaults[section].slice(1);
                select.appendChild(opt);
            }
            select.value = defaults[section];
        }
    });
});
                    </script>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary/80 transition-colors font-medium">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Prepare webContents data for JS
    window.webContentsData = @json($webContents->mapWithKeys(function($wc) {
        return [$wc->layout_template => $wc->sections];
    }));
</script>

<script>
    // Color picker sync
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('change', function() {
        const textInput = this.parentNode.querySelector('input[type="text"]');
        textInput.value = this.value;
    });
});
</script>
@endsection