@extends('layouts.dashboard', ['title' => $position->title])

@section('content')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $position->title }}</h1>
        <p class="text-gray-600 mt-2">Position Details</p>
    </div>
    <div class="space-x-2">
        <a href="{{ route('admin.positions.edit', $position) }}"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('admin.positions.index') }}"
            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="md:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold text-gray-600">Title</p>
                    <p class="text-gray-900">{{ $position->title }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Department</p>
                    <div class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full mt-1">
                        {{ $position->department->name ?? 'N/A' }}
                        <span class="text-xs ml-2 opacity-75">({{ ucfirst($position->department->type ?? '') }})</span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Description</p>
                    <p class="text-gray-900 mt-1">
                        @if($position->description)
                        {!! nl2br(e($position->description)) !!}
                        @else
                        <span class="text-gray-400 italic">No description provided</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Information -->
    <div class="space-y-6">
        <!-- Metadata -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Metadata</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600">ID</p>
                    <p class="text-gray-900 font-mono">{{ $position->id }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Created</p>
                    <p class="text-gray-900">{{ $position->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Updated</p>
                    <p class="text-gray-900">{{ $position->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
            <form action="{{ route('admin.positions.destroy', $position) }}" method="POST"
                onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold text-sm">
                    <i class="fas fa-trash mr-2"></i> Delete Position
                </button>
            </form>
        </div>
    </div>
</div>
@endsection