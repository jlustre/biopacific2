@extends('layouts.dashboard', ['title' => $department->name])

@section('content')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $department->name }}</h1>
        <p class="text-gray-600 mt-2">Department Details</p>
    </div>
    <div class="space-x-2">
        <a href="{{ route('admin.departments.edit', $department) }}"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('admin.departments.index') }}"
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
                    <p class="text-sm font-semibold text-gray-600">Name</p>
                    <p class="text-gray-900">{{ $department->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Type</p>
                    <div
                        class="inline-block {{ $department->type === 'facility' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }} px-3 py-1 rounded-full mt-1 font-medium">
                        {{ ucfirst($department->type) }}
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Description</p>
                    <p class="text-gray-900 mt-1">
                        @if($department->description)
                        {!! nl2br(e($department->description)) !!}
                        @else
                        <span class="text-gray-400 italic">No description provided</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Associated Positions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Associated Positions</h2>
            @if($department->positions->count() > 0)
            <div class="space-y-3">
                @foreach($department->positions as $position)
                <div class="border border-gray-200 rounded p-4 hover:bg-blue-50 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $position->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ isset($position->description) && strlen($position->description) > 0 ?
                                (strlen($position->description) > 100 ? substr($position->description, 0, 100) . '...' :
                                $position->description) : 'No description' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.positions.edit', $position) }}"
                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 italic">No positions assigned to this department yet.</p>
            @endif
        </div>
    </div>

    <!-- Sidebar Information -->
    <div class="space-y-6">
        <!-- Statistics -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Statistics</h2>
            <div class="space-y-3">
                <div class="bg-blue-50 rounded p-4">
                    <p class="text-gray-600 text-sm">Total Positions</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $department->positions->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Metadata</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600">ID</p>
                    <p class="text-gray-900 font-mono">{{ $department->id }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Created</p>
                    <p class="text-gray-900">{{ $department->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Updated</p>
                    <p class="text-gray-900">{{ $department->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
            @if($department->positions->count() === 0)
            <form action="{{ route('admin.departments.destroy', $department) }}" method="POST"
                onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold text-sm">
                    <i class="fas fa-trash mr-2"></i> Delete Department
                </button>
            </form>
            @else
            <p class="text-gray-500 text-sm">
                <i class="fas fa-lock mr-2"></i> Cannot delete department with active positions.
            </p>
            @endif
        </div>
    </div>
</div>
@endsection