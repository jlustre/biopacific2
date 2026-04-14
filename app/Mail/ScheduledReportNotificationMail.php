<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduledReportNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reportName;
    public $reportId;
    public $parameters;
    public $runAt;
    public $resultSummary;

    /**
     * Create a new message instance.
     */
    public function __construct($reportName, $reportId, $parameters, $runAt, $resultSummary = null)
    {
        $this->reportName = $reportName;
        $this->reportId = $reportId;
        $this->parameters = $parameters;
        $this->runAt = $runAt;
        $this->resultSummary = $resultSummary;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Scheduled Report Executed: ' . $this->reportName)
            ->markdown('emails.scheduled_report_notification')
            ->with([
                'reportName' => $this->reportName,
                'reportId' => $this->reportId,
                'parameters' => $this->parameters,
                'runAt' => $this->runAt,
                'resultSummary' => $this->resultSummary,
            ]);
    }
}
