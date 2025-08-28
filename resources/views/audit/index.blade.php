@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        Audit Logs
                    </h2>
                    <p class="text-muted mb-0">System activity and security monitoring</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                    <button class="btn btn-success" onclick="exportLogs()">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-list text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Logs</div>
                            <div class="h4 mb-0" id="totalLogs">{{ number_format($logs->total()) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-day text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Today's Logs</div>
                            <div class="h4 mb-0" id="todayLogs">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Critical Logs</div>
                            <div class="h4 mb-0" id="criticalLogs">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-chart-line text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">This Week</div>
                            <div class="h4 mb-0" id="weekLogs">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filters & Search
                    </h6>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
        </div>
        <div class="collapse" id="filtersCollapse">
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search logs...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Action</label>
                            <select class="form-select" name="action">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Severity</label>
                            <select class="form-select" name="severity">
                                <option value="">All Severities</option>
                                @foreach($severities as $severity)
                                    <option value="{{ $severity }}" {{ request('severity') == $severity ? 'selected' : '' }}>
                                        {{ ucfirst($severity) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select class="form-select" name="user_name">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user }}" {{ request('user_name') == $user ? 'selected' : '' }}>
                                        {{ $user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Model</label>
                            <select class="form-select" name="model_type">
                                <option value="">All Models</option>
                                @foreach($models as $model)
                                    <option value="{{ $model }}" {{ request('model_type') == $model ? 'selected' : '' }}>
                                        {{ $model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i>
                                Clear Filters
                            </a>
                            <button type="button" class="btn btn-outline-info" onclick="setQuickFilter('today')">Today</button>
                            <button type="button" class="btn btn-outline-info ms-1" onclick="setQuickFilter('week')">This Week</button>
                            <button type="button" class="btn btn-outline-info ms-1" onclick="setQuickFilter('month')">This Month</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Audit Logs ({{ number_format($logs->total()) }} total)
                </h6>
                <div class="d-flex align-items-center">
                    <small class="text-muted me-3">
                        Showing {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}
                    </small>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                            <i class="fas fa-user fa-sm text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $log->user_name ?? 'System' }}</div>
                                            @if($log->user_id)
                                                <small class="text-muted">ID: {{ $log->user_id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->action_color }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->model_type)
                                        <div class="fw-medium">{{ $log->model_name }}</div>
                                        @if($log->model_id)
                                            <small class="text-muted">ID: {{ $log->model_id }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $log->description }}">
                                        {{ $log->description }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->severity_color }}">
                                        {{ ucfirst($log->severity) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails({{ $log->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>No audit logs found</div>
                                        <small>Try adjusting your filters</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-transparent border-0">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('meta')
<script>
// Auto-refresh stats every 30 seconds
let statsInterval;

document.addEventListener('DOMContentLoaded', function() {
    refreshStats();
    statsInterval = setInterval(refreshStats, 30000);
});

function refreshStats() {
    fetch('/audit/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalLogs').textContent = data.total_logs.toLocaleString();
            document.getElementById('todayLogs').textContent = data.today_logs.toLocaleString();
            document.getElementById('criticalLogs').textContent = data.critical_logs.toLocaleString();
            document.getElementById('weekLogs').textContent = data.recent_actions.reduce((sum, action) => sum + action.count, 0).toLocaleString();
        })
        .catch(error => console.error('Error fetching stats:', error));
}

function viewLogDetails(logId) {
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    const content = document.getElementById('logDetailsContent');

    content.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    modal.show();

    fetch(`/audit/${logId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading log details</div>';
        });
}

function setQuickFilter(period) {
    const form = document.getElementById('filterForm');
    const dateFrom = form.querySelector('[name="date_from"]');
    const dateTo = form.querySelector('[name="date_to"]');

    const today = new Date();
    let fromDate = new Date();

    switch(period) {
        case 'today':
            fromDate = today;
            break;
        case 'week':
            fromDate.setDate(today.getDate() - 7);
            break;
        case 'month':
            fromDate.setMonth(today.getMonth() - 1);
            break;
    }

    dateFrom.value = fromDate.toISOString().split('T')[0];
    dateTo.value = today.toISOString().split('T')[0];

    form.submit();
}

function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location = url;
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    const exportUrl = '/audit/export?' + params.toString();

    fetch(exportUrl, { method: 'POST' })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'audit_logs_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            alert('Export failed. Please try again.');
        });
}

// Clear interval when page unloads
window.addEventListener('beforeunload', function() {
    if (statsInterval) {
        clearInterval(statsInterval);
    }
});
</script>
@endpush
