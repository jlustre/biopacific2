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
<<<<<<< HEAD
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
=======
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Facilities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
<<<<<<< HEAD
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-200 overflow-hidden">
=======
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-200 overflow-hidden">
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

                <!-- Facility Header -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $facility->name }}</h3>
<<<<<<< HEAD
                            <p class="text-sm text-gray-600 mb-2">{{ $facility->tagline ?? 'Quality healthcare services'
                                }}</p>

                            @if($facility->address)
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ $facility->address }}
                                @if($facility->city), {{ $facility->city }}@endif
                                @if($facility->state), {{ $facility->state }}@endif
                                @if($facility->zip), {{ $facility->zip }}@endif
                            </div>
                            @endif

                            @if($facility->subdomain)
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12v4m0 0v4m0-4h4m-4 0h-4" />
                                </svg>
                                Subdomain: {{ $facility->subdomain }}
=======
                            <p class="text-sm text-gray-600 mb-2">{{ $facility->tagline ?? 'Quality healthcare services' }}</p>

                            @if($facility->city && $facility->state)
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $facility->city }}, {{ $facility->state }}
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                            </div>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                               {{ $facility->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $facility->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <!-- Facility Details -->
                <div class="p-6 space-y-3">
                    @if($facility->domain)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
=======
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                        </svg>
                        <span class="text-gray-600">{{ $facility->domain }}</span>
                    </div>
                    @endif

                    @if($facility->layout_template)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
=======
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                        </svg>
                        <span class="text-gray-600">{{ ucfirst($facility->layout_template) }}</span>
                    </div>
                    @endif

                    @if($facility->beds)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
=======
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                        </svg>
                        <span class="text-gray-600">{{ $facility->beds }} beds</span>
                    </div>
                    @endif
                </div>

<<<<<<< HEAD
                <!-- Facility Location Map -->
                @if($facility->location_map)
                @if(\Illuminate\Support\Str::startsWith($facility->location_map, ['http://', 'https://']))
                <div class="p-6 pt-0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location Map</label>
                    <iframe src="{{ $facility->location_map }}" width="100%" height="200" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                @else
                <div class="p-6 pt-0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location Map</label>
                    {!! $facility->location_map !!}
                </div>
                @endif
                @endif

=======
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                <!-- Action Buttons -->
                <div class="p-6 pt-0 space-y-3">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.facilities.edit', $facility->id) }}"
<<<<<<< HEAD
                            class="flex-1 bg-primary text-white text-center py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
=======
                           class="flex-1 bg-primary text-white text-center py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                            Edit Details
                        </a>

                        <a href="{{ route('admin.facilities.layout-config', $facility->id) }}"
<<<<<<< HEAD
                            class="flex-1 bg-purple-600 text-white text-center py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
=======
                           class="flex-1 bg-purple-600 text-white text-center py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                            Configure Layout
                        </a>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('dashboard.facility', $facility->id) }}"
<<<<<<< HEAD
                            class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
=======
                           class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                            Preview Site
                        </a>

                        @if($facility->domain)
<<<<<<< HEAD
                        <a href="http://{{ $facility->domain }}" target="_blank"
                            class="flex-1 bg-green-100 text-green-700 text-center py-2 px-4 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
=======
                        <a href="http://{{ $facility->domain }}"
                           target="_blank"
                           class="flex-1 bg-green-100 text-green-700 text-center py-2 px-4 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                            Visit Live
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($facilities->hasPages())
        <div class="mt-8">
            {{ $facilities->links() }}
        </div>
        @endif

        <!-- Empty State -->
        @if($facilities->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
=======
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No facilities found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by seeding some facilities.</p>
        </div>
        @endif
    </div>
</div>
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
