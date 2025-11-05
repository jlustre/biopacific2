<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class JobApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Building JobApplicationMail', ['data' => $this->data]);

        $mail = $this->subject('New Job Application - ' . $this->data['job_title'] . ' at ' . $this->data['facility']['name'])
                     ->markdown('emails.job_application_email')
                     ->with('data', $this->data);

        // Attach resume if available
        if (!empty($this->data['resume_path'])) {
            $resumePath = storage_path('app/public/' . $this->data['resume_path']);
            if (file_exists($resumePath)) {
                $mail->attach($resumePath, [
                    'as' => $this->data['first_name'] . '_' . $this->data['last_name'] . '_Resume.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}