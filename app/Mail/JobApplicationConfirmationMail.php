<?php

namespace App\Mail;

use App\Models\Facility;
use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobApplicationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public ?Facility $facility = null,
        public ?string $positionTitle = null,
    ) {
    }

    public function build(): self
    {
        $facilityName = $this->facility?->name ?? config('app.name');
        $positionTitle = $this->positionTitle
            ?? $this->application->desired_position
            ?? $this->application->jobOpening?->title
            ?? 'your selected position';

        $applicantName = trim(
            ($this->application->first_name ?? '') . ' ' . ($this->application->last_name ?? '')
        ) ?: 'Applicant';

        return $this
            ->subject("Application Received – {$positionTitle} at {$facilityName}")
            ->markdown('emails.job_application_confirmation', [
                'applicantName' => $applicantName,
                'facilityName' => $facilityName,
                'positionTitle' => $positionTitle,
                'submittedAt' => $this->application->created_at?->timezone(config('app.timezone'))
                    ->format('F j, Y \a\t g:i A') ?? now()->format('F j, Y \a\t g:i A'),
            ]);
    }
}
