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
        public RegistrationCode $registrationCode,
        public JobApplication $jobApplication,
    ) {
    }

    public function build()
    {
        $registrationUrl = app(RegistrationCodeService::class)->registrationUrl($this->registrationCode);
        $applicantCode = $this->jobApplication->applicant_code ?? '';
        $preEmploymentUrl = url('/pre-employment') . ($applicantCode ? '?c=' . urlencode($applicantCode) : '');

        return $this->subject('Create your Bio-Pacific applicant portal account')
            ->view('emails.applicant_registration_invite')
            ->with([
                'applicantName' => $this->registrationCode->fullName(),
                'registrationCode' => $this->registrationCode->code,
                'registrationUrl' => $registrationUrl,
                'preEmploymentUrl' => $preEmploymentUrl,
                'applicantCode' => $applicantCode,
                'expiresAt' => $this->registrationCode->expires_at,
            ]);
    }
}
