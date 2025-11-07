@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">🚨 Security Anomalies</h1>
                <p class="text-gray-600 mt-2">Detailed view of all suspicious activities and security concerns</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.security.dashboard') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    ← Back to Dashboard
                </a>
                <a href="{{ route('admin.security.incidents') }}"
                    class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                    📋 Security Incidents
                </a>
                <a href="{{ route('admin.security.export') }}?type=anomalies&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    📊 Export Anomalies
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.security.anomalies') }}"
            class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="facility_id" class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                <select name="facility_id" id="facility_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id')==$facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity" id="severity"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Severities</option>
                    <option value="high" {{ request('severity')==='high' ? 'selected' : '' }}>High Risk</option>
                    <option value="medium" {{ request('severity')==='medium' ? 'selected' : '' }}>Medium Risk</option>
                    <option value="low" {{ request('severity')==='low' ? 'selected' : '' }}>Low Risk</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    🔍 Filter Results
                </button>
            </div>
        </form>
    </div>

    <!-- Anomaly Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-sm">🔥</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">High Risk</p>
                    <p class="text-2xl font-bold text-red-900">{{ $anomalies->where('severity', 'high')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 text-sm">⚠️</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-700">Medium Risk</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $anomalies->where('severity', 'medium')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 text-sm">ℹ️</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Low Risk</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $anomalies->where('severity', 'low')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 text-sm">📊</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-700">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $anomalies->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Anomalies List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">🔍 Detailed Anomaly Analysis</h3>
        </div>
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            @if($anomalies->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Severity
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Record
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Facility
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP Address
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Staff Email
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($anomalies as $anomaly)
                    <tr class="hover:bg-gray-50 
                                {{ $anomaly->severity === 'high' ? 'bg-red-25' : '' }}
                                {{ $anomaly->severity === 'medium' ? 'bg-yellow-25' : '' }}
                                {{ $anomaly->severity === 'low' ? 'bg-blue-25' : '' }}">

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($anomaly->severity === 'high')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔥 High Risk
                            </span>
                            @elseif($anomaly->severity === 'medium')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⚠️ Medium Risk
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ℹ️ Low Risk
                            </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $anomaly->access_time->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $anomaly->access_time->format('g:i A') }}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">{{ ucwords($anomaly->token_type) }}</div>
                            <div class="text-xs text-gray-500">#{{ $anomaly->record_id }}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $anomaly->facility->name ?? 'Unknown' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($anomaly->access_status === 'successful')
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✅ Successful
                            </span>
                            @elseif($anomaly->access_status === 'token_expired')
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⏰ Token Expired
                            </span>
                            @elseif($anomaly->access_status === 'invalid_token')
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🚫 Invalid Token
                            </span>
                            @elseif($anomaly->access_status === 'staff_verification_failed')
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                👤 Staff Verification Failed
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucwords(str_replace('_', ' ', $anomaly->access_status)) }}
                            </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                            {{ $anomaly->ip_address }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $anomaly->staff_email ?? 'N/A' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.security.record-logs', [$anomaly->token_type, $anomaly->record_id]) }}"
                                class="text-blue-600 hover:text-blue-900">View Details</a>

                            @if($anomaly->severity === 'high')
                            <span class="text-gray-300">|</span>
                            <button onclick="markAsInvestigated({{ $anomaly->id }})"
                                class="text-green-600 hover:text-green-900">Mark Investigated</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Layout -->
        <div class="md:hidden space-y-4">
            @foreach($anomalies as $anomaly)
            <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3 
                           {{ $anomaly->severity === 'high' ? 'border-red-200 bg-red-25' : '' }}
                           {{ $anomaly->severity === 'medium' ? 'border-yellow-200 bg-yellow-25' : '' }}
                           {{ $anomaly->severity === 'low' ? 'border-blue-200 bg-blue-25' : '' }}">

                <div class="flex items-center justify-between">
                    @if($anomaly->severity === 'high')
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        🔥 High Risk
                    </span>
                    @elseif($anomaly->severity === 'medium')
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ⚠️ Medium Risk
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ℹ️ Low Risk
                    </span>
                    @endif

                    @if($anomaly->access_status === 'successful')
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        ✅ Successful
                    </span>
                    @elseif($anomaly->access_status === 'token_expired')
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ⏰ Token Expired
                    </span>
                    @elseif($anomaly->access_status === 'invalid_token')
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        🚫 Invalid Token
                    </span>
                    @elseif($anomaly->access_status === 'staff_verification_failed')
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        👤 Staff Verification Failed
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ ucwords(str_replace('_', ' ', $anomaly->access_status)) }}
                    </span>
                    @endif
                </div>

                <div>
                    <h4 class="font-medium text-gray-900">{{ ucwords($anomaly->token_type) }}</h4>
                    <p class="text-sm text-gray-600">Record #{{ $anomaly->record_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-500">Time:</span>
                        <p class="text-gray-900">{{ $anomaly->access_time->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Facility:</span>
                        <p class="text-gray-900">{{ $anomaly->facility->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">IP Address:</span>
                        <p class="text-gray-900 font-mono text-xs">{{ $anomaly->ip_address }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Staff:</span>
                        <p class="text-gray-900 text-xs">{{ $anomaly->staff_email ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex flex-col space-y-2">
                    <a href="{{ route('admin.security.record-logs', [$anomaly->token_type, $anomaly->record_id]) }}"
                        class="inline-flex items-center justify-center px-3 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100">
                        View Details
                    </a>

                    @if($anomaly->severity === 'high')
                    <button onclick="markAsInvestigated({{ $anomaly->id }})"
                        class="inline-flex items-center justify-center px-3 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100">
                        Mark Investigated
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $anomalies->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <span class="text-6xl">✅</span>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Anomalies Found</h3>
            <p class="mt-1 text-gray-500">Great! No suspicious activities detected in the selected timeframe.</p>
        </div>
        @endif
    </div>
</div>

<!-- Risk Assessment Summary -->
@if($anomalies->count() > 0)
<div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Risk Assessment Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h4 class="font-medium text-red-900 mb-2">🔥 High Risk Indicators</h4>
            <ul class="text-sm text-red-700 space-y-1">
                <li>• Multiple failed attempts from same IP</li>
                <li>• Access outside business hours</li>
                <li>• Invalid or expired tokens</li>
                <li>• Staff verification failures</li>
            </ul>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-medium text-yellow-900 mb-2">⚠️ Medium Risk Indicators</h4>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Unusual access patterns</li>
                <li>• Multiple facility access</li>
                <li>• Repeated token usage</li>
                <li>• Geographic anomalies</li>
            </ul>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-900 mb-2">ℹ️ Low Risk Indicators</h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Normal access patterns</li>
                <li>• Successful staff verification</li>
                <li>• Business hours access</li>
                <li>• Expected user behavior</li>
            </ul>
        </div>
    </div>
</div>
@endif
</div>

<script>
    function markAsInvestigated(anomalyId) {
    if (confirm('Mark this anomaly as investigated? This will update the security log.')) {
        fetch(`/admin/security/anomalies/${anomalyId}/investigated`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating anomaly status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating anomaly status');
        });
    }
}
</script>
@endsection