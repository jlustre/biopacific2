@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="text-2xl font-bold mb-4">Documents</h1>
    <div class="bg-white p-6 rounded shadow">
        <form method="GET" class="mb-4 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold mb-1">Search by Name</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-input rounded border-gray-300" placeholder="Document name...">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Filter by Facility</label>
                <select name="facility_id" class="form-select rounded border-gray-300">
                    <option value="">All Facilities</option>
                    @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                    <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name
                        }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Filter</button>
        </form>

        <table class="min-w-full table-auto border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">File Name</th>
                    <th class="px-3 py-2 border">Type</th>
                    <th class="px-3 py-2 border">Facility</th>
                    <th class="px-3 py-2 border">Uploaded By</th>
                    <th class="px-3 py-2 border">Size</th>
                    <th class="px-3 py-2 border">Created</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $query = App\Models\EmployeeDocument::query();
                if(request('facility_id')) $query->where('facility_id', request('facility_id'));
                if(request('search')) $query->where('file_name', 'like', '%'.request('search').'%');
                $documents = $query->with(['facility','createdBy'])->latest()->paginate(15);
                @endphp
                @forelse($documents as $doc)
                <tr>
                    <td class="px-3 py-2 border">{{ $doc->file_name }}</td>
                    <td class="px-3 py-2 border">{{ $doc->document_type }}</td>
                    <td class="px-3 py-2 border">{{ $doc->facility->name ?? '-' }}</td>
                    <td class="px-3 py-2 border">{{ $doc->createdBy->name ?? '-' }}</td>
                    <td class="px-3 py-2 border">{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                    <td class="px-3 py-2 border">{{ $doc->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-3 py-2 border">
                        <a href="{{ route('admin.facility.document.download', ['facility' => $doc->facility_id, 'document' => $doc->id]) }}"
                            class="text-blue-600 hover:underline mr-2">Download</a>
                        <form
                            action="{{ route('admin.facility.document.delete', ['facility' => $doc->facility_id, 'document' => $doc->id]) }}"
                            method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500">No documents found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $documents->withQueryString()->links() }}</div>
    </div>
</div>
@endsection