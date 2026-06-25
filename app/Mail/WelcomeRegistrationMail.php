<?php

namespace App\Mail;

use App\Models\RegistrationCode;
use App\Models\User;
use App\Support\EmailTemplatePlaceholderService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected User $user,
        protected ?RegistrationCode $registrationCode = null,
    ) {
    }

    public function build()
    {
        $templateService = app(EmailTemplatePlaceholderService::class);
        $template = $templateService->welcomeRegistrationTemplate();

        if ($template) {
            [$subject, $body] = $templateService->fillForNewUser($template, $this->user, $this->registrationCode);

            return $this->subject($subject)->html($body);
        }

        $values = $templateService->valuesForNewUser($this->user, $this->registrationCode);

        return $this->subject('Welcome to Bio-Pacific')
            ->view('emails.welcome_registration')
            ->with([
                'userName' => $this->user->name,
                'firstName' => $values['first_name'],
                'facilityName' => $values['facility_name'],
                'verificationUrl' => $values['verification_link'],
                'dashboardUrl' => $values['dashboard_link'],
                'preEmploymentUrl' => $values['pre_employment_link'] ?: null,
            ]);
    }
}
