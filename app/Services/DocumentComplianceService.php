<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DocumentComplianceService
{
    /**
     * @return array{position_id:int|null, position_title:string|null, department_id:int|null, items:Collection<int, array<string,mixed>>, summary:array<string,int>}
     */
    public function forEmployee(BPEmployee $employee): array
    {
        $assignment = $employee->currentAssignment;
        $position = $assignment?->position;
        $departmentId = $assignment?->dept_id ? (int) $assignment->dept_id : null;

        if (! $position) {
            return [
                'position_id' => null,
                'position_title' => null,
                'department_id' => $departmentId,
                'items' => collect(),
                'summary' => [
                    'total' => 0,
                    'complete' => 0,
                    'expired' => 0,
                    'missing' => 0,
                ],
            ];
        }

        $requiredTypes = $position->requiredUploadTypes()
            ->wherePivot('is_required', true)
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->where(function ($scope) use ($departmentId) {
                    $scope->whereNull('department_ids')
                        ->orWhereJsonLength('department_ids', 0)
                        ->orWhereJsonContains('department_ids', $departmentId);
                });
            }, function ($query) {
                $query->where(function ($scope) {
                    $scope->whereNull('department_ids')
                        ->orWhereJsonLength('department_ids', 0);
                });
            })
            ->orderBy('name')
            ->get();

        $uploadsByType = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereIn('upload_type_id', $requiredTypes->pluck('id'))
            ->orderByDesc('uploaded_at')
            ->get()
            ->groupBy('upload_type_id');

        $today = Carbon::today();

        $items = $requiredTypes->map(function ($type) use ($uploadsByType, $today) {
            $uploads = $uploadsByType->get($type->id, collect());
            $latestUpload = $uploads->first();

            $validUpload = $uploads->first(function ($upload) use ($today) {
                if ($upload->expires_at === null) {
                    return true;
                }

                return Carbon::parse($upload->expires_at)->startOfDay()->gte($today);
            });

            $status = 'missing';
            if ($validUpload) {
                $status = 'complete';
            } elseif ($uploads->isNotEmpty()) {
                $status = 'expired';
            }

            $daysToExpiry = null;
            if ($validUpload && $validUpload->expires_at) {
                $daysToExpiry = $today->diffInDays(Carbon::parse($validUpload->expires_at)->startOfDay(), false);
            }

            return [
                'upload_type_id' => (int) $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'requires_expiry' => (bool) $type->requires_expiry,
                'is_license_or_certification' => (bool) ($type->is_license_or_certification ?? false),
                'status' => $status,
                'latest_uploaded_at' => optional($latestUpload?->uploaded_at)->toDateString(),
                'latest_expires_at' => optional($latestUpload?->expires_at)->toDateString(),
                'valid_upload_id' => $validUpload?->id,
                'days_to_expiry' => $daysToExpiry,
            ];
        })->values();

        return [
            'position_id' => (int) $position->id,
            'position_title' => $position->title,
            'department_id' => $departmentId,
            'items' => $items,
            'summary' => [
                'total' => $items->count(),
                'complete' => $items->where('status', 'complete')->count(),
                'expired' => $items->where('status', 'expired')->count(),
                'missing' => $items->where('status', 'missing')->count(),
            ],
        ];
    }
}
