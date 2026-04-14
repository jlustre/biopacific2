<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledReport;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';
    protected $description = 'Run scheduled reports based on their CRON expressions';

    public function handle()
    {
        $now = Carbon::now();
        $scheduled = ScheduledReport::where('status', 'active')
            ->where(function($q) use ($now) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', $now);
            })->get();

        foreach ($scheduled as $sr) {
            try {
                $report = $sr->report;
                if (!$report) continue;
                // Here you would run the report logic, e.g. DB::select($report->sql_template, $sr->parameters ?? []);
                // For demo, just log
                Log::info('Running scheduled report', ['id' => $sr->id, 'name' => $sr->name]);

                // --- EMAIL NOTIFICATION LOGIC ---
                if ($sr->notifications_enabled) {
                    $notifyEmails = [];
                    // Add direct emails from notify_emails field
                    if (!empty($sr->notify_emails)) {
                        $emails = is_array($sr->notify_emails) ? $sr->notify_emails : explode(',', $sr->notify_emails);
                        foreach ($emails as $email) {
                            $trimmed = trim($email);
                            if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                                $notifyEmails[] = $trimmed;
                            }
                        }
                    }
                    // Add emails by role
                    if (!empty($sr->notify_roles) && is_array($sr->notify_roles)) {
                        $roleEmails = \App\Models\User::role($sr->notify_roles)->pluck('email')->toArray();
                        $notifyEmails = array_merge($notifyEmails, $roleEmails);
                    }
                    // Remove duplicates
                    $notifyEmails = array_unique($notifyEmails);
                    if (!empty($notifyEmails)) {
                        try {
                            foreach ($notifyEmails as $email) {
                                Mail::to($email)->send(new \App\Mail\ScheduledReportNotificationMail(
                                    $sr->name,
                                    $sr->report_id,
                                    $sr->parameters ?? [],
                                    $now,
                                    null // Optionally pass a result summary
                                ));
                            }
                            Log::info('Scheduled report notification sent', ['report_id' => $sr->id, 'emails' => $notifyEmails]);
                        } catch (\Exception $mailEx) {
                            Log::error('Failed to send scheduled report notification', ['report_id' => $sr->id, 'error' => $mailEx->getMessage()]);
                        }
                    }
                }
                // --- END EMAIL NOTIFICATION LOGIC ---

                // Update last_run_at and next_run_at
                $sr->last_run_at = $now;
                $sr->next_run_at = $this->getNextRunAt($sr->cron_expression, $now);
                $sr->save();
            } catch (\Exception $e) {
                Log::error('Scheduled report failed', ['id' => $sr->id, 'error' => $e->getMessage()]);
                $sr->status = 'error';
                $sr->save();
            }
        }
        $this->info('Scheduled reports processed.');
    }

    protected function getNextRunAt($cron, $from)
    {
        try {
            $cron = \Cron\CronExpression::factory($cron);
            return $cron->getNextRunDate($from)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
