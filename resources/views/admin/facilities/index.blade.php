@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Facility Management</h1>
                        <p class="text-gray-600">Edit and configure your facilities</p>
                    </div>
                </div>
                <div class="bg-primary/10 px-4 py-2 rounded-lg">
                    <span class="text-primary font-semibold">{{ $facilities->total() }} Total Facilities</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full px-2 sm:px-4 lg:px-8 py-8">

        <!-- Facilities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
            <div
                class="bg-white rounded-2xl border border-gray-300 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col h-full relative">

                <!-- Active/Inactive Badge -->
                <div class="absolute top-4 right-4 z-10">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $facility->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} shadow">
                        {{ $facility->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <!-- Facility Header -->
                <div class="p-6 border-b border-gray-200">

                    <div class="flex flex-col gap-2">
                        <div class="flex justify-center mb-2">
                            <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-10 w-10 object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-1">{{ $facility->name }}</h3>
                        <p class="text-sm text-gray-600 text-center mb-1">{{ $facility->tagline ?? 'Quality healthcare
                            services' }}
                        </p>
                        @if($facility->address)
                        <div class="flex flex-col items-center text-center mb-2">
                            <span class="text-sm text-gray-500">{{ $facility->address }}</span>
                            @if($facility->city || $facility->state || $facility->zip)
                            <span class="text-sm text-gray-400 mt-1">
                                {{ $facility->city ?? '' }}{{ $facility->city && ($facility->state || $facility->zip) ?
                                ', ' : '' }}{{ $facility->state ?? '' }}{{ $facility->state && $facility->zip ? ' ' : ''
                                }}{{ $facility->zip ?? '' }}
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-center items-center mt-4">
                        <span class="text-xl font-bold text-primary tracking-wide">
                            {{ $facility->phone ? '(' . substr($facility->phone,0,3) . ') ' .
                            substr($facility->phone,3,3) . '-' . substr($facility->phone,6,4) : 'N/A' }}
                        </span>
                    </div>
                    <!-- Facility Details -->
                    <div class="p-6 space-y-3 flex flex-col gap-2">
                        <div class="flex items-center gap-4 mt-2">
                            @if($facility->layout_template)
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                                <span class="text-gray-600">{{ ucfirst($facility->layout_template) }}</span>
                            </div>
                            @endif
                            @if($facility->beds)
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                <span class="text-gray-600">{{ $facility->beds }} beds</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Facility Location Map -->
                    @if($facility->location_map)
                    <div class="p-6 pt-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location Map</label>
                        @if(\Illuminate\Support\Str::startsWith($facility->location_map, ['http://', 'https://']))
                        <iframe src="{{ $facility->location_map }}" width="100%" height="200" style="border:0;"
                            allowfullscreen loading="lazy"></iframe>
                        @else
                        {!! $facility->location_map !!}
                        @endif
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="p-6 pt-0 mt-auto space-y-3">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                                class="flex-1 bg-primary text-white text-center py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">Edit
                                Details</a>
                            <a href="{{ route('admin.facilities.layout-config', $facility->id) }}"
                                class="flex-1 bg-purple-600 text-white text-center py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">Configure
                                Layout</a>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.facility', $facility->id) }}"
                                class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">Preview
                                Site</a>
                            @if($facility->domain)
                            <a href="http://{{ $facility->domain }}" target="_blank"
                                class="flex-1 bg-green-100 text-green-700 text-center py-2 px-4 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">Visit
                                Live</a>
                            @endif
                        </div>
                    </div>
                    @if($facility->domain)
                    <div class="flex flex-wrap gap-2 items-center mt-2">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                            </svg>
                            {{ $facility->domain }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            @if($facilities->hasPages())
            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <div class="mt-8">
                    {{ $facilities->links() }}
                </div>
            </div>
            @endif

            <!-- Empty State -->
            @if($facilities->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No facilities found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by seeding some facilities.</p>
            </div>
            @endif
        </div>
        @endsection