<?php

namespace App\Jobs;

use App\Models\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateReportPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(public int $reportExportId) {}

    public function handle(): void
    {
        $export = ReportExport::query()
            ->with(['report', 'user'])
            ->findOrFail($this->reportExportId);

        $export->update([
            'status' => ReportExport::STATUS_PROCESSING,
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $sql = $export->report->sql_template;
            foreach ($export->parameters ?? [] as $key => $value) {
                $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
            }

            $results = array_map(
                fn ($row) => (array) $row,
                DB::select($sql)
            );

            $fileName = Str::slug($export->report->name).'-'.now()->format('Ymd-His').'.pdf';
            $filePath = 'report-exports/'.$export->id.'/'.$fileName;
            $generatedBy = $export->user
                ? trim($export->user->name.' ('.$export->user->email.')')
                : 'System';

            $pdf = Pdf::loadView('admin.reports.pdf', [
                'report' => $export->report,
                'results' => $results,
                'pdfOrientation' => $export->pdf_orientation,
                'logoPath' => public_path('images/bplogo.png'),
                'generatedAt' => now(),
                'generatedBy' => $generatedBy,
                'dateScope' => $this->dateScope($export->parameters ?? []),
            ])->setPaper('a4', $export->pdf_orientation);

            Storage::disk('local')->put($filePath, $pdf->output());

            $export->update([
                'status' => ReportExport::STATUS_COMPLETED,
                'row_count' => count($results),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->markFailed($exception);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        ReportExport::query()
            ->whereKey($this->reportExportId)
            ->update([
                'status' => ReportExport::STATUS_FAILED,
                'error_message' => $exception?->getMessage() ?: 'The PDF export job failed.',
                'completed_at' => now(),
            ]);
    }

    private function dateScope(array $parameters): string
    {
        $from = $parameters['date_from']
            ?? $parameters['start_date']
            ?? $parameters['from_date']
            ?? $parameters['from']
            ?? null;
        $to = $parameters['date_to']
            ?? $parameters['end_date']
            ?? $parameters['to_date']
            ?? $parameters['to']
            ?? null;

        if ($from || $to) {
            return trim(($from ?: 'Beginning').' to '.($to ?: 'Present'));
        }

        return 'All available records';
    }
}
