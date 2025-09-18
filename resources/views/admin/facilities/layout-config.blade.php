@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Layout Configuration</h1>
                        <p class="text-gray-600">{{ $facility->name }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                        Edit Details
                    </a>
                    <a href="{{ route('dashboard.facility', $facility->id) }}"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                        Preview Site
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
        <form action="{{ route('admin.facilities.update-layout-config', $facility->id) }}" method="POST"
            class="space-y-8">
            @csrf
            @method('PUT')

            @php
            $layoutConfig = $facility->layout_config ?? [];
            @endphp

            <!-- Hero Section Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Hero Section
                    </h3>
                    <p class="text-sm text-gray-600">Configure the main banner/hero area</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Hero Variant</label>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="hero_variant" value="default" {{
                                        ($layoutConfig['hero_variant'] ?? 'default' )==='default' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Default - Full-width background</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="hero_variant" value="video" {{
                                        ($layoutConfig['hero_variant'] ?? 'default' )==='video' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Video - Background video</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="hero_variant" value="split" {{
                                        ($layoutConfig['hero_variant'] ?? 'default' )==='split' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Split - Text and image side by side</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Configuration</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Text Alignment</label>
                                    <select name="hero_config[text_alignment]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="left" {{ ($layoutConfig['hero_config']['text_alignment']
                                            ?? 'left' )==='left' ? 'selected' : '' }}>Left</option>
                                        <option value="center" {{ ($layoutConfig['hero_config']['text_alignment']
                                            ?? 'left' )==='center' ? 'selected' : '' }}>Center</option>
                                        <option value="right" {{ ($layoutConfig['hero_config']['text_alignment']
                                            ?? 'left' )==='right' ? 'selected' : '' }}>Right</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Overlay Opacity</label>
                                    <select name="hero_config[overlay_opacity]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="0.3" {{ ($layoutConfig['hero_config']['overlay_opacity'] ?? '0.3'
                                            )==='0.3' ? 'selected' : '' }}>30%</option>
                                        <option value="0.5" {{ ($layoutConfig['hero_config']['overlay_opacity'] ?? '0.3'
                                            )==='0.5' ? 'selected' : '' }}>50%</option>
                                        <option value="0.7" {{ ($layoutConfig['hero_config']['overlay_opacity'] ?? '0.3'
                                            )==='0.7' ? 'selected' : '' }}>70%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Section Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        About Section
                    </h3>
                    <p class="text-sm text-gray-600">Configure the about/information section</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">About Variant</label>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="about_variant" value="default" {{
                                        ($layoutConfig['about_variant'] ?? 'default' )==='default' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Default - Text and image</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="about_variant" value="stats" {{
                                        ($layoutConfig['about_variant'] ?? 'default' )==='stats' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Stats - With statistics cards</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="about_variant" value="timeline" {{
                                        ($layoutConfig['about_variant'] ?? 'default' )==='timeline' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Timeline - With timeline elements</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">About Configuration</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Layout Style</label>
                                    <select name="about_config[layout]" class="w-full rounded border-gray-300 text-sm">
                                        <option value="image-left" {{ ($layoutConfig['about_config']['layout']
                                            ?? 'image-left' )==='image-left' ? 'selected' : '' }}>Image Left</option>
                                        <option value="image-right" {{ ($layoutConfig['about_config']['layout']
                                            ?? 'image-left' )==='image-right' ? 'selected' : '' }}>Image Right</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Show Stats</label>
                                    <select name="about_config[show_stats]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="true" {{ ($layoutConfig['about_config']['show_stats'] ?? 'true'
                                            )==='true' ? 'selected' : '' }}>Yes</option>
                                        <option value="false" {{ ($layoutConfig['about_config']['show_stats'] ?? 'true'
                                            )==='false' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Section Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Services Section
                    </h3>
                    <p class="text-sm text-gray-600">Configure how services are displayed</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Services Variant</label>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="services_variant" value="grid" {{
                                        ($layoutConfig['services_variant'] ?? 'grid' )==='grid' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Grid - Card grid layout</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="services_variant" value="cards" {{
                                        ($layoutConfig['services_variant'] ?? 'grid' )==='cards' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Cards - Featured cards</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="services_variant" value="tabs" {{
                                        ($layoutConfig['services_variant'] ?? 'grid' )==='tabs' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Tabs - Tabbed interface</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Services Configuration</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Items per Row</label>
                                    <select name="services_config[columns]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="2" {{ ($layoutConfig['services_config']['columns'] ?? '3' )==='2'
                                            ? 'selected' : '' }}>2 Columns</option>
                                        <option value="3" {{ ($layoutConfig['services_config']['columns'] ?? '3' )==='3'
                                            ? 'selected' : '' }}>3 Columns</option>
                                        <option value="4" {{ ($layoutConfig['services_config']['columns'] ?? '3' )==='4'
                                            ? 'selected' : '' }}>4 Columns</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Show Icons</label>
                                    <select name="services_config[show_icons]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="true" {{ ($layoutConfig['services_config']['show_icons']
                                            ?? 'true' )==='true' ? 'selected' : '' }}>Yes</option>
                                        <option value="false" {{ ($layoutConfig['services_config']['show_icons']
                                            ?? 'true' )==='false' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Contact Section
                    </h3>
                    <p class="text-sm text-gray-600">Configure the contact section layout</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Contact Variant</label>

                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="contact_variant" value="form" {{
                                        ($layoutConfig['contact_variant'] ?? 'form' )==='form' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Form - Contact form focus</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="contact_variant" value="info" {{
                                        ($layoutConfig['contact_variant'] ?? 'form' )==='info' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Info - Contact details focus</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="contact_variant" value="map" {{
                                        ($layoutConfig['contact_variant'] ?? 'form' )==='map' ? 'checked' : '' }}
                                        class="border-gray-300 text-primary focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm">Map - Map focus</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Configuration</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Show Map</label>
                                    <select name="contact_config[show_map]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="true" {{ ($layoutConfig['contact_config']['show_map'] ?? 'true'
                                            )==='true' ? 'selected' : '' }}>Yes</option>
                                        <option value="false" {{ ($layoutConfig['contact_config']['show_map'] ?? 'true'
                                            )==='false' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Show Contact Form</label>
                                    <select name="contact_config[show_form]"
                                        class="w-full rounded border-gray-300 text-sm">
                                        <option value="true" {{ ($layoutConfig['contact_config']['show_form'] ?? 'true'
                                            )==='true' ? 'selected' : '' }}>Yes</option>
                                        <option value="false" {{ ($layoutConfig['contact_config']['show_form'] ?? 'true'
                                            )==='false' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary/90 transition-colors font-medium">
                    Save Layout Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection