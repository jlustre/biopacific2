<?php

namespace App\Mail;

use App\Models\Facility;
use App\Models\Upload;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacilityUploadNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Upload $upload,
        public Facility $facility,
        public ?User $sentBy = null,
        public string $expiryTier = 'soon',
        public ?string $customSubject = null,
        public ?string $customMessage = null,
    ) {
    }

    public function build(): self
    {
        $viewData = self::viewData($this->upload, $this->facility, $this->sentBy, $this->expiryTier);
        $subject = trim((string) $this->customSubject) !== ''
            ? trim((string) $this->customSubject)
            : self::defaultSubject($this->upload, $this->facility, $this->expiryTier);
        $customMessage = trim((string) $this->customMessage) !== ''
            ? trim((string) $this->customMessage)
            : null;

        return $this
            ->subject($subject)
            ->markdown('emails.facility_upload_notification', array_merge($viewData, [
                'customMessage' => $customMessage,
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(Upload $upload, Facility $facility, ?User $sentBy, string $expiryTier): array
    {
        $upload = $upload->loadMissing(['uploadType', 'employee']);
        $typeName = $upload->uploadType?->name ?? 'Document';
        $facilityName = $facility->name ?? config('app.name');
        $employeeName = $upload->employee
            ? trim(($upload->employee->first_name ?? '') . ' ' . ($upload->employee->last_name ?? ''))
            : 'Employee';

        $expiresAt = $upload->expires_at
            ? Carbon::parse($upload->expires_at)->format('F j, Y')
            : null;
        $expiryDateClause = $expiresAt
            ? match ($expiryTier) {
                'expired' => " (expired {$expiresAt})",
                default => " (expires {$expiresAt})",
            }
            : '';

        return [
            'employeeName' => $employeeName ?: 'Employee',
            'facilityName' => $facilityName,
            'documentType' => $typeName,
            'fileName' => $upload->original_filename,
            'expiresAt' => $expiresAt,
            'effectiveStart' => $upload->effective_start_date
                ? Carbon::parse($upload->effective_start_date)->format('F j, Y')
                : null,
            'comments' => $upload->comments,
            'sentByName' => $sentBy?->name,
            'memberDocumentsUrl' => route('member.documents'),
            'expiryTier' => $expiryTier,
            'expiryDateClause' => $expiryDateClause,
        ];
    }

    public static function defaultSubject(Upload $upload, Facility $facility, string $expiryTier): string
    {
        $upload = $upload->loadMissing(['uploadType']);
        $typeName = $upload->uploadType?->name ?? 'Document';
        $facilityName = $facility->name ?? config('app.name');

        $subjectCore = match ($expiryTier) {
            'expired' => "Action Required: Expired Document – {$typeName}",
            'urgent' => "Urgent: Document Expiring Soon – {$typeName}",
            default => "Reminder: Document Expiring – {$typeName}",
        };

        return "{$subjectCore} at {$facilityName}";
    }

    public static function defaultMessage(Upload $upload, Facility $facility, string $expiryTier): string
    {
        $data = self::viewData($upload, $facility, null, $expiryTier);
        $employeeName = $data['employeeName'];
        $documentType = $data['documentType'];
        $facilityName = $data['facilityName'];
        $expiryDateClause = $data['expiryDateClause'];

        return match ($expiryTier) {
            'expired' => "Hello {$employeeName},\n\n"
                . "Your {$documentType} document at {$facilityName} has expired{$expiryDateClause}. "
                . 'Please renew or replace this document as soon as possible.',
            'urgent' => "Hello {$employeeName},\n\n"
                . "Your {$documentType} document at {$facilityName} is expiring within the next 30 days{$expiryDateClause}. "
                . 'Please take action promptly to avoid a lapse.',
            default => "Hello {$employeeName},\n\n"
                . "Your {$documentType} document at {$facilityName} will expire within the next 120 days{$expiryDateClause}. "
                . 'Please plan ahead to renew or replace it before the expiration date.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function previewPayload(
        Upload $upload,
        Facility $facility,
        ?User $sentBy,
        string $expiryTier,
        string $recipientEmail,
    ): array {
        $upload = $upload->loadMissing(['uploadType', 'employee']);
        $viewData = self::viewData($upload, $facility, $sentBy, $expiryTier);

        return [
            'to' => $recipientEmail,
            'subject' => self::defaultSubject($upload, $facility, $expiryTier),
            'message' => self::defaultMessage($upload, $facility, $expiryTier),
            'expiry_tier' => $expiryTier,
            'expiry_tier_label' => match ($expiryTier) {
                'expired' => 'Expired',
                'urgent' => 'Expiring within 30 days',
                default => 'Expiring within 120 days',
            },
            'employee_name' => $viewData['employeeName'],
            'document_type' => $viewData['documentType'],
            'file_name' => $viewData['fileName'],
            'expires_at' => $viewData['expiresAt'],
            'effective_start' => $viewData['effectiveStart'],
            'facility_name' => $viewData['facilityName'],
        ];
    }
}
