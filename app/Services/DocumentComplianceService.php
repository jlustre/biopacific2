<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DocumentComplianceService
{
    public function __construct(
        protected EmployeeDocumentRequirementsService $requirements
    ) {
    }

    /**
     * @return array{position_id:int|null, position_title:string|null, department_id:int|null, items:Collection<int, array<string,mixed>>, summary:array<string,int>}
     */
    public function forEmployee(BPEmployee $employee): array
    {
        $assignment = $employee->currentAssignment;
        $position = $assignment?->position;
        $departmentId = $assignment?->dept_id ? (int) $assignment->dept_id : null;

        // All-employees types always apply; position-specific types need an assignment position.
        $requiredTypes = $position
            ? $this->requirements->requiredGeneralUploadTypesForPosition($position, $departmentId)
            : $this->requirements->catalogUploadTypesForEmployee($employee);

        $uploadsByType = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->current()
            ->whereIn('upload_type_id', $requiredTypes->pluck('id'))
            ->orderByDesc('uploaded_at')
            ->get()
            ->groupBy('upload_type_id');

        $today = Carbon::today();

        $items = $requiredTypes->map(function ($type) use ($uploadsByType, $today) {
            $uploads = $uploadsByType->get($type->id, collect());
            $latestUpload = $uploads->first();
            $evaluation = $this->evaluateUploads($uploads, $today);
            $validApprovedUpload = $evaluation['valid_approved_upload'];
            $status = $evaluation['status'];

            $daysToExpiry = null;
            $dueAt = null;
            $daysUntilDue = null;
            if ($validApprovedUpload && $validApprovedUpload->expires_at) {
                $expiresAt = Carbon::parse($validApprovedUpload->expires_at)->startOfDay();
                $daysToExpiry = $today->diffInDays($expiresAt, false);
                $dueAt = \App\Support\ComplianceDueDate::forExpiration($expiresAt);
                $daysUntilDue = $dueAt ? $today->diffInDays($dueAt, false) : null;
            }

            $referenceUpload = $validApprovedUpload ?? $latestUpload;

            return [
                'upload_type_id' => (int) $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'requires_expiry' => (bool) $type->requires_expiry,
                'is_license_or_certification' => (bool) ($type->is_license_or_certification ?? false),
                'status' => $status,
                'latest_uploaded_at' => optional($latestUpload?->uploaded_at)->toDateString(),
                'latest_expires_at' => optional($referenceUpload?->expires_at)->toDateString(),
                'due_at' => $dueAt?->toDateString(),
                'valid_upload_id' => $validApprovedUpload?->id,
                'latest_upload_id' => $latestUpload?->id,
                'verification_notes' => $latestUpload?->verification_notes,
                'days_to_expiry' => $daysToExpiry,
                'days_until_due' => $daysUntilDue,
            ];
        })->values();

        return [
            'position_id' => $position ? (int) $position->id : null,
            'position_title' => $position?->title,
            'department_id' => $departmentId,
            'items' => $items,
            'summary' => [
                'total' => $items->count(),
                'complete' => $items->where('status', 'complete')->count(),
                'expired' => $items->where('status', 'expired')->count(),
                'missing' => $items->whereIn('status', ['missing', 'rejected', 'pending_review'])->count(),
                'pending_review' => $items->where('status', 'pending_review')->count(),
                'rejected' => $items->where('status', 'rejected')->count(),
            ],
        ];
    }

    /**
     * Newer pending/rejected submissions remain outstanding even when an older
     * approved upload for another coverage year is still current.
     *
     * @return array{status:string, valid_approved_upload:?Upload}
     */
    public function evaluateUploads(Collection $uploads, ?Carbon $today = null): array
    {
        $today ??= Carbon::today();
        $latestUpload = $uploads->first();
        $validApprovedUpload = $uploads->first(function (Upload $upload) use ($today): bool {
            if ($upload->verification_status !== Upload::VERIFICATION_APPROVED) {
                return false;
            }

            return $upload->expires_at === null
                || Carbon::parse($upload->expires_at)->startOfDay()->gte($today);
        });

        $status = match (true) {
            $latestUpload?->verification_status === Upload::VERIFICATION_PENDING => 'pending_review',
            $latestUpload?->verification_status === Upload::VERIFICATION_REJECTED => 'rejected',
            $validApprovedUpload instanceof Upload => 'complete',
            $uploads->contains(fn (Upload $upload) => $upload->verification_status === Upload::VERIFICATION_APPROVED) => 'expired',
            default => 'missing',
        };

        return [
            'status' => $status,
            'valid_approved_upload' => $validApprovedUpload,
        ];
    }
}
