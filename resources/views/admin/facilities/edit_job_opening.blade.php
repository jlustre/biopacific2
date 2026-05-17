@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Job Opening</h1>
        <a href="{{ route('admin.facility.job_openings', $facility) }}"
            class="inline-block px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back to Listings</a>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.facility.job_openings.update', [$facility, $jobOpening]) }}"
        class="bg-white p-6 rounded shadow" id="edit-job-opening-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Facility Name</label>
                <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="{{ $facility->name }}" disabled>
            </div>
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" name="title" class="w-full border rounded px-3 py-2" value="{{ old('title', $jobOpening->title) }}" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Employment Type</label>
                <select name="employment_type" class="w-full border rounded px-3 py-2">
                    <option value="">Select Employment Type</option>
                    @foreach(['Full-time', 'Part-time', 'Per Diem', 'Temporary', 'Contract'] as $type)
                    <option value="{{ $type }}" @selected(old('employment_type', $jobOpening->employment_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Department</label>
                <input type="text" name="department" class="w-full border rounded px-3 py-2"
                    value="{{ old('department', $jobOpening->department) }}">
            </div>
            <div>
                <label class="block font-semibold mb-1">Reporting To</label>
                <select name="reporting_to" class="w-full border rounded px-3 py-2">
                    <option value="">Select Reporting To</option>
                    @foreach($reportingToPositions as $position)
                    <option value="{{ $position }}" @selected(old('reporting_to', $jobOpening->reporting_to) === $position)>{{ $position }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Posted At</label>
                <input type="date" name="posted_at" class="w-full border rounded px-3 py-2"
                    value="{{ old('posted_at', $jobOpening->posted_at?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="block font-semibold mb-1">Expires At</label>
                <input type="date" name="expires_at" class="w-full border rounded px-3 py-2"
                    value="{{ old('expires_at', $jobOpening->expires_at?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="block font-semibold mb-1">Active</label>
                <select name="active" class="w-full border rounded px-3 py-2">
                    <option value="1" @selected(old('active', $jobOpening->active ? '1' : '0') == '1')>Active</option>
                    <option value="0" @selected(old('active', $jobOpening->active ? '1' : '0') == '0')>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2" required>
                    @foreach(['open', 'closed', 'pending', 'filled', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $jobOpening->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Description</label>
                <textarea id="edit-job-description" name="description" class="w-full border rounded px-3 py-2"
                    rows="10">{{ old('description', $jobOpening->description) }}</textarea>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                class="px-6 py-2 bg-teal-600 text-white font-semibold rounded hover:bg-teal-700">Update Job Opening</button>
        </div>
    </form>
</div>

<div class="container py-4">
    <h2 class="text-xl font-bold mb-4">Description Templates</h2>
    <livewire:admin.description-templates-manager />
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce === 'undefined' || !document.getElementById('edit-job-description')) {
            return;
        }
        tinymce.init({
            selector: '#edit-job-description',
            menubar: false,
            plugins: 'lists link table code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link table | code',
            min_height: 280,
            branding: false,
            promotion: false,
        });

        const form = document.getElementById('edit-job-opening-form');
        if (form) {
            form.addEventListener('submit', function () {
                const editor = tinymce.get('edit-job-description');
                if (editor) {
                    editor.save();
                }
            });
        }
    });
</script>
@endpush
