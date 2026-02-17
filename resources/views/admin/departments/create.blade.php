@extends('layouts.dashboard', ['title' => 'Create Department'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create Department</h1>
    <p class="text-gray-600 mt-2">Add a new organizational department</p>
</div>
<div class="max-w-2xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Department Name <span
                        class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" placeholder="e.g., Nursing, Administrative"
                    value="{{ old('name') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type Field -->
            <div>
                <label for="type" class="block text-sm font-semibold text-gray-900 mb-2">Type <span
                        class="text-red-500">*</span></label>
                <select name="type" id="type" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select a type...</option>
                    @foreach ($types as $typeKey => $typeLabel)
                    <option value="{{ $typeKey }}" {{ old('type')==$typeKey ? 'selected' : '' }}>
                        {{ $typeLabel }}
                    </option>
                    @endforeach
                </select>
                @error('type')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">
                    <strong>Facility:</strong> Department specific to a single facility
                    <br><strong>Corporate:</strong> Department at the corporate/company level
                </p>
            </div>

            <!-- Description Field -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" rows="6" placeholder="Enter department description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Create Department
                </button>
                <a href="{{ route('admin.departments.index') }}"
                    class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection