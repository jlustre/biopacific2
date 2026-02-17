@extends('layouts.dashboard', ['title' => 'Edit Position'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Position</h1>
    <p class="text-gray-600 mt-2">Update position information</p>
</div>
<div class="max-w-2xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.positions.update', $position) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Position Title <span
                        class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g., Registered Nurse"
                    value="{{ old('title', $position->title) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department Field -->
            <div>
                <label for="department_id" class="block text-sm font-semibold text-gray-900 mb-2">Department <span
                        class="text-red-500">*</span></label>
                <select name="department_id" id="department_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select a department...</option>
                    @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $position->department_id) == $dept->id ?
                        'selected' : '' }}>
                        {{ $dept->name }} ({{ ucfirst($dept->type) }})
                    </option>
                    @endforeach
                </select>
                @error('department_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" rows="6" placeholder="Enter position description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $position->description) }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Update Position
                </button>
                <a href="{{ route('admin.positions.index') }}"
                    class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection