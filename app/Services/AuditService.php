<?php
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(array $data): AuditLog
    {
        $request = request();

        $auditData = array_merge([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Guest',
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
            'severity' => 'low'
        ], $data);

        return AuditLog::create($auditData);
    }

    public static function logModelEvent($model, string $action, array $oldValues = [], array $newValues = []): AuditLog
    {
        return self::log([
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => ucfirst($action) . ' ' . class_basename($model) . ' #' . ($model->id ?? 'new'),
            'severity' => $action === 'deleted' ? 'high' : 'medium'
        ]);
    }

    public static function logUserAction(string $action, string $description = null, string $severity = 'low'): AuditLog
    {
        return self::log([
            'action' => $action,
            'description' => $description ?? ucfirst($action) . ' action performed',
            'severity' => $severity
        ]);
    }

    public static function logSecurityEvent(string $action, string $description, string $severity = 'high'): AuditLog
    {
        return self::log([
            'action' => $action,
            'description' => $description,
            'severity' => $severity,
            'tags' => ['security']
        ]);
    }
}
