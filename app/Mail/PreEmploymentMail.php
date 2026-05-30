<?php

namespace App\Mail;

use App\Models\JobApplication;
use App\Models\RegistrationCode;
use App\Support\RegistrationCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreEmploymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected JobApplication $jobApplicationRecord,
        protected ?RegistrationCode $registrationCodeRecord = null,
    ) {
    }

    public function build()
    {
        $applicantName = trim($this->jobApplicationRecord->first_name . ' ' . $this->jobApplicationRecord->last_name);
        $applicantCode = $this->jobApplicationRecord->applicant_code ?? '';
        $preEmploymentUrl = url('/pre-employment') . ($applicantCode ? '?c=' . urlencode($applicantCode) : '');
        $registrationUrl = $this->registrationCodeRecord
            ? app(RegistrationCodeService::class)->registrationUrl($this->registrationCodeRecord)
            : null;

        return $this->subject('Your pre-employment link is ready')
            ->view('emails.pre_employment_email')
            ->with([
                'applicantName' => $applicantName ?: 'Applicant',
                'applicantCode' => $applicantCode,
                'preEmploymentUrl' => $preEmploymentUrl,
                'registrationCode' => $this->registrationCodeRecord?->code,
                'registrationUrl' => $registrationUrl,
            ]);
    }
}
