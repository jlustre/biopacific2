@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    @include('components.back-link-header', [
    'title_hdr' => 'Tour Requests',
    'subtitle_hdr' => 'Manage requests for tours',
    'preview' => false
    ])

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route('admin.tour-requests.index') }}" class="mb-4 mt-4">
        <div class="flex space-x-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email"
                class="border border-slate-400 rounded px-4 py-2 w-full">
            <select name="facility" class="border border-slate-400 rounded px-4 py-2">
                <option value="">All Facilities</option>
                @foreach ($facilities as $facility)
                <option value="{{ $facility->id }}" {{ request('facility')==$facility->id ? 'selected' : '' }}>{{
                    $facility->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
        </div>
    </form>

    <!-- Tour Requests Table -->
    <table class="min-w-full bg-white responsive-table">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">ID</th>
                <th class="py-2 px-4 border-b">Facility</th>
                <th class="py-2 px-4 border-b">Full Name</th>
                <th class="py-2 px-4 border-b">Email</th>
                <th class="py-2 px-4 border-b">Phone</th>
                <th class="py-2 px-4 border-b">Preferred Date</th>
                <th class="py-2 px-4 border-b">Preferred Time</th>
                <th class="py-2 px-4 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tourRequests as $tourRequest)
            <tr>
                <td class="p-2 border-b">{{ $tourRequest->id }}</td>
                <td class="p-2 border-b text-sm">{{ $tourRequest->facility->name }}</td>
                <td class="p-2 border-b text-sm">{{ $tourRequest->full_name }}</td>
                <td class="p-2 border-b text-sm">{{ $tourRequest->email }}</td>
                <td class="p-2 border-b">{{ $tourRequest->phone }}</td>
                <td class="p-2 border-b">{{ $tourRequest->preferred_date }}</td>
                <td class="p-2 border-b">{{ $tourRequest->preferred_time }}</td>
                <td class="p-2 border-b flex items-center space-x-2 justify-end">
                    <a href="{{ route('admin.tour-requests.show', $tourRequest->id) }}"
                        class="text-blue-500 hover:underline" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <form action="{{ route('admin.tour-requests.destroy', $tourRequest->id) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline" title="Delete"
                            onclick="return confirm('Are you sure you want to delete this tour request?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tourRequests->withQueryString()->links() }}
    </div>
</div>
@endsection