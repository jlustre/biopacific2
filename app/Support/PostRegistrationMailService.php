<?php

namespace App\Support;

use App\Mail\WelcomeRegistrationMail;
use App\Models\RegistrationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PostRegistrationMailService
{
    public function sendWelcome(User $user, ?RegistrationCode $registrationCode = null): void
    {
        Mail::to($user->email)->send(new WelcomeRegistrationMail($user, $registrationCode));
    }

    public function sendVerification(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    public function sendWelcomeAndVerification(User $user, ?RegistrationCode $registrationCode = null): void
    {
        $this->sendWelcome($user, $registrationCode);
        $this->sendVerification($user);
    }
}
