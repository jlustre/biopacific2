@extends('layouts.dynamic')

@section('title', 'Preview: ' . $template->name)

@php
    // Make facility data available as $facility for the layout
    $facility = $facilityData;
@endphp

@push('head')
<style>
    /* Preview-specific styles */
    .preview-badge {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .preview-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<!-- Preview Badge -->
<div class="preview-badge">
    <i class="fas fa-eye mr-2"></i>
    Previewing: {{ $template->name }}
</div>

<!-- Preview Controls -->
<div class="preview-controls">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.layouts.show', $template->id) }}"
           class="text-sm text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-1"></i>
            Back to Details
        </a>
        <a href="{{ route('admin.layouts.edit', $template->id) }}"
           class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fas fa-edit mr-1"></i>
            Edit Template
        </a>
    </div>
</div>

<!-- Render Template Sections -->
@php
    // Create the mock facility object for services
    $mockFacilityObject = new class {
        public function getSetting($key, $default = null) {
            $settings = [
                'favicon' => null,
                'custom_css' => '',
                'logo' => null
            ];
            return $settings[$key] ?? $default;
        }
    };

    // Set up the mock facility as the current facility for preview
    app()->instance('current_facility', $mockFacilityObject);
@endphp

@foreach($template->sections ?? [] as $sectionSlug)
    @php
        $section = $sections->where('slug', $sectionSlug)->first();
        $config = $template->default_config[$sectionSlug] ?? [];
        $variant = $config['variant'] ?? 'default';

        // Build the component path
        $componentPath = "partials.{$sectionSlug}.{$variant}";

        // Fallback paths if the variant doesn't exist
        $fallbackPaths = [
            "partials.{$sectionSlug}.default",
            "partials.{$sectionSlug}",
        ];
    @endphp

    <section class="section-{{ $sectionSlug }}">
        @php
            $rendered = false;
            $viewPaths = array_merge([$componentPath], $fallbackPaths);
        @endphp

        @foreach($viewPaths as $viewPath)
            @if(!$rendered && View::exists($viewPath))
                @include($viewPath, [
                    'facility' => $facilityData,
                    'config' => $config,
                    'variant' => $variant
                ])
                @php $rendered = true; @endphp
                @break
            @endif
        @endforeach

        @if(!$rendered)
            <!-- Fallback content for missing sections -->
            <div class="py-16 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Section "{{ $sectionSlug }}" ({{ $variant }} variant) - View not found
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Expected paths: {{ implode(', ', $viewPaths) }}
                    </p>
                </div>
            </div>
        @endif
    </section>
@endforeach

@if(empty($template->sections))
    <!-- Empty template message -->
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="text-center">
            <i class="fas fa-th-large text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Empty Template</h2>
            <p class="text-gray-600 mb-6">This template doesn't have any sections configured yet.</p>
            <a href="{{ route('admin.layouts.edit', $template->id) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                Configure Sections
            </a>
        </div>
    </div>
@endif
@endsection
