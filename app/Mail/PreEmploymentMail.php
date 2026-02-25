<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreEmploymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public JobApplication $jobApplication;

    public function __construct(JobApplication $jobApplication)
    {
        $this->jobApplication = $jobApplication;
    }

    public function build()
    {
        $applicantName = trim($this->jobApplication->first_name . ' ' . $this->jobApplication->last_name);
        $applicantCode = $this->jobApplication->applicant_code ?? '';
        $preEmploymentUrl = url('/pre-employment') . ($applicantCode ? '?c=' . urlencode($applicantCode) : '');

        return $this->subject('Your pre-employment link is ready')
            ->view('emails.pre_employment_email')
            ->with([
                'applicantName' => $applicantName ?: 'Applicant',
                'applicantCode' => $applicantCode,
                'preEmploymentUrl' => $preEmploymentUrl,
            ]);
    }
}
