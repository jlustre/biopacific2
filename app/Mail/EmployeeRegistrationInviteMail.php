<?php

namespace App\Mail;

use App\Models\RegistrationCode;
use App\Support\RegistrationCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeRegistrationInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegistrationCode $registrationCode)
    {
    }

    public function build()
    {
        $registrationUrl = app(RegistrationCodeService::class)->registrationUrl($this->registrationCode);

        return $this->subject('Your Bio-Pacific HR portal registration code')
            ->view('emails.employee_registration_invite')
            ->with([
                'employeeName' => $this->registrationCode->fullName(),
                'registrationCode' => $this->registrationCode->code,
                'registrationUrl' => $registrationUrl,
                'expiresAt' => $this->registrationCode->expires_at,
            ]);
    }
}
