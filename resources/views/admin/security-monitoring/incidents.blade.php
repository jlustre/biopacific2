@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📋 Security Incidents</h1>
                <p class="text-gray-600 mt-2">Critical security events requiring immediate attention</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.security.dashboard') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    ← Back to Dashboard
                </a>
                <a href="{{ route('admin.security.anomalies') }}"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    🚨 View Anomalies
                </a>
                <a href="{{ route('admin.security.export') }}?type=incidents&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    📊 Export Incidents
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.security.incidents') }}"
            class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    🔍 Filter Results
                </button>
            </div>
        </form>
    </div>

    <!-- Incident Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-sm">🚨</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-700">Critical Incidents</p>
                    <p class="text-2xl font-bold text-red-900">{{ $incidents->where('severity', 'critical')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-orange-600 text-sm">⚠️</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-orange-700">High Priority</p>
                    <p class="text-2xl font-bold text-orange-900">{{ $incidents->where('severity', 'high')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 text-sm">📊</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-700">Under Review</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $incidents->where('status',
                        'under_review')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-sm">✅</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Resolved</p>
                    <p class="text-2xl font-bold text-green-900">{{ $incidents->where('status', 'resolved')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Incidents Alert -->
    @if($incidents->where('severity', 'critical')->where('status', '!=', 'resolved')->count() > 0)
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="text-red-400 text-xl">🚨</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Critical Security Incidents Detected
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>
                        {{ $incidents->where('severity', 'critical')->where('status', '!=', 'resolved')->count() }}
                        critical security incidents require immediate attention. Please review and take appropriate
                        action.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Incidents List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">🔍 Security Incidents Report</h3>
        </div>
        <div class="overflow-x-auto">
            @if($incidents->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Severity
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Incident Type
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Detected
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Facility
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Count
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                IP Address
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Review
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($incidents as $incident)
                        <tr class="hover:bg-gray-50
                                {{ $incident['severity'] === 'critical' ? 'bg-red-25' : '' }}
                                {{ $incident['severity'] === 'high' ? 'bg-orange-25' : '' }}">

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($incident['severity'] === 'critical')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    🚨 Critical
                                </span>
                                @elseif($incident['severity'] === 'high')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    ⚠️ High
                                </span>
                                @elseif($incident['severity'] === 'medium')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    📊 Medium
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ℹ️ Low
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ ucwords(str_replace('_', ' ', $incident['type'])) }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $incident['description'] }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $incident['latest']->access_time->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $incident['latest']->access_time->format('g:i A')
                                    }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $incident['facility']->name ?? 'Multiple/Unknown' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(isset($incident['count']))
                                <div class="font-medium">{{ $incident['count'] }} attempts</div>
                                @elseif(isset($incident['access_count']))
                                <div class="font-medium">{{ $incident['access_count'] }} accesses</div>
                                @else
                                <div class="font-medium">1 incident</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                @if(isset($incident['ip_address']))
                                {{ $incident['ip_address'] }}
                                @else
                                {{ $incident['latest']->ip_address ?? 'Unknown' }}
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    🔓 Open
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <span class="text-gray-500">Review Required</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout -->
            <div class="md:hidden space-y-4">
                @foreach($incidents as $incident)
                <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        @if($incident['severity'] === 'critical')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            🚨 Critical
                        </span>
                        @elseif($incident['severity'] === 'high')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            ⚠️ High
                        </span>
                        @elseif($incident['severity'] === 'medium')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            📊 Medium
                        </span>
                        @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ℹ️ Low
                        </span>
                        @endif
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            🔓 Open
                        </span>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $incident['type'])) }}
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $incident['description'] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500">Detected:</span>
                            <p class="text-gray-900">{{ $incident['latest']->access_time->format('M j, Y g:i A') }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">Facility:</span>
                            <p class="text-gray-900">{{ $incident['facility']->name ?? 'Multiple/Unknown' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">Count:</span>
                            <p class="text-gray-900">
                                @if(isset($incident['count']))
                                {{ $incident['count'] }} attempts
                                @elseif(isset($incident['access_count']))
                                {{ $incident['access_count'] }} accesses
                                @else
                                1 incident
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500">IP:</span>
                            <p class="text-gray-900 font-mono text-xs">
                                @if(isset($incident['ip_address']))
                                {{ $incident['ip_address'] }}
                                @else
                                {{ $incident['latest']->ip_address ?? 'Unknown' }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <span class="text-sm text-gray-500">Review Required</span>
                    </div>
                </div>
                @endforeach
            </div>

            @else
            <div class="text-center py-12">
                <span class="text-6xl">🛡️</span>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No Security Incidents</h3>
                <p class="mt-1 text-gray-500">Excellent! No security incidents detected in the selected timeframe.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Response Guidelines -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Incident Response Guidelines</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">🚨 Critical Incidents</h4>
                <ul class="text-sm text-red-700 space-y-1">
                    <li>• Immediate investigation required</li>
                    <li>• Notify security team within 1 hour</li>
                    <li>• Document all findings</li>
                    <li>• Consider system lockdown if necessary</li>
                    <li>• Prepare breach notification if PHI involved</li>
                </ul>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h4 class="font-medium text-orange-900 mb-2">⚠️ High Priority</h4>
                <ul class="text-sm text-orange-700 space-y-1">
                    <li>• Review within 4 hours</li>
                    <li>• Analyze access patterns</li>
                    <li>• Check for related incidents</li>
                    <li>• Implement additional monitoring</li>
                    <li>• Update security measures</li>
                </ul>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">📊 Standard Protocol</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Review within 24 hours</li>
                    <li>• Log in incident report</li>
                    <li>• Monitor for escalation</li>
                    <li>• Update security policies</li>
                    <li>• Schedule follow-up review</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function viewIncidentDetails(incidentId) {
    // This would open a modal or navigate to detailed incident view
    alert('Incident details feature would open here for incident ID: ' + incidentId);
}

function markAsResolved(incidentId) {
    if (confirm('Mark this incident as resolved? This action will close the incident.')) {
        fetch(`/admin/security/incidents/${incidentId}/resolve`, {
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
                alert('Error updating incident status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating incident status');
        });
    }
}

function markUnderReview(incidentId) {
    if (confirm('Mark this incident as under review?')) {
        fetch(`/admin/security/incidents/${incidentId}/review`, {
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
                alert('Error updating incident status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating incident status');
        });
    }
}
</script>
@endsection