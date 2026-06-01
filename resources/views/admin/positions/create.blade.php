@extends('layouts.dashboard', ['title' => 'Create Position'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create Position</h1>
    <p class="text-gray-600 mt-2">Add a new job position</p>
</div>
<div class="max-w-2xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.positions.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Position Title <span
                        class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g., Registered Nurse"
                    value="{{ old('title') }}" required
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
                    <option value="{{ $dept->id }}" {{ old('department_id')==$dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} ({{ ucfirst($dept->type) }})
                    </option>
                    @endforeach
                </select>
                @error('department_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reports_to_position_id" class="block text-sm font-semibold text-gray-900 mb-2">Reports To Position</label>
                <select name="reports_to_position_id" id="reports_to_position_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">No reporting position</option>
                    @foreach ($reportingPositions as $reportingPosition)
                    <option value="{{ $reportingPosition->id }}" {{ old('reports_to_position_id') == $reportingPosition->id ? 'selected' : '' }}>
                        {{ $reportingPosition->title }}
                    </option>
                    @endforeach
                </select>
                @error('reports_to_position_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" rows="6" placeholder="Enter position description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Required Documents</label>
                <p class="text-xs text-gray-500 mb-3">Select the upload types required for this position. Completion in employee Documents tab is auto-calculated from uploaded unexpired files.</p>
                <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 p-3 space-y-2 bg-gray-50">
                    @forelse($uploadTypes as $uploadType)
                        <label class="flex items-start gap-3 p-2 rounded hover:bg-white">
                            <input
                                type="checkbox"
                                name="required_upload_type_ids[]"
                                value="{{ $uploadType->id }}"
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                {{ in_array($uploadType->id, old('required_upload_type_ids', [])) ? 'checked' : '' }}
                            >
                            <span class="text-sm text-gray-800">
                                <span class="font-medium">{{ $uploadType->name }}</span>
                                @if($uploadType->requires_expiry)
                                    <span class="ml-1 inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-amber-800">Expiry required</span>
                                @endif
                                @if($uploadType->description)
                                    <span class="block text-xs text-gray-500 mt-0.5">{{ $uploadType->description }}</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No upload types available. Create upload types first.</p>
                    @endforelse
                </div>
                @error('required_upload_type_ids')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('required_upload_type_ids.*')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Create Position
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