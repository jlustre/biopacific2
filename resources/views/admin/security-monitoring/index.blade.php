@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🛡️ Security Monitoring</h1>
            <p class="text-gray-600 mt-2 mb-2">Monitor secure access attempts and detect anomalies across all facilities
            </p>
            <div class="flex flex-wrap gap-3 mt-4">
                <a href="{{ asset('docs/INCIDENT_RESPONSE_PLAYBOOK.md') }}" target="_blank"
                    class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 flex items-center">
                    📄 Incident Response Playbook
                </a>
                <a href="{{ route('admin.incident-contacts.index') }}"
                    class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800 flex items-center">
                    📇 Incident Response Contacts
                </a>
                <a href="{{ route('admin.security.anomalies') }}"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center">
                    🚨 View Anomalies
                </a>
                <a href="{{ route('admin.security.incidents') }}"
                    class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 flex items-center">
                    📋 Security Incidents
                </a>
                <a href="{{ route('admin.security.cleanup') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                    🧹 Cleanup Logs
                </a>
                <a href="{{ route('admin.security.export') }}?start_date={{ $startDate }}&end_date={{ $endDate }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                    📊 Export Report
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.security.dashboard') }}"
            class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="facility_id" class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                <select name="facility_id" id="facility_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ $facilityId==$facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    🔍 Filter Results
                </button>
            </div>
        </form>
    </div>

    <!-- Metrics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-blue-600 text-sm">📊</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['total_access']) }}</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Total Access</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-green-600 text-sm">✅</span>
                </div>
                <p class="text-2xl font-bold text-green-700">{{ number_format($metrics['successful_access']) }}</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Successful</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-red-600 text-sm">❌</span>
                </div>
                <p class="text-2xl font-bold text-red-700">{{ number_format($metrics['failed_access']) }}</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Failed</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-purple-600 text-sm">📈</span>
                </div>
                <p class="text-2xl font-bold text-purple-700">{{ $metrics['success_rate'] }}%</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Success Rate</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-indigo-600 text-sm">🌐</span>
                </div>
                <p class="text-2xl font-bold text-indigo-700">{{ number_format($metrics['unique_ips']) }}</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Unique IPs</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-teal-600 text-sm">👥</span>
                </div>
                <p class="text-2xl font-bold text-teal-700">{{ number_format($metrics['verified_staff']) }}</p>
            </div>
            <p class="text-sm font-medium text-gray-500 text-center">Verified Staff</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Suspicious Activities -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🚨 Recent Suspicious Activities</h3>
            </div>
            <div class="p-6">
                @if($suspiciousActivities->count() > 0)
                <div class="space-y-4">
                    @foreach($suspiciousActivities as $activity)
                    <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex-shrink-0">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ ucwords(str_replace('_', ' ', $activity->access_status)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <strong>{{ ucwords($activity->token_type) }}</strong> #{{ $activity->record_id }}
                                @if($activity->facility)
                                at {{ $activity->facility->name }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                IP: {{ $activity->ip_address }} •
                                {{ $activity->access_time->diffForHumans() }}
                                @if($activity->staff_email)
                                • Email: {{ $activity->staff_email }}
                                @endif
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.security.record-logs', [$activity->token_type, $activity->record_id]) }}"
                                class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <span class="text-4xl">✅</span>
                    <p class="text-gray-500 mt-2">No suspicious activities detected</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Failed Access Attempts -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">❌ Recent Failed Attempts</h3>
            </div>
            <div class="p-6">
                @if($failedAttempts->count() > 0)
                <div class="space-y-4">
                    @foreach($failedAttempts as $attempt)
                    <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="flex-shrink-0">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ ucwords(str_replace('_', ' ', $attempt->access_status)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <strong>{{ ucwords($attempt->token_type) }}</strong> #{{ $attempt->record_id }}
                                @if($attempt->facility)
                                at {{ $attempt->facility->name }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                IP: {{ $attempt->ip_address }} •
                                {{ $attempt->access_time->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.security.record-logs', [$attempt->token_type, $attempt->record_id]) }}"
                                class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <span class="text-4xl">✅</span>
                    <p class="text-gray-500 mt-2">No failed attempts detected</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Accessed Records -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📈 Most Accessed Records</h3>
            </div>
            <div class="p-6">
                @if($topAccessedRecords->count() > 0)
                <div class="space-y-3">
                    @foreach($topAccessedRecords as $record)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ ucwords($record->token_type) }} #{{ $record->record_id }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $record->facility->name ?? 'Unknown Facility' }} •
                                Last accessed {{ Carbon\Carbon::parse($record->latest_access)->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $record->access_count }} accesses
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <span class="text-4xl">📊</span>
                    <p class="text-gray-500 mt-2">No access data available</p>
                </div>
                @endif
            </div>
        </div>

        <!-- IP Analysis -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🌐 Top IP Addresses</h3>
            </div>
            <div class="p-6">
                @if($ipAnalysis->count() > 0)
                <div class="space-y-3">
                    @foreach($ipAnalysis->take(10) as $ip)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $ip->ip_address }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $ip->facility_count }} facilities •
                                Last seen {{ Carbon\Carbon::parse($ip->latest_access)->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $ip->access_count > 20 ? 'bg-red-100 text-red-800' : ($ip->access_count > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $ip->access_count }} accesses
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <span class="text-4xl">🌐</span>
                    <p class="text-gray-500 mt-2">No IP data available</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-sm text-gray-500">
        <p>Security monitoring data for {{ $metrics['date_range'] }} • All access attempts are logged for HIPAA
            compliance</p>
    </div>
</div>
@endsection