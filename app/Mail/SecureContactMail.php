<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Inquiry;

class SecureContactMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inquiry;
    public $secureViewUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
        
        // Generate secure access token if not exists
        if (!$inquiry->access_token) {
            $inquiry->generateSecureAccessToken();
        }
        
        // Generate secure view URL
        $this->secureViewUrl = route('secure.inquiry.view', [
            'token' => $inquiry->access_token
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $facilityName = $this->inquiry->facility->name ?? 'Bio-Pacific';
        
        return new Envelope(
            subject: "New Secure Contact Inquiry - {$facilityName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.secure-contact-notification',
            with: [
                'inquiry' => $this->inquiry,
                'secureViewUrl' => $this->secureViewUrl,
                'facilityName' => $this->inquiry->facility->name ?? 'Bio-Pacific',
                'safeData' => $this->inquiry->getSafeDataForEmail(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
