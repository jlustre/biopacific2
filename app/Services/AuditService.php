<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Facility;
use App\Models\User;
use App\Support\SelectedFacility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
            'severity' => 'low',
        ], $data);

        if (Schema::hasColumn('audit_logs', 'facility_id') && ! array_key_exists('facility_id', $auditData)) {
            $auditData['facility_id'] = self::resolveFacilityId();
        }

        return AuditLog::create($auditData);
    }

    protected static function resolveFacilityId(): ?int
    {
        if ($id = SelectedFacility::id()) {
            return $id;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return self::defaultFacilityId();
        }

        if ($user->facility_id) {
            return (int) $user->facility_id;
        }

        if (method_exists($user, 'resolvedBpEmployee')) {
            $employee = $user->resolvedBpEmployee(['currentAssignment.facility']);
            $facilityId = $employee?->currentAssignment?->facility_id;
            if ($facilityId) {
                return (int) $facilityId;
            }
        }

        return self::defaultFacilityId();
    }

    protected static function defaultFacilityId(): ?int
    {
        $id = Facility::query()->orderBy('id')->value('id');

        return $id ? (int) $id : null;
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
