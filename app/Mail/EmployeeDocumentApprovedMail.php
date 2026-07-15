<?php

namespace App\Mail;

use App\Models\BPEmployee;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeDocumentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BPEmployee $employee,
        public Upload $upload,
    ) {}

    public function build(): self
    {
        $employeeName = trim(($this->employee->first_name ?? '').' '.($this->employee->last_name ?? ''));
        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';
        $documentName = $this->upload->checklistItem?->name
            ?? $this->upload->uploadType?->name
            ?? $this->upload->original_filename
            ?? 'Document';
        $reviewer = $this->upload->verifiedBy?->name;
        $credentialsUrl = route('member.certifications');
        $documentsUrl = route('member.documents');

        return $this->subject('Document approved: '.$documentName)
            ->view('emails.employee-document-approved', [
                'employeeName' => $employeeName,
                'documentName' => $documentName,
                'reviewerName' => $reviewer,
                'credentialsUrl' => $credentialsUrl,
                'documentsUrl' => $documentsUrl,
                'isLicense' => (bool) ($this->upload->uploadType?->is_license_or_certification ?? false),
            ]);
    }
}
