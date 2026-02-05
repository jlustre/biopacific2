<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecureJobApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $jobApplication;
    public $facility;

    /**
     * Create a new message instance.
     */
    public function __construct(JobApplication $jobApplication, $facility = null)
    {
        $this->jobApplication = $jobApplication;
        $this->facility = $facility;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Job Application Submitted - Secure Access Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate secure access URL
        $secureUrl = route('secure.job-application', ['token' => $this->jobApplication->getAccessToken()]);
        
        return new Content(
            html: 'emails.secure_job_application_email',
            with: [
                'secureUrl' => $secureUrl,
                'applicationId' => $this->jobApplication->id,
                'jobTitle' => $this->jobApplication->jobOpening->title ?? 'Unknown title',
                'facilityName' => $this->facility->name ?? 'BioPacific',
                'submittedAt' => $this->jobApplication->created_at->format('M j, Y \a\t g:i A'),
                'expiresAt' => $this->jobApplication->expires_at->format('M j, Y \a\t g:i A'),
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