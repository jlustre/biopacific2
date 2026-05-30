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

    public function __construct(protected RegistrationCode $registrationCodeRecord)
    {
    }

    public function build()
    {
        $registrationUrl = app(RegistrationCodeService::class)->registrationUrl($this->registrationCodeRecord);

        return $this->subject('Your Bio-Pacific HR portal registration code')
            ->view('emails.employee_registration_invite')
            ->with([
                'employeeName' => $this->registrationCodeRecord->fullName(),
                'registrationCode' => $this->registrationCodeRecord->code,
                'registrationUrl' => $registrationUrl,
                'expiresAt' => $this->registrationCodeRecord->expires_at,
            ]);
    }
}
