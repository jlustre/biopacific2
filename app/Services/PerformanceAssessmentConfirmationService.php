<?php

namespace App\Services;

use App\Models\DocType;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeePerformanceAssessment;
use App\Models\EmployeePerformanceSectionComment;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PerformanceAssessmentConfirmationService
{
    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(EmployeePerformanceAssessment $assessment): array
    {
        $periodId = (int) $assessment->assessment_period_id;
        $employeeNum = (string) $assessment->employee_num;

        $ratings = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $periodId)
            ->where('assessment_type', 'performance')
            ->whereNull('revoked_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('item_key')
            ->map(fn ($entries) => (string) ($entries->first()?->rating ?? ''))
            ->sortKeys()
            ->all();

        $docTypesByName = DocType::query()
            ->whereIn('name', [
                'Areas Requiring Further Development',
                'Development Plans',
                'Employee Comments',
            ])
            ->pluck('id', 'name');

        $commentsByDocTypeId = EmployeePerformanceSectionComment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $periodId)
            ->whereIn('doc_type_id', $docTypesByName->values())
            ->pluck('comment', 'doc_type_id');

        $sections = [];
        foreach ($docTypesByName as $name => $docTypeId) {
            $sections[$name] = trim((string) ($commentsByDocTypeId->get($docTypeId) ?? ''));
        }

        return [
            'ratings' => $ratings,
            'sections' => $sections,
            'overall_rating' => (string) ($assessment->overall_rating ?? ''),
            'total_score' => $assessment->total_score,
            'average_score' => $assessment->average_score !== null
                ? number_format((float) $assessment->average_score, 2, '.', '')
                : '',
            'review_dt' => $this->formatSnapshotDate($assessment->review_dt),
            'reviewer_name' => trim((string) ($assessment->reviewer_name ?? '')),
            'comments' => trim((string) ($assessment->comments ?? '')),
        ];
    }

    public function hasChangedSinceEmployeeConfirmation(EmployeePerformanceAssessment $assessment): bool
    {
        if ($assessment->workflowStatus() !== AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            return false;
        }

        $stored = $assessment->employee_confirmation_snapshot;
        if (! is_array($stored) || $stored === []) {
            return false;
        }

        return $this->snapshotFingerprint($stored) !== $this->snapshotFingerprint(
            $this->buildSnapshot($assessment->fresh())
        );
    }

    public function storeEmployeeConfirmationSnapshot(EmployeePerformanceAssessment $assessment): void
    {
        $assessment->employee_confirmation_snapshot = $this->buildSnapshot($assessment);
    }

    public function resetForEmployeeReconfirmation(EmployeePerformanceAssessment $assessment): void
    {
        if (filled($assessment->employee_signature_path)) {
            Storage::disk('public')->delete($assessment->employee_signature_path);
        }

        $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;
        $assessment->acknowledge_dt = null;
        $assessment->employee_signature_path = null;
        $assessment->employee_confirmation_snapshot = null;
        $assessment->syncFinalizedFromStatus();
    }

    public function prepareForEmployeeConfirmation(EmployeePerformanceAssessment $assessment): bool
    {
        $needsReset = $assessment->workflowStatus() === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL
            || filled($assessment->employee_signature_path)
            || filled($assessment->acknowledge_dt);

        if ($needsReset) {
            $this->resetForEmployeeReconfirmation($assessment);

            return true;
        }

        $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;
        $assessment->syncFinalizedFromStatus();

        return false;
    }

    public function storeEmployeeSignature(
        EmployeePerformanceAssessment $assessment,
        ?string $dataUrl,
        ?UploadedFile $uploadedFile
    ): string {
        $directory = 'performance-assessments/'.$assessment->employee_num.'/signatures';

        if ($uploadedFile) {
            if (filled($assessment->employee_signature_path)) {
                Storage::disk('public')->delete($assessment->employee_signature_path);
            }

            return $uploadedFile->store($directory, 'public');
        }

        if (is_string($dataUrl) && preg_match('/^data:image\/(\w+);base64,/', $dataUrl)) {
            $extension = strtolower((string) preg_replace('/[^a-z]/', '', (string) preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches) ? $matches[1] : 'png'));
            $extension = $extension !== '' ? $extension : 'png';
            $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

            if ($binary === false) {
                throw new \InvalidArgumentException('Invalid signature image data.');
            }

            if (filled($assessment->employee_signature_path)) {
                Storage::disk('public')->delete($assessment->employee_signature_path);
            }

            $path = $directory.'/assessment-'.$assessment->id.'-'.time().'.'.$extension;
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        throw new \InvalidArgumentException('A drawn or uploaded signature is required.');
    }

    public function signaturePublicPath(?string $relativePath): ?string
    {
        if (! filled($relativePath) || ! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return Storage::disk('public')->path($relativePath);
    }

    protected function formatSnapshotDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return Carbon::parse((string) $value)->format('Y-m-d');
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected function snapshotFingerprint(array $snapshot): string
    {
        return hash('sha256', json_encode($this->normalizeSnapshot($snapshot)));
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    protected function normalizeSnapshot(array $snapshot): array
    {
        ksort($snapshot);

        if (isset($snapshot['ratings']) && is_array($snapshot['ratings'])) {
            ksort($snapshot['ratings']);
        }

        if (isset($snapshot['sections']) && is_array($snapshot['sections'])) {
            ksort($snapshot['sections']);
        }

        return $snapshot;
    }
}
