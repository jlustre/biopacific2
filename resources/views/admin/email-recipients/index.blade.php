@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    @include('components.back-link-header', [
    'title_hdr' => 'Email Recipients',
    'subtitle_hdr' => 'Manage email recipients for notifications',
    'preview' => false
    ])

    <div class="flex justify-between items-center my-4">
        <form method="GET" action="{{ route('admin.email-recipients.index') }}" class="flex space-x-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email or category"
                class="border border-slate-500 bg-white/70 rounded px-4 py-2">
            <select name="facility" class="border border-slate-500 bg-white/70 rounded px-4 py-2">
                <option value="">All Facilities</option>
                @foreach ($facilities as $facility)
                <option value="{{ $facility->id }}" {{ request('facility')==$facility->id ? 'selected' : '' }}>{{
                    $facility->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
        </form>

        <a href="{{ route('admin.email-recipients.create') }}"
            class="bg-teal-500 text-white px-4 py-2 rounded inline-block">Add New Recipient</a>
    </div>

    <table class="min-w-full bg-white responsive-table">
        <thead>
            <tr>
                <th class="p-2 border-b">ID</th>
                <th class="p-2 border-b">Facility</th>
                <th class="p-2 border-b">Category</th>
                <th class="p-2 border-b">Email</th>
                <th class="p-2 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emailRecipients as $recipient)
            <tr>
                <td class="p-2 border-b">{{ $recipient->id }}</td>
                <td class="p-2 border-b">{{ $recipient->facility->name }}</td>
                <td class="p-2 border-b">{{ $recipient->category }}</td>
                <td class="p-2 border-b">{{ $recipient->email }}</td>
                <td class="p-2 border-b flex items-center space-x-2">
                    <a href="{{ route('admin.email-recipients.show', $recipient->id) }}"
                        class="text-blue-500 hover:underline" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.email-recipients.edit', $recipient->id) }}"
                        class="text-green-500 hover:underline" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.email-recipients.destroy', $recipient->id) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline" title="Delete"
                            onclick="return confirm('Are you sure you want to delete this recipient?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $emailRecipients->links() }}
    </div>
</div>
@endsection