@extends('layouts.dashboard')

@section('title', 'Security Monitoring - Cleanup')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Security Log Cleanup</h1>
                        <p class="mt-2 text-gray-600">Manage and clean up access log history for data management and
                            compliance</p>
                    </div>
                    <a href="{{ route('admin.security.dashboard') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Current Statistics -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">📊 Current Access Log Statistics</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_logs']) }}
                            </div>
                            <div class="text-sm text-blue-700">Total Access Logs</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['oldest_log']?->format('M j, Y') ??
                                'N/A' }}</div>
                            <div class="text-sm text-green-700">Oldest Log</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-purple-600">{{ $stats['newest_log']?->format('M j, Y')
                                ?? 'N/A' }}</div>
                            <div class="text-sm text-purple-700">Newest Log</div>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-orange-600">
                                {{ $stats['oldest_log'] && $stats['newest_log'] ?
                                $stats['oldest_log']->diffInDays($stats['newest_log']) : 0 }}
                            </div>
                            <div class="text-sm text-orange-700">Days Span</div>
                        </div>
                    </div>

                    <!-- Breakdown by Type -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">By Record Type</h3>
                            <div class="space-y-2">
                                @forelse($stats['by_type'] as $type => $count)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $type)
                                        }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($count) }}</span>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">No logs available</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">By Access Status</h3>
                            <div class="space-y-2">
                                @forelse($stats['by_status'] as $status => $count)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $status)
                                        }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($count) }}</span>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">No logs available</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">By Facility</h3>
                            <div class="space-y-2">
                                @forelse($stats['by_facility'] as $facilityLog)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">{{ $facilityLog->facility->name ?? 'Unknown'
                                        }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{
                                        number_format($facilityLog->count) }}</span>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">No logs available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cleanup Options -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">🧹 Cleanup Options</h2>
                    <p class="text-sm text-gray-600 mt-1">Choose how you want to clean up the access logs. This action
                        cannot be undone.</p>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.security.cleanup.perform') }}" class="space-y-6">
                        @csrf

                        <!-- Cleanup Type Selection -->
                        <div>
                            <label class="text-base font-medium text-gray-900">Cleanup Type</label>
                            <p class="text-sm text-gray-500">Select what type of cleanup you want to perform</p>
                            <fieldset class="mt-4">
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="cleanup_all" name="cleanup_type" type="radio" value="all"
                                            class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                        <label for="cleanup_all" class="ml-3 block text-sm font-medium text-gray-700">
                                            🚨 Delete ALL access logs ({{ number_format($stats['total_logs']) }}
                                            records)
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input id="cleanup_failed" name="cleanup_type" type="radio" value="failed_only"
                                            class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                        <label for="cleanup_failed"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            ⚠️ Delete only failed/unauthorized access attempts
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input id="cleanup_by_type" name="cleanup_type" type="radio" value="by_type"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <label for="cleanup_by_type"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            📝 Delete by record type
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input id="cleanup_by_status" name="cleanup_type" type="radio" value="by_status"
                                            class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                        <label for="cleanup_by_status"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            🎯 Delete by access status
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input id="cleanup_by_facility" name="cleanup_type" type="radio"
                                            value="by_facility"
                                            class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                        <label for="cleanup_by_facility"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            🏢 Delete by facility
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input id="cleanup_by_date" name="cleanup_type" type="radio" value="by_date"
                                            class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="cleanup_by_date"
                                            class="ml-3 block text-sm font-medium text-gray-700">
                                            📅 Delete logs older than specific date
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <!-- Conditional Fields -->
                        <div id="type_selector" class="hidden">
                            <label for="token_type" class="block text-sm font-medium text-gray-700">Record Type</label>
                            <select id="token_type" name="token_type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select record type</option>
                                <option value="inquiry">Contact Inquiries</option>
                                <option value="job_application">Job Applications</option>
                                <option value="tour_request">Tour Requests</option>
                            </select>
                        </div>

                        <div id="status_selector" class="hidden">
                            <label for="access_status" class="block text-sm font-medium text-gray-700">Access
                                Status</label>
                            <select id="access_status" name="access_status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select access status</option>
                                <option value="success">Successful Access</option>
                                <option value="unauthorized">Unauthorized</option>
                                <option value="invalid_token">Invalid Token</option>
                                <option value="expired">Expired</option>
                                <option value="staff_verification_failed">Staff Verification Failed</option>
                            </select>
                        </div>

                        <div id="facility_selector" class="hidden">
                            <label for="facility_id" class="block text-sm font-medium text-gray-700">Facility</label>
                            <select id="facility_id" name="facility_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select facility</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="date_selector" class="hidden">
                            <label for="date_before" class="block text-sm font-medium text-gray-700">Delete logs older
                                than</label>
                            <input type="date" id="date_before" name="date_before"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Confirmation -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <input id="confirm_cleanup" name="confirm_cleanup" type="checkbox"
                                    class="h-4 w-4 text-red-600 border-red-300 rounded focus:ring-red-500" required>
                                <label for="confirm_cleanup" class="ml-2 text-sm text-red-700">
                                    <strong>I understand that this action cannot be undone and will permanently delete
                                        the selected access logs.</strong>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                🗑️ Perform Cleanup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle cleanup type changes
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="cleanup_type"]');
    const typeSelector = document.getElementById('type_selector');
    const statusSelector = document.getElementById('status_selector');
    const facilitySelector = document.getElementById('facility_selector');
    const dateSelector = document.getElementById('date_selector');

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all selectors
            typeSelector.classList.add('hidden');
            statusSelector.classList.add('hidden');
            facilitySelector.classList.add('hidden');
            dateSelector.classList.add('hidden');

            // Show relevant selector based on selection
            switch(this.value) {
                case 'by_type':
                    typeSelector.classList.remove('hidden');
                    break;
                case 'by_status':
                    statusSelector.classList.remove('hidden');
                    break;
                case 'by_facility':
                    facilitySelector.classList.remove('hidden');
                    break;
                case 'by_date':
                    dateSelector.classList.remove('hidden');
                    break;
            }
        });
    });
});
</script>
@endsection