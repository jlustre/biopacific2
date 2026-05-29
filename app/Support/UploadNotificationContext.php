<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Support\Collection;

class UploadNotificationContext
{
    public static function resolveEmployeeEmail(BPEmployee $employee): ?string
    {
        foreach ([$employee->email, $employee->user?->email] as $candidate) {
            $email = trim((string) $candidate);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, User>
     */
    public static function facilityDsdUsers(Facility $facility): Collection
    {
        return User::query()
            ->role('facility-dsd')
            ->where(function ($query) use ($facility) {
                $query->where('facility_id', $facility->id)
                    ->orWhereHas('facilities', fn ($facilityQuery) => $facilityQuery->where('facilities.id', $facility->id));
            })
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * @return list<string>
     */
    public static function facilityDsdEmails(Facility $facility): array
    {
        return self::facilityDsdUsers($facility)
            ->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn (string $email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{upload: Upload, facility: Facility, email: string, expiryTier: string}
     */
    public static function resolve(Upload $upload, ?Facility $facility = null): array
    {
        $upload->load(['employee.user', 'facility', 'uploadType']);

        if (! $upload->employee) {
            abort(422, 'No employee is linked to this upload.');
        }

        $facility = $facility ?? $upload->facility;
        if (! $facility) {
            abort(422, 'No facility is linked to this upload.');
        }

        if ($upload->facility_id !== null && (int) $upload->facility_id !== (int) $facility->id) {
            abort(403, 'Upload does not belong to this facility.');
        }

        $email = self::resolveEmployeeEmail($upload->employee);
        if (! $email) {
            abort(422, 'Employee has no valid email address on file.');
        }

        $expiryTier = $upload->expiryNotificationTier();
        if ($expiryTier === null) {
            abort(403, 'Notifications can only be sent for documents with an expiry date within the next 120 days.');
        }

        return [
            'upload' => $upload,
            'facility' => $facility,
            'email' => $email,
            'expiryTier' => $expiryTier,
        ];
    }

    /**
     * @return array{upload: Upload, facility: Facility, emails: list<string>}
     */
    public static function resolveEmployeeSubmission(Upload $upload, BPEmployee $employee): array
    {
        $upload->load(['employee.user', 'facility', 'uploadType']);

        if ($upload->employee_num !== $employee->employee_num) {
            abort(403, 'This document does not belong to this employee.');
        }

        $facility = $upload->facility;
        if (! $facility) {
            abort(422, 'No facility is linked to this upload.');
        }

        $emails = self::facilityDsdEmails($facility);
        if ($emails === []) {
            abort(422, 'No DSD contact email is configured for this facility. Please contact your administrator.');
        }

        if (! $upload->canSubmitForVerification()) {
            abort(403, 'This document cannot be submitted for review at this time.');
        }

        return [
            'upload' => $upload,
            'facility' => $facility,
            'emails' => $emails,
        ];
    }
}
