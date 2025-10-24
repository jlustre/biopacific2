@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    @include('components.back-link-header', [
    'title_hdr' => 'Add Email Recipient',
    'subtitle_hdr' => 'Create a new email recipient for notifications',
    'preview' => false
    ])

    <form method="POST" action="{{ route('admin.email-recipients.store') }}"
        class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf

        <div class="mb-4">
            <label for="facility_id" class="block text-gray-700 text-sm font-bold mb-2">Facility:</label>
            <select name="facility_id" id="facility_id"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @foreach ($facilities as $facility)
                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
            <input type="text" name="category" id="category"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" name="email" id="email"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save
            </button>
        </div>
    </form>
</div>
@endsection