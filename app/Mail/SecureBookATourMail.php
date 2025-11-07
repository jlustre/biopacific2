<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TourRequest;

class SecureBookATourMail extends Mailable
{
    use Queueable, SerializesModels;

    private $tourRequest;
    private $facilityName;

    /**
     * Create a new message instance.
     */
    public function __construct(TourRequest $tourRequest, string $facilityName)
    {
        $this->tourRequest = $tourRequest;
        $this->facilityName = $facilityName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $secureUrl = $this->tourRequest->getSecureAccessUrl();
        
        return $this->subject("New Tour Request - {$this->facilityName}")
                    ->markdown('emails.secure-tour-notification')
                    ->with([
                        'facilityName' => $this->facilityName,
                        'secureUrl' => $secureUrl,
                        'submittedAt' => $this->tourRequest->created_at,
                        'expiresAt' => $this->tourRequest->expires_at,
                        'requestId' => $this->tourRequest->id
                    ]);
    }
}