@extends('layouts.dashboard')

@section('content')
    <div class="container py-4">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">{{ $facility->name }}</h1>
            <div class="text-gray-600 text-lg">{{ $facility->address ?? 'No address on file' }}</div>
        </div>
        <div class="bg-white p-6 rounded shadow mb-8">
            <p>Welcome to the dashboard for <strong>{{ $facility->name }}</strong>.</p>
            <ul class="mt-4">
                <li><strong>Phone:</strong> {{ $facility->phone ?? 'N/A' }}</li>
                <li><strong>Email:</strong> {{ $facility->email ?? 'N/A' }}</li>
                <li><strong>Status:</strong> {{ $facility->status ?? 'N/A' }}</li>
            </ul>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('admin.facility.job_openings', ['facility' => $facility->id]) }}"
                class="flex flex-col items-center justify-center bg-indigo-50 hover:bg-indigo-100 rounded-xl p-6 shadow transition">
                <i class="fas fa-briefcase text-3xl text-indigo-600 mb-2"></i>
                <span class="font-semibold text-lg text-indigo-800">Job Listing</span>
            </a>
            <a href="{{ route('admin.facility.hiring', ['facility' => $facility->id]) }}"
                class="flex flex-col items-center justify-center bg-blue-50 hover:bg-blue-100 rounded-xl p-6 shadow transition">
                <i class="fas fa-user-plus text-3xl text-blue-600 mb-2"></i>
                <span class="font-semibold text-lg text-blue-800">Hiring</span>
            </a>

            <a href="{{ route('admin.facility.employees', ['facility' => $facility->id]) }}?facility={{ $facility->id }}"
                class="flex flex-col items-center justify-center bg-green-50 hover:bg-green-100 rounded-xl p-6 shadow transition">
                <i class="fas fa-users text-3xl text-green-600 mb-2"></i>
                <span class="font-semibold text-lg text-green-800">Employees</span>
            </a>
            <a href="{{ route('admin.facility.documents', ['facility' => $facility->id]) }}"
                class="flex flex-col items-center justify-center bg-purple-50 hover:bg-purple-100 rounded-xl p-6 shadow transition">
                <i class="fas fa-file-alt text-3xl text-purple-600 mb-2"></i>
                <span class="font-semibold text-lg text-purple-800">Documents</span>
            </a>
            <a href="{{ route('admin.facility.reports', ['facility' => $facility->id]) }}"
                class="flex flex-col items-center justify-center bg-pink-50 hover:bg-pink-100 rounded-xl p-6 shadow transition">
                <i class="fas fa-clipboard-list text-3xl text-pink-600 mb-2"></i>
                <span class="font-semibold text-lg text-pink-800">Reports</span>
            </a>
            <button type="button"
                onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="flex flex-col items-center justify-center bg-yellow-50 hover:bg-yellow-100 rounded-xl p-6 shadow transition focus:outline-none">
                <i class="fas fa-file-excel text-3xl text-yellow-600 mb-2"></i>
                <span class="font-semibold text-lg text-yellow-800">Files</span>
            </button>
        </div>
        @include('admin.facilities.partials.import-mapping-modal')
    </div>
                
    @include('admin.facilities.partials.import-mapping-scripts')
@endsection