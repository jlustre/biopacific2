<?php

namespace App\Services;

use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DocumentUploadLifecycleService
{
    /**
     * Coverage year for annual/document tracking:
     * prefers expiration year, then effective start year, then upload year.
     */
    public function coverageYearFor(?Carbon $expiresAt, ?Carbon $effectiveStart, ?Carbon $uploadedAt = null): int
    {
        if ($expiresAt) {
            return (int) $expiresAt->year;
        }

        if ($effectiveStart) {
            return (int) $effectiveStart->year;
        }

        return (int) ($uploadedAt ?? now())->year;
    }

    public function refreshCoverageYear(Upload $upload): Upload
    {
        $upload->coverage_year = $this->coverageYearFor(
            $upload->expires_at ? Carbon::parse($upload->expires_at) : null,
            $upload->effective_start_date ? Carbon::parse($upload->effective_start_date) : null,
            $upload->uploaded_at ? Carbon::parse($upload->uploaded_at) : now(),
        );
        $upload->saveQuietly();

        return $upload;
    }

    /**
     * After a new current upload is saved, archive prior same-type records for the same coverage year
     * (or all prior currents for non-expiring types). Preserves old expiration dates on archived rows.
     */
    public function supersedePriorCurrents(Upload $current): Collection
    {
        $current->loadMissing('uploadType');
        $this->refreshCoverageYear($current);

        $query = Upload::query()
            ->where('employee_num', $current->employee_num)
            ->whereNull('superseded_at')
            ->where('id', '!=', $current->id);

        if ($current->upload_type_id) {
            $query->where('upload_type_id', $current->upload_type_id);
        } elseif ($current->checklist_item_id) {
            $query->where('checklist_item_id', $current->checklist_item_id);
        } else {
            return collect();
        }

        // Annual / expiring documents: one current entry per coverage year.
        // Non-expiring: only one active current row for that type overall.
        $requiresExpiry = (bool) ($current->uploadType?->requires_expiry);
        if ($requiresExpiry || $current->expires_at) {
            $query->where('coverage_year', $current->coverage_year);
        }

        $prior = $query->get();

        foreach ($prior as $old) {
            $old->forceFill([
                'superseded_at' => now(),
                'superseded_by_upload_id' => $current->id,
            ])->saveQuietly();
        }

        return $prior;
    }

    /**
     * When updating a current document and the expiration/file changes, preserve a history
     * snapshot of the previous version, then keep the updated row as current.
     */
    public function preservePreviousVersionBeforeUpdate(Upload $upload, array $previousAttributes): ?Upload
    {
        $oldExpires = $previousAttributes['expires_at'] ?? null;
        $newExpires = $upload->expires_at;

        $oldExpiresStr = $oldExpires ? Carbon::parse($oldExpires)->toDateString() : null;
        $newExpiresStr = $newExpires ? Carbon::parse($newExpires)->toDateString() : null;
        $fileChanged = (($previousAttributes['file_path'] ?? null) !== $upload->file_path);

        if ($oldExpiresStr === $newExpiresStr && ! $fileChanged) {
            return null;
        }

        $snapshot = $upload->replicate([
            'superseded_at',
            'superseded_by_upload_id',
        ]);
        $snapshot->fill([
            'expires_at' => $oldExpires,
            'effective_start_date' => $previousAttributes['effective_start_date'] ?? $upload->effective_start_date,
            'file_path' => $previousAttributes['file_path'] ?? $upload->file_path,
            'original_filename' => $previousAttributes['original_filename'] ?? $upload->original_filename,
            'file_size' => $previousAttributes['file_size'] ?? $upload->file_size,
            'comments' => $previousAttributes['comments'] ?? $upload->comments,
            'uploaded_at' => $previousAttributes['uploaded_at'] ?? $upload->uploaded_at,
            'submission_reason' => $previousAttributes['submission_reason'] ?? $upload->submission_reason,
            'verification_status' => $previousAttributes['verification_status'] ?? $upload->verification_status,
            'submitted_for_review_at' => $previousAttributes['submitted_for_review_at'] ?? $upload->submitted_for_review_at,
            'verified_by_user_id' => $previousAttributes['verified_by_user_id'] ?? $upload->verified_by_user_id,
            'verified_at' => $previousAttributes['verified_at'] ?? $upload->verified_at,
            'verification_notes' => $previousAttributes['verification_notes'] ?? $upload->verification_notes,
            'user_id' => $previousAttributes['user_id'] ?? $upload->user_id,
            'superseded_at' => now(),
            'superseded_by_upload_id' => $upload->id,
        ]);
        $snapshot->coverage_year = $this->coverageYearFor(
            $oldExpires ? Carbon::parse($oldExpires) : null,
            isset($previousAttributes['effective_start_date']) && $previousAttributes['effective_start_date']
                ? Carbon::parse($previousAttributes['effective_start_date'])
                : null,
            isset($previousAttributes['uploaded_at']) && $previousAttributes['uploaded_at']
                ? Carbon::parse($previousAttributes['uploaded_at'])
                : null,
        );
        $snapshot->saveQuietly();

        $this->refreshCoverageYear($upload);

        return $snapshot;
    }

    /**
     * Soft-archive a current upload (keep history, remove from active listing).
     */
    public function archiveCurrent(Upload $upload): Upload
    {
        $upload->forceFill([
            'superseded_at' => $upload->superseded_at ?? now(),
        ])->saveQuietly();

        return $upload;
    }

    /**
     * History rows for the same employee + type (+ optional same coverage year).
     *
     * @return Collection<int, Upload>
     */
    public function historyFor(Upload $current, bool $sameYearOnly = true): Collection
    {
        $query = Upload::query()
            ->where('employee_num', $current->employee_num)
            ->whereNotNull('superseded_at')
            ->where('id', '!=', $current->id)
            ->with(['uploadType', 'checklistItem'])
            ->orderByDesc('uploaded_at')
            ->orderByDesc('id');

        if ($current->upload_type_id) {
            $query->where('upload_type_id', $current->upload_type_id);
        } elseif ($current->checklist_item_id) {
            $query->where('checklist_item_id', $current->checklist_item_id);
        } else {
            return collect();
        }

        if ($sameYearOnly && $current->coverage_year) {
            $query->where('coverage_year', $current->coverage_year);
        }

        return $query->get();
    }
}
