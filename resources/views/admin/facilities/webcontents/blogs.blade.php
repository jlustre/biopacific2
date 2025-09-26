@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Blogs Management</h1>
                        <p class="text-gray-600">Manage blog posts for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Select a Facility</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($facilities as $facility)
                <div
                    class="bg-white border border-gray-300 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-primary text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $facility->name }}</h3>
                            <p class="text-sm text-gray-500 truncate">{{ $facility->city ?? 'N/A' }}, {{
                                $facility->state ?? 'N/A' }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i
                                class="fas fa-chevron-right text-gray-400 group-hover:text-primary transition-colors"></i>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Placeholder Content -->
        <div class="bg-white rounded-lg shadow p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 mb-4">
                    <i class="fas fa-blog text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Blogs Management</h3>
                <p class="text-gray-500 mb-6">Select a facility above to manage its blog content. This page will allow
                    you to:</p>
                <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Create and edit blog posts
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Schedule posts for publication
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Categorize and tag content
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Manage blog SEO settings
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection