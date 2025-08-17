<div class="row">
    <div class="col-12">
        <!-- Basic Information -->
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-primary mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Basic Information
                </h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 30%;">ID:</td>
                        <td class="fw-medium">#{{ $auditLog->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Timestamp:</td>
                        <td class="fw-medium">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Action:</td>
                        <td>
                            <span class="badge bg-{{ $auditLog->action_color }}">
                                {{ ucfirst($auditLog->action) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Severity:</td>
                        <td>
                            <span class="badge bg-{{ $auditLog->severity_color }}">
                                {{ ucfirst($auditLog->severity) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary mb-2">
                    <i class="fas fa-user me-1"></i>
                    User Information
                </h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 30%;">User:</td>
                        <td class="fw-medium">{{ $auditLog->user_name ?? 'System' }}</td>
                    </tr>
                    @if($auditLog->user_id)
                    <tr>
                        <td class="text-muted">User ID:</td>
                        <td class="fw-medium">{{ $auditLog->user_id }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">IP Address:</td>
                        <td class="fw-medium">{{ $auditLog->ip_address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">URL:</td>
                        <td class="fw-medium text-break">{{ $auditLog->url ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Model Information -->
        @if($auditLog->model_type)
        <div class="mb-3">
            <h6 class="text-primary mb-2">
                <i class="fas fa-database me-1"></i>
                Model Information
            </h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="text-muted" style="width: 15%;">Model Type:</td>
                    <td class="fw-medium">{{ $auditLog->model_name }}</td>
                </tr>
                @if($auditLog->model_id)
                <tr>
                    <td class="text-muted">Model ID:</td>
                    <td class="fw-medium">{{ $auditLog->model_id }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <!-- Description -->
        @if($auditLog->description)
        <div class="mb-3">
            <h6 class="text-primary mb-2">
                <i class="fas fa-comment me-1"></i>
                Description
            </h6>
            <div class="bg-light p-3 rounded">
                {{ $auditLog->description }}
            </div>
        </div>
        @endif

        <!-- Changes -->
        @if($auditLog->old_values || $auditLog->new_values)
        <div class="mb-3">
            <h6 class="text-primary mb-2">
                <i class="fas fa-exchange-alt me-1"></i>
                Changes
            </h6>
            <div class="row">
                @if($auditLog->old_values)
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Old Values</h6>
                    <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <pre class="mb-0 small">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
                @if($auditLog->new_values)
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">New Values</h6>
                    <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <pre class="mb-0 small">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Technical Details -->
        <div class="mb-3">
            <h6 class="text-primary mb-2">
                <i class="fas fa-cogs me-1"></i>
                Technical Details
            </h6>
            <table class="table table-sm table-borderless">
                @if($auditLog->user_agent)
                <tr>
                    <td class="text-muted" style="width: 15%;">User Agent:</td>
                    <td class="fw-medium text-break small">{{ $auditLog->user_agent }}</td>
                </tr>
                @endif
                @if($auditLog->tags)
                <tr>
                    <td class="text-muted">Tags:</td>
                    <td>
                        @foreach($auditLog->tags as $tag)
                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                        @endforeach
                    </td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Created At:</td>
                    <td class="fw-medium">{{ $auditLog->created_at->format('Y-m-d H:i:s T') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Time Ago:</td>
                    <td class="fw-medium">{{ $auditLog->created_at->diffForHumans() }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
