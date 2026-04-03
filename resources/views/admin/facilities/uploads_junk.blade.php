@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Facility Uploads</h1>
    <div class="p-6 mb-6 bg-white rounded shadow">
        <form method="POST" action="{{ route('admin.facility.uploads.store', ['facility' => request()->route('facility')]) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
            @csrf
            <div>
                <label class="block mb-1 text-xs font-semibold">Upload Type</label>
                <select name="upload_type_id" class="border-2 border-teal-500 rounded focus:border-teal-600" required>
                    <option value="">Select Type</option>
                    @foreach($uploadTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">File</label>
                    <input type="file" name="file" class="border-2 border-teal-500 rounded focus:border-teal-600" required>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                    <input type="date" name="effective_start_date" class="border-2 border-teal-500 rounded focus:border-teal-600" required>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Effective End Date</label>
                    <input type="date" name="effective_end_date" class="border-2 border-teal-500 rounded focus:border-teal-600">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Expires At</label>
                    <input type="date" name="expires_at" class="border-2 border-teal-500 rounded focus:border-teal-600">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Description</label>
                <input type="text" name="description" class="border-2 border-teal-500 rounded focus:border-teal-600">
            </div>
            <button type="submit" class="px-4 py-2 ml-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">Upload2</button>
        </form>
    </div>
    <div class="p-6 bg-white rounded shadow">
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">File Name</th>
                    <th class="px-3 py-2 border">Type</th>
                    <th class="px-3 py-2 border">Uploaded By</th>
                    <th class="px-3 py-2 border">Size</th>
                    <th class="px-3 py-2 border">Effective Dates</th>
                    <th class="px-3 py-2 border">Expires</th>
                    <th class="px-3 py-2 border">Uploaded</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uploads as $upload)
                <tr>
                    <td class="px-3 py-2 border">{{ $upload->original_filename }}</td>
                    <td class="px-3 py-2 border">{{ $upload->uploadType->name ?? '-' }}</td>
                    <td class="px-3 py-2 border">{{ $upload->user->name ?? '-' }}</td>
                    <td class="px-3 py-2 border">{{ number_format($upload->file_size / 1024, 2) }} KB</td>
                    <td class="px-3 py-2 border">{{ $upload->effective_start_date }} - {{ $upload->effective_end_date ?? 'Current' }}</td>
                    <td class="px-3 py-2 border">{{ $upload->expires_at ?? '-' }}</td>
                    <td class="px-3 py-2 border">{{ $upload->uploaded_at ? \Carbon\Carbon::parse($upload->uploaded_at)->format('M d, Y g:i A') : '-' }}</td>
                    <td class="px-3 py-2 border">
                        <a href="{{ route('admin.facility.uploads.download', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" class="mr-2 text-blue-600 hover:underline">Download</a>
                        <form action="{{ route('admin.facility.uploads.destroy', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this upload?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-6 text-center text-gray-500">No uploads found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $uploads->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
