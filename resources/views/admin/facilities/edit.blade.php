@extends('layouts.dashboard')

@section('content')

@php
$activeSections = $activeWebContent ? $activeWebContent->sections : [];
$availableSections = [
'topbar' => 'Topbar Section',
'hero' => 'Hero Section',
'about' => 'About Section',
'services' => 'Services Section',
'rooms' => 'Rooms Section',
'gallery' => 'Gallery Section',
'news' => 'News Section',
'testimonials' => 'Testimonials',
'careers' => 'Careers Section',
'book' => 'Book a Tour Section',
'contact' => 'Contact Section',
'faqs' => 'FAQs Section',
'resources' => 'Resources Section',
'footer' => 'Footer Section',
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

<style>
    :root {
        --color-primary: {
                {
                $facility->colorScheme->primary_color ?? '#007bff'
            }
        }

        ;

        --color-secondary: {
                {
                $facility->colorScheme->secondary_color ?? '#6c757d'
            }
        }

        ;

        --color-accent: {
                {
                $facility->colorScheme->accent_color ?? '#28a745'
            }
        }

        ;
    }

    .tab-pane {
        min-height: 400px;
    }
</style>
<div class="min-h-screen bg-gray-50">
    @include('components.back-link-header', [
    'title_hdr' => 'Edit Facility',
    'subtitle_hdr' => $facility->name,
    'preview' => true
    ])
    <!-- Success Message -->
    @if(session('success'))
    @include('components.success', ['message' => session('success')])
    @endif

    @if($errors->any())
    @include('components.errors', ['errors' => $errors->all()])
    @endif

    <!-- Main Form -->
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Color Scheme Select (always visible for form submission) -->
            <div class="mb-6">
                <label for="color_scheme_id" class="block text-sm font-medium text-gray-700 mb-2">Color
                    Scheme</label>
                <!-- Removed duplicate color_scheme_id select. Only keep the one in the Colors tab. -->

                <!-- Tab Navigation -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    @include('admin.facilities.edit-tabs.navigation', ['activeTab' => 'basic'])

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Basic Information Tab -->
                        @include('admin.facilities.edit-tabs.basic', ['facility' => $facility])
                    </div>

                    <!-- Contact Information Tab -->
                    @include('admin.facilities.edit-tabs.contact', ['facility' => $facility])

                    <!-- Content & Branding Tab -->
                    @include('admin.facilities.edit-tabs.content', ['facility' => $facility, 'activeSections' =>
                    $activeSections, 'availableSections' => $availableSections, 'sectionVariances' =>
                    $sectionVariances])

                    <!-- Colors Tab -->
                    @include('admin.facilities.edit-tabs.colors', ['facility' => $facility])

                    <!-- Services Tab -->
                    @include('admin.facilities.edit-tabs.services', ['facility' => $facility])

                    <!-- Social Media Tab -->
                    @include('admin.facilities.edit-tabs.social', ['facility' => $facility])

                    <!-- News Tab -->
                    {{-- @if (!request()->routeIs('admin.news.create'))
                    @include('admin.facilities.edit-tabs.news', ['facility' => $facility])
                    @endif --}}

                    <!-- Layout & Sections Tab -->
                    @include('admin.facilities.edit-tabs.sections', ['facility' => $facility, 'activeSections' =>
                    $activeSections, 'availableSections' => $availableSections, 'sectionVariances' =>
                    $sectionVariances])

                    <!-- Gallery Tab -->
                    {{-- @include('admin.facilities.edit-tabs.gallery', ['facility' => $facility]) --}}
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" id="save-button"
                    class="text-white px-8 py-3 rounded-lg transition-colors font-medium bg-teal-500 hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    style="border: 2px solid var(--color-accent); opacity: 1 !important; pointer-events: auto !important; display: block !important; visibility: visible !important; z-index: 9999 !important; position: relative !important; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">Save
                    Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- News Delete Forms: Place OUTSIDE the main facility form -->
@if(isset($facility) && $facility->news->count())
<div id="news-delete-forms" style="display:none;">
    @foreach($facility->news as $item)
    <form id="delete-news-form-{{ $item->id }}" action="{{ route('admin.news.destroy', $item) }}" method="POST">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
</div>
@endif

<script>
    function deleteNewsItem(newsId) {
        if (confirm('Delete this news item?')) {
            var form = document.getElementById('delete-news-form-' + newsId);
            if (form) {
                form.submit();
            }
        }
    }
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
        // Prepare webContents data for JS
        window.webContentsData = @json($webContents->mapWithKeys(function($wc) {
            return [$wc->layout_template => $wc->sections];
        }));

        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(function(pane) {
                pane.classList.add('hidden');
            });
            // Remove active classes from all tabs
            document.querySelectorAll('.tab-button').forEach(function(button) {
                button.classList.remove('border-primary', 'text-primary');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            // Show the selected tab pane
            document.getElementById(tabName + '-content').classList.remove('hidden');
            // Add active classes to the selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-primary', 'text-primary');
            activeTab.setAttribute('aria-current', 'page');
            // Persist tab selection
            localStorage.setItem('facilityEditActiveTab', tabName);
        }
    
        // Color picker sync
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            colorInput.addEventListener('change', function() {
                const textInput = this.parentNode.querySelector('input[type="text"]');
                textInput.value = this.value;
            });
        });
    
        // Debug form submission - wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence logic
            var lastTab = localStorage.getItem('facilityEditActiveTab') || 'basic';
            showTab(lastTab);
            // Target the main facility edit form specifically, not the logout form
            const form = document.querySelector('form[action*="admin/facilities"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // ...existing code...
                    return true;
                });
            }
        });
</script>

<style>
    .tab-pane {
        min-height: 400px;
    }
</style>

@endsection