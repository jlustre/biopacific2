@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold">{{ $facility->name }}</h1>
                <p class="text-gray-600 mt-2">{{ $facility->city }}, {{ $facility->state }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('facilities.edit', $facility) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('facilities.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            @if($facility->hero_image_url)
                <div class="h-64 bg-cover bg-center" style="background-image: url('{{ $facility->hero_image_url }}')"></div>
            @endif

            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Facility Name</label>
                            <p class="text-gray-900">{{ $facility->name }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <p class="text-gray-900">{{ $facility->slug }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <p class="text-gray-900">{{ $facility->city }}, {{ $facility->state }}</p>
                        </div>

                        @if($facility->beds)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Number of Beds</label>
                                <p class="text-gray-900">{{ $facility->beds }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Ranking Information -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Ranking Information</h3>

                        @if($facility->ranking_position && $facility->ranking_total)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Ranking</label>
                                <p class="text-gray-900">{{ $facility->ranking_position }} of {{ $facility->ranking_total }}</p>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Created</label>
                            <p class="text-gray-900">{{ $facility->created_at->format('M d, Y') }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                            <p class="text-gray-900">{{ $facility->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                @if($facility->description)
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-lg font-semibold mb-4">Description</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $facility->description }}</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="mt-8 pt-6 border-t flex justify-between">
                    <form action="{{ route('facilities.destroy', $facility) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this facility? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete Facility
                        </button>
                    </form>

                    <div class="flex gap-2">
                        <a href="{{ route('facilities.edit', $facility) }}"
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Facility
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
