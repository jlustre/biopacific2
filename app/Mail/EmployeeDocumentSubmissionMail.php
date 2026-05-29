<?php

namespace App\Mail;

use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\User;
use App\Support\UploadSubmissionReason;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeDocumentSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Upload $upload,
        public BPEmployee $employee,
        public Facility $facility,
        public User $submittedBy,
        public string $submissionReason,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
    ) {
    }

    public function build(): self
    {
        $viewData = self::viewData(
            $this->upload,
            $this->employee,
            $this->facility,
            $this->submittedBy,
            $this->submissionReason,
        );

        $subject = trim((string) $this->customSubject) !== ''
            ? trim((string) $this->customSubject)
            : self::defaultSubject($this->upload, $this->employee, $this->submissionReason);

        $customMessage = trim((string) $this->customMessage) !== ''
            ? trim((string) $this->customMessage)
            : null;

        return $this
            ->subject($subject)
            ->markdown('emails.employee_document_submission', array_merge($viewData, [
                'customMessage' => $customMessage,
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(
        Upload $upload,
        BPEmployee $employee,
        Facility $facility,
        User $submittedBy,
        string $submissionReason,
    ): array {
        $upload = $upload->loadMissing(['uploadType']);

        $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));

        return [
            'employeeName' => $employeeName !== '' ? $employeeName : 'Employee',
            'employeeNum' => $employee->employee_num,
            'facilityName' => $facility->name ?? config('app.name'),
            'documentType' => $upload->uploadType?->name ?? 'Document',
            'fileName' => $upload->original_filename,
            'submissionReason' => UploadSubmissionReason::label($submissionReason) ?? $submissionReason,
            'expiresAt' => $upload->expires_at
                ? Carbon::parse($upload->expires_at)->format('F j, Y')
                : null,
            'effectiveStart' => $upload->effective_start_date
                ? Carbon::parse($upload->effective_start_date)->format('F j, Y')
                : null,
            'comments' => $upload->comments,
            'submittedByName' => $submittedBy->name,
            'reviewUrl' => route('admin.employees.edit', $employee->id) . '?tab=documents',
        ];
    }

    public static function defaultSubject(Upload $upload, BPEmployee $employee, string $submissionReason): string
    {
        $upload = $upload->loadMissing(['uploadType']);
        $typeName = $upload->uploadType?->name ?? 'Document';
        $employeeName = trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? ''));

        return "Document review requested — {$typeName} ({$employeeName})";
    }

    public static function defaultMessage(
        Upload $upload,
        BPEmployee $employee,
        Facility $facility,
        string $submissionReason,
    ): string {
        $upload = $upload->loadMissing(['uploadType']);
        $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';
        $facilityName = $facility->name ?? config('app.name');
        $reason = UploadSubmissionReason::label($submissionReason) ?? $submissionReason;

        return "Hello,\n\n"
            . "{$employeeName} ({$employee->employee_num}) has uploaded a document for your review at {$facilityName}.\n\n"
            . "Reason for upload: {$reason}\n\n"
            . 'Please verify the document and approve or reject it in the employee record.';
    }

    /**
     * @return array<string, mixed>
     */
    public static function previewPayload(
        Upload $upload,
        BPEmployee $employee,
        Facility $facility,
        User $submittedBy,
        string $recipientEmails,
    ): array {
        $upload = $upload->loadMissing(['uploadType']);

        return [
            'mode' => 'submission',
            'to' => $recipientEmails,
            'subject' => self::defaultSubject($upload, $employee, $upload->submission_reason ?? UploadSubmissionReason::INITIAL),
            'message' => self::defaultMessage(
                $upload,
                $employee,
                $facility,
                $upload->submission_reason ?? UploadSubmissionReason::INITIAL,
            ),
            'document_type' => $upload->uploadType?->name ?? 'Document',
            'file_name' => $upload->original_filename,
            'facility_name' => $facility->name,
            'employee_name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
            'submission_reasons' => collect(UploadSubmissionReason::options())
                ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
            'current_submission_reason' => $upload->submission_reason,
            'verification_status' => $upload->verification_status,
        ];
    }
}
