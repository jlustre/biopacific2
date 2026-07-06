<?php

namespace App\Services;

use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompetencyAssessmentConfirmationService
{
    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(EmployeeCompetencyAssessment $assessment): array
    {
        $periodId = (int) $assessment->assessment_period_id;
        $employeeNum = (string) $assessment->employee_num;

        $ratings = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $periodId)
            ->where('assessment_type', 'competency')
            ->whereNull('revoked_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('item_key')
            ->map(fn ($entries) => (string) ($entries->first()?->rating ?? ''))
            ->sortKeys()
            ->all();

        $responses = is_array($assessment->responses) ? $assessment->responses : [];
        ksort($responses);

        $snapshotJson = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];

        return [
            'ratings' => $ratings,
            'responses' => $responses,
            'snapshot_json' => $snapshotJson,
            'overall_rating' => (string) ($assessment->overall_rating ?? ''),
            'total_score' => $assessment->total_score,
            'average_score' => $assessment->average_score !== null
                ? number_format((float) $assessment->average_score, 2, '.', '')
                : '',
            'review_date' => $this->formatSnapshotDate($assessment->review_date),
            'reviewer_name' => trim((string) ($assessment->reviewer_name ?? '')),
            'reviewer_title' => trim((string) ($assessment->reviewer_title ?? '')),
            'comments' => trim((string) ($assessment->comments ?? '')),
            'employee_comments' => trim((string) ($assessment->employee_comments ?? '')),
            'further_action_required' => trim((string) ($assessment->further_action_required ?? '')),
        ];
    }

    public function hasChangedSinceEmployeeConfirmation(EmployeeCompetencyAssessment $assessment): bool
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

    public function storeEmployeeConfirmationSnapshot(EmployeeCompetencyAssessment $assessment): void
    {
        $assessment->employee_confirmation_snapshot = $this->buildSnapshot($assessment);
    }

    public function resetForEmployeeReconfirmation(EmployeeCompetencyAssessment $assessment): void
    {
        if (filled($assessment->employee_signature_path)) {
            Storage::disk('public')->delete($assessment->employee_signature_path);
        }

        if (filled($assessment->reviewer_signature_path)) {
            Storage::disk('public')->delete($assessment->reviewer_signature_path);
        }

        app(CompetencyAssessmentPdfStorage::class)->deleteAllPdfs($assessment);

        $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;
        $assessment->employee_signed_at = null;
        $assessment->employee_signature_path = null;
        $assessment->employee_confirmation_snapshot = null;
        $assessment->reviewer_signed_at = null;
        $assessment->reviewer_signature_path = null;
        $assessment->review_date = null;
        $assessment->pdf_path = null;
        $assessment->pdf_generated_at = null;
        $assessment->completed_at = null;
    }

    public function prepareForEmployeeConfirmation(EmployeeCompetencyAssessment $assessment): bool
    {
        $needsReset = $assessment->workflowStatus() === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL
            || filled($assessment->employee_signature_path)
            || filled($assessment->employee_signed_at);

        if ($needsReset) {
            $this->resetForEmployeeReconfirmation($assessment);

            return true;
        }

        $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;

        return false;
    }

    public function storeEmployeeSignature(
        EmployeeCompetencyAssessment $assessment,
        ?string $dataUrl,
        ?UploadedFile $uploadedFile
    ): string {
        return $this->storeSignatureImage(
            $assessment,
            $dataUrl,
            $uploadedFile,
            'employee',
            $assessment->employee_signature_path
        );
    }

    public function storeReviewerSignature(
        EmployeeCompetencyAssessment $assessment,
        ?string $dataUrl,
        ?UploadedFile $uploadedFile
    ): string {
        return $this->storeSignatureImage(
            $assessment,
            $dataUrl,
            $uploadedFile,
            'reviewer',
            $assessment->reviewer_signature_path
        );
    }

    public function signaturePublicPath(?string $relativePath): ?string
    {
        if (! filled($relativePath) || ! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return Storage::disk('public')->path($relativePath);
    }

    protected function storeSignatureImage(
        EmployeeCompetencyAssessment $assessment,
        ?string $dataUrl,
        ?UploadedFile $uploadedFile,
        string $role,
        ?string $existingPath,
    ): string {
        $directory = 'competency-assessments/'.$assessment->employee_num.'/signatures';

        if ($uploadedFile) {
            if (filled($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: 'png');
            $filename = $role.'-'.Str::uuid().'.'.$extension;

            return $uploadedFile->storeAs($directory, $filename, 'public');
        }

        if (is_string($dataUrl) && preg_match('/^data:image\/(\w+);base64,/', $dataUrl)) {
            $extension = strtolower((string) preg_replace('/[^a-z]/', '', (string) preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches) ? $matches[1] : 'png'));
            $extension = $extension !== '' ? $extension : 'png';
            $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

            if ($binary === false) {
                throw new \InvalidArgumentException('Invalid signature image data.');
            }

            if (filled($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            $path = $directory.'/'.$role.'-'.Str::uuid().'.'.$extension;
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        throw new \InvalidArgumentException('A drawn or uploaded signature is required.');
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

        foreach (['ratings', 'responses'] as $key) {
            if (isset($snapshot[$key]) && is_array($snapshot[$key])) {
                ksort($snapshot[$key]);
            }
        }

        if (isset($snapshot['snapshot_json']) && is_array($snapshot['snapshot_json'])) {
            $snapshot['snapshot_json'] = $this->normalizeSnapshot($snapshot['snapshot_json']);
        }

        return $snapshot;
    }
}
