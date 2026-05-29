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
        public JobApplication $jobApplication,
        public ?RegistrationCode $registrationCode = null,
    ) {
    }

    public function build()
    {
        $applicantName = trim($this->jobApplication->first_name . ' ' . $this->jobApplication->last_name);
        $applicantCode = $this->jobApplication->applicant_code ?? '';
        $preEmploymentUrl = url('/pre-employment') . ($applicantCode ? '?c=' . urlencode($applicantCode) : '');
        $registrationUrl = $this->registrationCode
            ? app(RegistrationCodeService::class)->registrationUrl($this->registrationCode)
            : null;

        return $this->subject('Your pre-employment link is ready')
            ->view('emails.pre_employment_email')
            ->with([
                'applicantName' => $applicantName ?: 'Applicant',
                'applicantCode' => $applicantCode,
                'preEmploymentUrl' => $preEmploymentUrl,
                'registrationCode' => $this->registrationCode?->code,
                'registrationUrl' => $registrationUrl,
            ]);
    }
}
