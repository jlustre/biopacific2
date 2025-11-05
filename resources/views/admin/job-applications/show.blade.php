@extends('layouts.dashboard', ['title' => 'View Job Application'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Job Application Details</h1>
            <p class="text-gray-600">Submitted on {{ $jobApplication->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        <a href="{{ route('admin.job-applications.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Cover Letter -->
            @if($jobApplication->cover_letter)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Cover Letter</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $jobApplication->cover_letter }}</p>
                </div>
            </div>
            @endif

            <!-- Resume -->
            @if($jobApplication->resume_path)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Resume</h2>
                <div class="flex items-center space-x-4">
                    @php
                    $extension = pathinfo($jobApplication->resume_path, PATHINFO_EXTENSION);
                    $iconClass = match(strtolower($extension)) {
                    'pdf' => 'fas fa-file-pdf text-red-500',
                    'doc', 'docx' => 'fas fa-file-word text-blue-500',
                    default => 'fas fa-file text-gray-500'
                    };
                    @endphp
                    <i class="{{ $iconClass }} text-2xl"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Resume File</p>
                        <p class="text-sm text-gray-500">{{ basename($jobApplication->resume_path) }}</p>
                        <p class="text-xs text-gray-400">{{ strtoupper($extension) }} • {{
                            number_format(Storage::disk('public')->size($jobApplication->resume_path) / 1024, 1) }} KB
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.job-applications.preview-resume', $jobApplication) }}" target="_blank"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                            <i class="fas fa-eye mr-1"></i>Preview
                        </a>
                        <a href="{{ route('admin.job-applications.download-resume', $jobApplication) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    </div>
                </div>

                @if(strtolower($extension) === 'pdf')
                <!-- PDF Preview Embed -->
                <div class="mt-6">
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Resume Preview</span>
                            <button onclick="togglePreview()" id="toggleBtn"
                                class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye mr-1"></i>Show Preview
                            </button>
                        </div>
                        <div id="previewContainer" class="hidden">
                            <iframe src="{{ route('admin.job-applications.preview-resume', $jobApplication) }}"
                                class="w-full h-96 border-0" title="Resume Preview">
                            </iframe>
                        </div>
                    </div>
                </div>
                @else
                <!-- Non-PDF file notice -->
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <p class="text-sm text-blue-700">
                            This {{ strtoupper($extension) }} file cannot be previewed in the browser. Use the preview
                            button to open it in a new tab or download it directly.
                        </p>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Applicant Information -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Applicant Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <p class="text-sm text-gray-900">{{ $jobApplication->first_name }} {{ $jobApplication->last_name
                            }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <p class="text-sm text-gray-900">
                            <a href="mailto:{{ $jobApplication->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $jobApplication->email }}
                            </a>
                        </p>
                    </div>
                    @if($jobApplication->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <p class="text-sm text-gray-900">
                            <a href="tel:{{ $jobApplication->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $jobApplication->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Job Opening Information -->
            @if($jobApplication->jobOpening)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Opening</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <p class="text-sm text-gray-900">{{ $jobApplication->jobOpening->title }}</p>
                    </div>
                    @if($jobApplication->jobOpening->facility)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <p class="text-sm text-gray-900">{{ $jobApplication->jobOpening->facility->name }}</p>
                    </div>
                    @endif
                    @if($jobApplication->jobOpening->department)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <p class="text-sm text-gray-900">{{ $jobApplication->jobOpening->department }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Application Status -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Application Status</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($jobApplication->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($jobApplication->status === 'reviewed') bg-blue-100 text-blue-800
                            @elseif($jobApplication->status === 'interview') bg-purple-100 text-purple-800
                            @elseif($jobApplication->status === 'hired') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($jobApplication->status) }}
                        </span>
                    </div>

                    <!-- Status Update Form -->
                    <form action="{{ route('admin.job-applications.update-status', $jobApplication) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Update Status</label>
                            <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="pending" {{ $jobApplication->status === 'pending' ? 'selected' : ''
                                    }}>Pending</option>
                                <option value="reviewed" {{ $jobApplication->status === 'reviewed' ? 'selected' : ''
                                    }}>Reviewed</option>
                                <option value="interview" {{ $jobApplication->status === 'interview' ? 'selected' : ''
                                    }}>Interview</option>
                                <option value="hired" {{ $jobApplication->status === 'hired' ? 'selected' : '' }}>Hired
                                </option>
                                <option value="rejected" {{ $jobApplication->status === 'rejected' ? 'selected' : ''
                                    }}>Rejected</option>
                            </select>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Consent Information -->
            @if($jobApplication->consent)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Consent</h3>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-gray-700">Consent given for communication</span>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <a href="mailto:{{ $jobApplication->email }}?subject=Re: Your Application for {{ $jobApplication->jobOpening->title ?? 'Job Opening' }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-reply mr-2"></i>Reply via Email
                    </a>
                    <form action="{{ route('admin.job-applications.destroy', $jobApplication) }}" method="POST"
                        class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Are you sure you want to delete this application?')"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash mr-2"></i>Delete Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePreview() {
    const container = document.getElementById('previewContainer');
    const btn = document.getElementById('toggleBtn');
    
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide Preview';
    } else {
        container.classList.add('hidden');
        btn.innerHTML = '<i class="fas fa-eye mr-1"></i>Show Preview';
    }
}
</script>

@endsection