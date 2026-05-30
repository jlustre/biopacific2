<?php

namespace App\Mail;

use App\Models\JobApplication;
use App\Models\RegistrationCode;
use App\Support\RegistrationCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicantRegistrationInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected RegistrationCode $registrationCodeRecord,
        protected JobApplication $jobApplication,
    ) {
    }

    public function build()
    {
        $registrationUrl = app(RegistrationCodeService::class)->registrationUrl($this->registrationCodeRecord);
        $applicantCode = $this->jobApplication->applicant_code ?? '';
        $preEmploymentUrl = url('/pre-employment') . ($applicantCode ? '?c=' . urlencode($applicantCode) : '');

        return $this->subject('Create your Bio-Pacific applicant portal account')
            ->view('emails.applicant_registration_invite')
            ->with([
                'applicantName' => $this->registrationCodeRecord->fullName(),
                'registrationCode' => $this->registrationCodeRecord->code,
                'registrationUrl' => $registrationUrl,
                'preEmploymentUrl' => $preEmploymentUrl,
                'applicantCode' => $applicantCode,
                'expiresAt' => $this->registrationCodeRecord->expires_at,
            ]);
    }
}
