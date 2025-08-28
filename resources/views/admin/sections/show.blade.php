@extends('layouts.dashboard')

@section('title', $section->name . ' - Section Details')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ $section->name }}</h1>
            @if($section->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Active
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                    <i class="fas fa-pause-circle mr-1"></i>
                    Inactive
                </span>
            @endif
        </div>
        <p class="text-gray-600">Section slug: <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $section->slug }}</code></p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sections.index') }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Back to Sections
        </a>
        <a href="{{ route('admin.sections.edit', $section->id) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-edit"></i>
            Edit Section
        </a>
    </div>
</div>
@endsection

@section('content')
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Information --}}
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <p class="mt-1 text-gray-900">{{ $section->name }}</p>
                </div>

                @if($section->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-gray-900">{{ $section->description }}</p>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Component Path</label>
                    <p class="mt-1 font-mono text-sm bg-gray-50 px-3 py-2 rounded border">{{ $section->component_path }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created</label>
                        <p class="mt-1 text-gray-900">{{ $section->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="mt-1 text-gray-900">{{ $section->updated_at->format('M j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Variants --}}
        @if($section->variants && count($section->variants) > 0)
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Available Variants</h2>
                    <p class="text-sm text-gray-600 mt-1">Different visual styles for this section</p>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($section->variants as $variant)
                            @php
                                $variantName = is_array($variant) ? ($variant['name'] ?? 'default') : (is_object($variant) ? ($variant->name ?? 'default') : $variant);
                                $variantDesc = is_array($variant) ? ($variant['description'] ?? '') : (is_object($variant) ? ($variant->description ?? '') : '');
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 capitalize">{{ $variantName }}</h3>
                                        @if($variantDesc)
                                            <p class="text-sm text-gray-600 mt-1">{{ $variantDesc }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-2 font-mono">
                                            partials.{{ $section->slug }}.{{ $variantName }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        <a href="{{ route('admin.sections.preview', [$section->id, $variantName]) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm"
                                           target="_blank"
                                           title="Preview this variant">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Configuration Schema --}}
        @if($section->config_schema && count($section->config_schema) > 0)
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Configuration Schema</h2>
                    <p class="text-sm text-gray-600 mt-1">Available configuration options for this section</p>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-sm text-gray-800">{{ json_encode($section->config_schema, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Quick Actions --}}
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
            </div>
            <div class="px-6 py-4 space-y-3">
                <a href="{{ route('admin.sections.edit', $section->id) }}"
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 justify-center">
                    <i class="fas fa-edit"></i>
                    Edit Section
                </a>

                @if($section->variants && count($section->variants) > 0)
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Preview Variants:</label>
                        @foreach($section->variants as $variant)
                            @php
                                $variantName = is_array($variant) ? ($variant['name'] ?? 'default') : (is_object($variant) ? ($variant->name ?? 'default') : $variant);
                            @endphp
                            <a href="{{ route('admin.sections.preview', [$section->id, $variantName]) }}"
                               class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2 justify-center text-sm"
                               target="_blank">
                                <i class="fas fa-eye"></i>
                                Preview {{ ucfirst($variantName) }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ route('admin.sections.preview', $section->id) }}"
                       class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2 justify-center"
                       target="_blank">
                        <i class="fas fa-eye"></i>
                        Preview Section
                    </a>
                @endif

                <form action="{{ route('admin.sections.duplicate', $section->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-4 py-2 rounded-lg flex items-center gap-2 justify-center">
                        <i class="fas fa-copy"></i>
                        Duplicate Section
                    </button>
                </form>

                @php
                    $templatesUsingSection = \App\Models\LayoutTemplate::whereJsonContains('sections', $section->slug)->count();
                @endphp

                @if($templatesUsingSection == 0)
                    <form action="{{ route('admin.sections.destroy', $section->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this section? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full bg-red-100 hover:bg-red-200 text-red-800 px-4 py-2 rounded-lg flex items-center gap-2 justify-center">
                            <i class="fas fa-trash"></i>
                            Delete Section
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Usage Statistics --}}
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Usage Statistics</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Templates using this section</span>
                    <span class="font-semibold text-gray-900">{{ $templatesUsingSection }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Available variants</span>
                    <span class="font-semibold text-gray-900">{{ count($section->variants ?? []) }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Configuration options</span>
                    <span class="font-semibold text-gray-900">{{ count($section->config_schema ?? []) }}</span>
                </div>

                @if($templatesUsingSection > 0)
                    <div class="pt-3 border-t">
                        <p class="text-sm font-medium text-gray-700 mb-2">Used in templates:</p>
                        @php
                            $templates = \App\Models\LayoutTemplate::whereJsonContains('sections', $section->slug)->get();
                        @endphp
                        <div class="space-y-1">
                            @foreach($templates as $template)
                                <a href="{{ route('admin.layouts.show', $template->id) }}"
                                   class="block text-sm text-blue-600 hover:text-blue-800">
                                    {{ $template->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- File Information --}}
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">File Information</h2>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Component Path</label>
                    <p class="mt-1 text-sm font-mono bg-gray-50 px-3 py-2 rounded border break-all">
                        resources/views/{{ str_replace('.', '/', $section->component_path) }}.blade.php
                    </p>
                </div>

                @if($section->variants && count($section->variants) > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Variant Files</label>
                        <div class="mt-1 space-y-1">
                            @foreach($section->variants as $variant)
                                @php
                                    $variantName = is_array($variant) ? ($variant['name'] ?? 'default') : (is_object($variant) ? ($variant->name ?? 'default') : $variant);
                                @endphp
                                <p class="text-sm font-mono bg-gray-50 px-3 py-2 rounded border break-all">
                                    resources/views/partials/{{ $section->slug }}/{{ $variantName }}.blade.php
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
