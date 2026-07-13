<?php

namespace App\Services;

use App\Mail\ScheduledReportNotificationMail;
use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScheduledReportRunner
{
    /**
     * Execute a scheduled report, persist a history row, and advance next_run_at.
     *
     * @param  array<string, mixed>|null  $parameterOverrides
     */
    public function execute(
        ScheduledReport $scheduledReport,
        ?array $parameterOverrides = null,
        bool $markScheduleErrorOnFailure = false,
        bool $sendNotifications = true
    ): ScheduledReportRun {
        $now = Carbon::now();
        $report = $scheduledReport->report;

        if (! $report) {
            $run = $this->recordRun($scheduledReport, $now, 'error', null, null, 'Report template not found for this schedule.');
            if ($markScheduleErrorOnFailure) {
                $scheduledReport->status = 'error';
                $scheduledReport->save();
            }

            return $run;
        }

        try {
            $parameters = $this->resolveParameters(
                $report,
                $parameterOverrides ?? ($scheduledReport->parameters ?? [])
            );
            $results = $this->queryResults((string) $report->sql_template, $parameters);

            $resultPath = null;
            try {
                $export = $this->buildExport($scheduledReport, $results, $now);
                $resultPath = $this->storeExport($scheduledReport, $export, $now);
            } catch (Throwable $fileEx) {
                Log::warning('Scheduled report file generation failed; run data was still saved', [
                    'id' => $scheduledReport->id,
                    'format' => $scheduledReport->report_format,
                    'error' => $fileEx->getMessage(),
                ]);
            }

            $run = $this->recordRun($scheduledReport, $now, 'success', $results, $resultPath, null);

            $scheduledReport->last_run_at = $now;
            $scheduledReport->next_run_at = $this->getNextRunAt($scheduledReport->cron_expression, $now);
            if ($scheduledReport->status === 'error') {
                $scheduledReport->status = 'active';
            }
            $scheduledReport->save();

            if ($sendNotifications && $scheduledReport->notifications_enabled) {
                $this->sendNotifications($scheduledReport, $now, $results, $resultPath);
            }

            return $run;
        } catch (Throwable $e) {
            Log::error('Scheduled report execution failed', [
                'id' => $scheduledReport->id,
                'error' => $e->getMessage(),
            ]);

            $run = $this->recordRun($scheduledReport, $now, 'error', null, null, $e->getMessage());

            $scheduledReport->last_run_at = $now;
            $scheduledReport->next_run_at = $this->getNextRunAt($scheduledReport->cron_expression, $now);
            if ($markScheduleErrorOnFailure) {
                $scheduledReport->status = 'error';
            }
            $scheduledReport->save();

            return $run;
        }
    }

    /**
     * Build exported content in the schedule's selected format (csv|pdf|html).
     *
     * @param  list<object|array<string, mixed>>  $results
     * @return array{content: string, mime: string, ext: string, filename: string}
     */
    public function buildExport(ScheduledReport $scheduledReport, array $results, $runAt = null): array
    {
        $runAt = $runAt ?? Carbon::now();
        $results = array_map(static function ($row) {
            return (object) (array) $row;
        }, $results);

        $format = $scheduledReport->report_format ?: 'csv';
        $stamp = Carbon::parse($runAt)->format('Ymd_His');
        $baseName = 'scheduled_report_'.$scheduledReport->id.'_'.$stamp;

        if ($format === 'pdf') {
            $pdfView = view('admin.scheduled-reports.report-pdf', [
                'results' => $results,
                'scheduledReport' => $scheduledReport,
                'runAt' => $runAt,
            ])->render();
            $orientation = ($scheduledReport->pdf_orientation === 'L') ? 'landscape' : 'portrait';
            $content = Pdf::loadHTML($pdfView)->setPaper('a4', $orientation)->output();

            return [
                'content' => $content,
                'mime' => 'application/pdf',
                'ext' => 'pdf',
                'filename' => $baseName.'.pdf',
            ];
        }

        if ($format === 'html') {
            $content = view('admin.scheduled-reports.report-html', [
                'results' => $results,
                'scheduledReport' => $scheduledReport,
                'runAt' => $runAt,
            ])->render();

            return [
                'content' => $content,
                'mime' => 'text/html; charset=UTF-8',
                'ext' => 'html',
                'filename' => $baseName.'.html',
            ];
        }

        $csv = '';
        if ($results !== []) {
            $header = array_keys((array) $results[0]);
            $csv .= implode(',', $header)."\n";
            foreach ($results as $row) {
                $csv .= implode(',', array_map(static function ($v) {
                    return '"'.str_replace('"', '""', (string) $v).'"';
                }, (array) $row))."\n";
            }
        } else {
            $csv .= "No results found.\n";
        }

        return [
            'content' => $csv,
            'mime' => 'text/csv; charset=UTF-8',
            'ext' => 'csv',
            'filename' => $baseName.'.csv',
        ];
    }

    /**
     * Persist an export under storage/app/scheduled-reports.
     *
     * @param  array{content: string, mime: string, ext: string, filename: string}  $export
     */
    public function storeExport(ScheduledReport $scheduledReport, array $export, $runAt = null): string
    {
        $runAt = $runAt ?? Carbon::now();
        $relativePath = sprintf(
            'scheduled-reports/%d/%s',
            $scheduledReport->id,
            $export['filename'] ?: ('run_'.Carbon::parse($runAt)->format('Ymd_His').'.'.$export['ext'])
        );

        Storage::disk('local')->put($relativePath, $export['content']);

        return $relativePath;
    }

    /**
     * Resolve export bytes for a past run (regenerate from result_json when available).
     *
     * @return array{content: string, mime: string, ext: string, filename: string}
     */
    public function exportForRun(ScheduledReport $scheduledReport, ScheduledReportRun $run): array
    {
        $results = is_array($run->result_json) ? $run->result_json : [];

        if ($results !== [] || $run->status === 'success') {
            $export = $this->buildExport($scheduledReport, $results, $run->executed_at);

            try {
                $path = $this->storeExport($scheduledReport, $export, $run->executed_at);
                if ($run->result_path && $run->result_path !== $path && Storage::disk('local')->exists($run->result_path)) {
                    Storage::disk('local')->delete($run->result_path);
                }
                $run->result_path = $path;
                $run->save();
            } catch (Throwable $e) {
                Log::warning('Could not cache regenerated scheduled report file', [
                    'run_id' => $run->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $export;
        }

        if ($run->result_path && Storage::disk('local')->exists($run->result_path)) {
            $ext = pathinfo($run->result_path, PATHINFO_EXTENSION) ?: 'csv';
            $mime = match ($ext) {
                'pdf' => 'application/pdf',
                'html', 'htm' => 'text/html; charset=UTF-8',
                default => 'text/csv; charset=UTF-8',
            };

            return [
                'content' => Storage::disk('local')->get($run->result_path),
                'mime' => $mime,
                'ext' => $ext,
                'filename' => basename($run->result_path),
            ];
        }

        return $this->buildExport($scheduledReport, [], $run->executed_at);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return list<object|array<string, mixed>>
     */
    public function queryResults(string $sqlTemplate, array $parameters): array
    {
        $sql = $sqlTemplate;
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = array_values($value)[0] ?? null;
            }
            if (is_string($value) && is_numeric($value)) {
                $value = str_contains($value, '.') ? (float) $value : (int) $value;
            }

            $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
        }

        // Neutralize any leftover named placeholders so PDO does not treat them as bindings.
        $sql = preg_replace('/:[a-zA-Z_][a-zA-Z0-9_]*/', 'NULL', $sql) ?? $sql;

        return DB::select($sql);
    }

    /**
     * Merge stored schedule params with report parameter defaults.
     *
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public function resolveParameters(\App\Models\Report $report, array $stored): array
    {
        $resolved = $this->normalizeParameters($stored);

        foreach ($report->parameters ?? [] as $param) {
            if (! is_array($param)) {
                $name = (string) $param;
                if ($name !== '' && ! array_key_exists($name, $resolved)) {
                    $resolved[$name] = 0;
                }

                continue;
            }

            $name = (string) ($param['name'] ?? '');
            if ($name === '' || array_key_exists($name, $resolved)) {
                continue;
            }

            $resolved[$name] = $param['default'] ?? (
                in_array(($param['type'] ?? ''), ['facility', 'department', 'position', 'reports_to', 'integer', 'number'], true)
                    ? 0
                    : null
            );
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function normalizeParameters(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                if (count($value) === 1 && array_key_exists(0, $value)) {
                    $parameters[$key] = $value[0];
                } else {
                    $first = array_values($value);
                    $parameters[$key] = $first[0] ?? null;
                }
            }
        }

        return $parameters;
    }

    public function getNextRunAt(?string $cron, $from = null): ?Carbon
    {
        $cron = trim((string) $cron);
        if ($cron === '') {
            return null;
        }

        try {
            $expression = new CronExpression($cron);
            $from = Carbon::parse($from ?? now());

            return Carbon::instance($expression->getNextRunDate($from));
        } catch (Throwable $e) {
            Log::warning('Invalid scheduled report cron expression', [
                'cron' => $cron,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function isDue(ScheduledReport $scheduledReport, ?Carbon $now = null): bool
    {
        $now = $now ?? Carbon::now();

        if ($scheduledReport->status !== 'active') {
            return false;
        }

        if ($scheduledReport->start_at && $now->lt(Carbon::parse($scheduledReport->start_at))) {
            return false;
        }

        if ($scheduledReport->end_at && $now->gt(Carbon::parse($scheduledReport->end_at))) {
            return false;
        }

        if ($scheduledReport->next_run_at === null) {
            return true;
        }

        return Carbon::parse($scheduledReport->next_run_at)->lte($now);
    }

    /**
     * @param  list<object|array<string, mixed>>|null  $results
     */
    protected function recordRun(
        ScheduledReport $scheduledReport,
        Carbon $now,
        string $status,
        ?array $results,
        ?string $resultPath,
        ?string $errorMessage
    ): ScheduledReportRun {
        return ScheduledReportRun::create([
            'scheduled_report_id' => $scheduledReport->id,
            'executed_at' => $now,
            'result_path' => $resultPath,
            'result_json' => $results,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * @param  list<object|array<string, mixed>>  $results
     */
    protected function sendNotifications(
        ScheduledReport $scheduledReport,
        Carbon $now,
        array $results,
        ?string $resultPath = null
    ): void {
        $notifyEmails = [];

        if (! empty($scheduledReport->notify_emails)) {
            $emails = is_array($scheduledReport->notify_emails)
                ? $scheduledReport->notify_emails
                : explode(',', (string) $scheduledReport->notify_emails);
            foreach ($emails as $email) {
                $trimmed = trim((string) $email);
                if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $notifyEmails[] = $trimmed;
                }
            }
        }

        if (! empty($scheduledReport->notify_roles) && is_array($scheduledReport->notify_roles)) {
            $roleEmails = User::role($scheduledReport->notify_roles)->pluck('email')->all();
            $notifyEmails = array_merge($notifyEmails, $roleEmails);
        }

        $notifyEmails = array_values(array_unique(array_filter($notifyEmails)));
        if ($notifyEmails === []) {
            return;
        }

        $summary = count($results).' row(s)';
        $attachment = null;

        if ($resultPath && Storage::disk('local')->exists($resultPath)) {
            $format = $scheduledReport->report_format ?: 'csv';
            $mime = match ($format) {
                'pdf' => 'application/pdf',
                'html' => 'text/html; charset=UTF-8',
                default => 'text/csv; charset=UTF-8',
            };
            $attachment = [
                'data' => Storage::disk('local')->get($resultPath),
                'name' => basename($resultPath),
                'mime' => $mime,
            ];
        }

        try {
            foreach ($notifyEmails as $email) {
                Mail::to($email)->send(new ScheduledReportNotificationMail(
                    $scheduledReport->name,
                    $scheduledReport->report_id,
                    $scheduledReport->parameters ?? [],
                    $now,
                    $summary,
                    $attachment
                ));
            }
        } catch (Throwable $mailEx) {
            Log::error('Failed to send scheduled report notification', [
                'report_id' => $scheduledReport->id,
                'error' => $mailEx->getMessage(),
            ]);
        }
    }
}
