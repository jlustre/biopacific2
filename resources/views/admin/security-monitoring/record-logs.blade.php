@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">🔍 Record Access Logs</h1>
                <p class="text-gray-600 mt-2">
                    Detailed access history for {{ ucwords($tokenType) }} Record #{{ $recordId }}
                    @if($record && $record->facility)
                    at {{ $record->facility->name }}
                    @endif
                </p>
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
                <a href="{{ route('admin.security.export') }}?type=record&token_type={{ $tokenType }}&record_id={{ $recordId }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    📊 Export Record Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Record Information -->
    @if($record)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Record Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Record Type</label>
                <p class="mt-1 text-sm text-gray-900">{{ ucwords($tokenType) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Record ID</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $recordId }}</p>
            </div>
            @if($record->facility)
            <div>
                <label class="block text-sm font-medium text-gray-700">Facility</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->facility->name }}</p>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700">Created Date</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->created_at->format('M j, Y g:i A') }}</p>
            </div>
            @if($tokenType === 'contact' && isset($record->name))
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->name }}</p>
            </div>
            @elseif($tokenType === 'tour_request' && isset($record->name))
            <div>
                <label class="block text-sm font-medium text-gray-700">Visitor Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->name }}</p>
            </div>
            @elseif($tokenType === 'job_application' && isset($record->first_name))
            <div>
                <label class="block text-sm font-medium text-gray-700">Applicant Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->first_name }} {{ $record->last_name }}</p>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700">Security Status</label>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $logs->where('access_status', 'successful')->count() > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $logs->where('access_status', 'successful')->count() > 0 ? '✅ Has Been Accessed' : '🔒 Never
                    Successfully Accessed' }}
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Access Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 text-sm">📊</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Attempts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $logs->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-sm">✅</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Successful</p>
                    <p class="text-2xl font-bold text-green-700">{{ $logs->where('access_status', 'successful')->count()
                        }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-sm">❌</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Failed</p>
                    <p class="text-2xl font-bold text-red-700">{{ $logs->whereIn('access_status', ['token_expired',
                        'invalid_token', 'staff_verification_failed'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 text-sm">🌐</span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Unique IPs</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $logs->pluck('ip_address')->unique()->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Access Timeline -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">⏱️ Access Timeline</h3>
        </div>
        <div class="p-6">
            @if($logs->count() > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($logs as $index => $log)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span
                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                {{ $log->access_status === 'successful' ? 'bg-green-500' : 
                                                   ($log->access_status === 'token_expired' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                        @if($log->access_status === 'successful')
                                        <span class="text-white text-xs">✓</span>
                                        @elseif($log->access_status === 'token_expired')
                                        <span class="text-white text-xs">⏰</span>
                                        @else
                                        <span class="text-white text-xs">✗</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-900">
                                            <strong>{{ ucwords(str_replace('_', ' ', $log->access_status)) }}</strong>
                                            @if($log->staff_email)
                                            • Staff: {{ $log->staff_email }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            IP: {{ $log->ip_address }} •
                                            User Agent: {{ Str::limit($log->user_agent, 60) }}
                                        </p>
                                        @if($log->notes)
                                        <p class="text-xs text-gray-600 mt-1">
                                            📝 {{ $log->notes }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time datetime="{{ $log->access_time }}">
                                            {{ $log->access_time->format('M j, Y') }}<br>
                                            <span class="text-xs">{{ $log->access_time->format('g:i:s A') }}</span>
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @else
            <div class="text-center py-8">
                <span class="text-4xl">📝</span>
                <p class="text-gray-500 mt-2">No access logs found for this record</p>
            </div>
            @endif
        </div>
    </div>

    <!-- IP Analysis for this Record -->
    @if($logs->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- IP Address Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🌐 IP Address Analysis</h3>
            </div>
            <div class="p-6">
                @php
                $ipStats = $logs->groupBy('ip_address')->map(function($ipLogs) {
                return [
                'total' => $ipLogs->count(),
                'successful' => $ipLogs->where('access_status', 'successful')->count(),
                'failed' => $ipLogs->whereIn('access_status', ['token_expired', 'invalid_token',
                'staff_verification_failed'])->count(),
                'first_seen' => $ipLogs->min('access_time'),
                'last_seen' => $ipLogs->max('access_time'),
                ];
                });
                @endphp

                @if($ipStats->count() > 0)
                <div class="space-y-4">
                    @foreach($ipStats as $ip => $stats)
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $ip }}</span>
                            <div class="flex space-x-2">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $stats['successful'] }} successful
                                </span>
                                @if($stats['failed'] > 0)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $stats['failed'] }} failed
                                </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            First seen: {{ Carbon\Carbon::parse($stats['first_seen'])->format('M j, Y g:i A') }} •
                            Last seen: {{ Carbon\Carbon::parse($stats['last_seen'])->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Security Assessment -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🛡️ Security Assessment</h3>
            </div>
            <div class="p-6">
                @php
                $successfulAccess = $logs->where('access_status', 'successful')->count();
                $failedAccess = $logs->whereIn('access_status', ['token_expired', 'invalid_token',
                'staff_verification_failed'])->count();
                $totalAccess = $logs->count();
                $uniqueIPs = $logs->pluck('ip_address')->unique()->count();
                $staffVerified = $logs->whereNotNull('staff_email')->where('access_status', 'successful')->count();

                $riskLevel = 'low';
                $riskReasons = [];

                if ($failedAccess > $successfulAccess && $failedAccess > 3) {
                $riskLevel = 'high';
                $riskReasons[] = 'High number of failed access attempts';
                }

                if ($uniqueIPs > 5) {
                $riskLevel = $riskLevel === 'high' ? 'high' : 'medium';
                $riskReasons[] = 'Multiple IP addresses accessing record';
                }

                if ($successfulAccess > 0 && $staffVerified === 0) {
                $riskLevel = 'high';
                $riskReasons[] = 'Successful access without staff verification';
                }

                if (empty($riskReasons)) {
                $riskReasons[] = 'Normal access patterns detected';
                }
                @endphp

                <div class="space-y-4">
                    <!-- Risk Level -->
                    <div
                        class="p-4 rounded-lg border
                            {{ $riskLevel === 'high' ? 'bg-red-50 border-red-200' : 
                               ($riskLevel === 'medium' ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200') }}">
                        <div class="flex items-center mb-2">
                            <span class="text-lg mr-2">
                                {{ $riskLevel === 'high' ? '🔥' : ($riskLevel === 'medium' ? '⚠️' : '✅') }}
                            </span>
                            <span class="font-medium 
                                    {{ $riskLevel === 'high' ? 'text-red-900' : 
                                       ($riskLevel === 'medium' ? 'text-yellow-900' : 'text-green-900') }}">
                                {{ ucwords($riskLevel) }} Risk Level
                            </span>
                        </div>
                        <ul class="text-sm 
                                {{ $riskLevel === 'high' ? 'text-red-700' : 
                                   ($riskLevel === 'medium' ? 'text-yellow-700' : 'text-green-700') }}">
                            @foreach($riskReasons as $reason)
                            <li class="mb-1">• {{ $reason }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Success Rate:</span>
                            <span class="font-medium text-gray-900">
                                {{ $totalAccess > 0 ? round(($successfulAccess / $totalAccess) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Staff Verified:</span>
                            <span class="font-medium text-gray-900">{{ $staffVerified }} accesses</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Unique IPs:</span>
                            <span class="font-medium text-gray-900">{{ $uniqueIPs }} addresses</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Attempts:</span>
                            <span class="font-medium text-gray-900">{{ $totalAccess }} attempts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection