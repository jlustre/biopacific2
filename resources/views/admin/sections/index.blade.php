@extends('layouts.dashboard')

@section('title', 'Layout Sections')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Layout Sections</h1>
        <p class="text-gray-600 mt-1">Manage individual sections like hero, about, services, contact, and more</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.layouts.index') }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-th-large"></i>
            Layout Templates
        </a>
        <a href="{{ route('admin.sections.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Create Section
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

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($sections as $section)
        <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
            {{-- Section Header --}}
            <div class="p-4 border-b">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $section->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $section->slug }}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        @if($section->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="fas fa-pause-circle mr-1"></i>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Section Info --}}
            <div class="p-4">
                @if($section->description)
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($section->description, 100) }}</p>
                @endif

                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-code w-4 mr-2"></i>
                        <span class="font-mono text-xs">{{ $section->component_path }}</span>
                    </div>

                    @if($section->variants && count($section->variants) > 0)
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-palette w-4 mr-2"></i>
                            <span>{{ count($section->variants) }} variant(s)</span>
                        </div>
                    @endif

                    {{-- Templates using this section --}}
                    @php
                        $templatesUsingSection = \App\Models\LayoutTemplate::whereJsonContains('sections', $section->slug)->count();
                    @endphp
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-th-large w-4 mr-2"></i>
                        <span>Used in {{ $templatesUsingSection }} template(s)</span>
                    </div>
                </div>

                {{-- Variants Preview --}}
                @if($section->variants && count($section->variants) > 0)
                    <div class="mt-3 pt-3 border-t">
                        <p class="text-xs font-medium text-gray-700 mb-2">Available Variants:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($section->variants as $variant)
                                @php
                                    $variantName = is_array($variant) ? ($variant['name'] ?? 'default') : (is_object($variant) ? ($variant->name ?? 'default') : $variant);
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700 border">
                                    {{ ucfirst($variantName) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="px-4 py-3 bg-gray-50 border-t rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.sections.show', $section->id) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Details
                        </a>
                    </div>
                    <div class="flex items-center gap-1">
                        {{-- Preview dropdown for variants --}}
                        @if($section->variants && count($section->variants) > 0)
                            <div class="relative group">
                                <button class="text-gray-600 hover:text-gray-800 p-1 rounded" title="Preview Variants">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <div class="absolute right-0 bottom-full mb-1 hidden group-hover:block bg-white border rounded-lg shadow-lg py-1 whitespace-nowrap z-10">
                                    @foreach($section->variants as $variant)
                                        @php
                                            $variantName = is_array($variant) ? ($variant['name'] ?? 'default') : (is_object($variant) ? ($variant->name ?? 'default') : $variant);
                                        @endphp
                                        <a href="{{ route('admin.sections.preview', [$section->id, $variantName]) }}"
                                           class="block px-3 py-1 text-xs text-gray-700 hover:bg-gray-100"
                                           target="_blank">
                                            Preview {{ ucfirst($variantName) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route('admin.sections.preview', $section->id) }}"
                               class="text-gray-600 hover:text-gray-800 p-1 rounded"
                               title="Preview Section"
                               target="_blank">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                        @endif

                        <a href="{{ route('admin.sections.edit', $section->id) }}"
                           class="text-gray-600 hover:text-gray-800 p-1 rounded"
                           title="Edit Section">
                            <i class="fas fa-edit text-sm"></i>
                        </a>

                        <form action="{{ route('admin.sections.duplicate', $section->id) }}"
                              method="POST"
                              class="inline">
                            @csrf
                            <button type="submit"
                                    class="text-gray-600 hover:text-gray-800 p-1 rounded"
                                    title="Duplicate Section">
                                <i class="fas fa-copy text-sm"></i>
                            </button>
                        </form>

                        @if($templatesUsingSection == 0)
                            <form action="{{ route('admin.sections.destroy', $section->id) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this section?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 p-1 rounded"
                                        title="Delete Section">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 p-1 rounded" title="Cannot delete - section is in use">
                                <i class="fas fa-trash text-sm"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <div class="col-span-full">
            <div class="text-center py-12">
                <i class="fas fa-puzzle-piece text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No sections found</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first layout section</p>
                <a href="{{ route('admin.sections.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Create First Section
                </a>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($sections->hasPages())
    <div class="mt-8">
        {{ $sections->links() }}
    </div>
@endif

{{-- Quick Stats --}}
<div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-puzzle-piece text-blue-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-600">Total Sections</p>
                <p class="text-lg font-semibold text-gray-900">{{ $sections->total() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-600">Active Sections</p>
                <p class="text-lg font-semibold text-gray-900">{{ $sections->where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-palette text-yellow-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-600">With Variants</p>
                <p class="text-lg font-semibold text-gray-900">{{ $sections->filter(function($s) { return !empty($s->variants); })->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-th-large text-purple-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-600">Total Templates</p>
                <p class="text-lg font-semibold text-gray-900">{{ \App\Models\LayoutTemplate::count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
