<?php

namespace App\Mail;

use App\Models\BPEmployee;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeDocumentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BPEmployee $employee,
        public Upload $upload,
        public string $notes,
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
        $isLicense = (bool) ($this->upload->uploadType?->is_license_or_certification ?? false);
        $actionUrl = $isLicense
            ? route('member.certifications', array_filter(['upload_type_id' => $this->upload->upload_type_id]))
            : route('member.documents', array_filter(['upload_type_id' => $this->upload->upload_type_id]));

        return $this->subject('Document returned for correction: '.$documentName)
            ->view('emails.employee-document-rejected', [
                'employeeName' => $employeeName,
                'documentName' => $documentName,
                'reviewerName' => $reviewer,
                'notes' => $this->notes,
                'actionUrl' => $actionUrl,
                'isLicense' => $isLicense,
            ]);
    }
}
