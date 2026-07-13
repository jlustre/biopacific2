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

    /** @var array{data: string, name: string, mime: string}|null */
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @param  array{data: string, name: string, mime: string}|null  $attachment
     */
    public function __construct($reportName, $reportId, $parameters, $runAt, $resultSummary = null, ?array $attachment = null)
    {
        $this->reportName = $reportName;
        $this->reportId = $reportId;
        $this->parameters = $parameters;
        $this->runAt = $runAt;
        $this->resultSummary = $resultSummary;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Scheduled Report Executed: '.$this->reportName)
            ->markdown('emails.scheduled_report_notification')
            ->with([
                'reportName' => $this->reportName,
                'reportId' => $this->reportId,
                'parameters' => $this->parameters,
                'runAt' => $this->runAt,
                'resultSummary' => $this->resultSummary,
            ]);

        if (! empty($this->attachment['data']) && ! empty($this->attachment['name'])) {
            $mail->attachData(
                $this->attachment['data'],
                $this->attachment['name'],
                ['mime' => $this->attachment['mime'] ?? 'application/octet-stream']
            );
        }

        return $mail;
    }
}
