<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduledReport;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ScheduledReportRun;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ScheduledReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ScheduledReport::with('report');

        // Search by name or report name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('report', function($qr) use ($search) {
                      $qr->where('name', 'like', "%$search%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by report
        if ($request->filled('report_id')) {
            $query->where('report_id', $request->input('report_id'));
        }

        $scheduledReports = $query->orderByDesc('created_at')->paginate(15)->appends($request->all());
        $reports = \App\Models\Report::orderBy('name')->get();
        return view('admin.scheduled-reports.index', compact('scheduledReports', 'reports'));
    }

    public function create()
    {
        $reports = Report::all();
        return view('admin.scheduled-reports.create', compact('reports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'nullable|string',
            'cron_expression' => 'required|string',
            'status' => 'required|in:active,paused',
            'notify_roles' => 'nullable|array',
            'notify_roles.*' => 'string',
            'notify_emails' => 'nullable|string',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'notifications_enabled' => 'nullable|boolean',
            'report_format' => 'required|in:csv,pdf,html',
            'pdf_orientation' => 'nullable|in:P,L',
        ]);


        $validated['created_by'] = Auth::id();

        // Convert parameters to array if JSON
        if (!empty($validated['parameters'])) {
            $decoded = json_decode($validated['parameters'], true);
            $validated['parameters'] = is_array($decoded) ? $decoded : [];
        } else {
            $validated['parameters'] = [];
        }

        // Store notify_roles and notify_emails as JSON fields (add to fillable/model/migration if needed)
        $validated['notify_roles'] = $request->input('notify_roles', []);
        $validated['notify_emails'] = $request->input('notify_emails', '');
        $validated['start_at'] = $request->input('start_at');
        $validated['end_at'] = $request->input('end_at');
        $validated['notifications_enabled'] = $request->has('notifications_enabled') ? 1 : 0;
        $validated['pdf_orientation'] = $request->input('pdf_orientation');

        try {
            ScheduledReport::create($validated);
            return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to save scheduled report: ' . $e->getMessage()]);
        }
    }

        /**
     * Show history of generated reports for a scheduled report
     */
    public function history(ScheduledReport $scheduledReport)
    {
        $runs = $scheduledReport->runs()->orderByDesc('executed_at')->get();
        return view('admin.scheduled-reports.history', compact('scheduledReport', 'runs'));
    }

    /**
     * Download a generated report CSV
     */
    public function download(ScheduledReport $scheduledReport, $runId)
    {
        // For in-memory streaming, just redirect to runNow (or show error)
        return back()->with('error', 'Download not available. Please re-run the report.');
    }

    public function edit(ScheduledReport $scheduledReport)
    {
        $reports = Report::all();
        return view('admin.scheduled-reports.edit', compact('scheduledReport', 'reports'));
    }

    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'nullable|string',
            'cron_expression' => 'required|string',
            'status' => 'required|in:active,paused',
            'report_format' => 'required|in:csv,pdf,html',
            'pdf_orientation' => 'nullable|in:P,L',
        ]);

        // Convert parameters to array if JSON
        if (!empty($validated['parameters'])) {
            $decoded = json_decode($validated['parameters'], true);
            $validated['parameters'] = is_array($decoded) ? $decoded : [];
        } else {
            $validated['parameters'] = [];
        }

        $validated['pdf_orientation'] = $request->input('pdf_orientation');

        $scheduledReport->update($validated);
        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report updated successfully.');
    }

    public function destroy(ScheduledReport $scheduledReport)
    {
        $scheduledReport->delete();
        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report deleted.');
    }

    /**
     * Manually run a scheduled report ("Run Now" action)
     */
    public function runNow(ScheduledReport $scheduledReport)
    {
        $now = now();
        $report = $scheduledReport->report;
        if (!$report) {
            return back()->with('error', 'Report not found for this schedule.');
        }
        try {
            // 1. Run the report SQL and get results
            $parameters = $scheduledReport->parameters ?? [];
            foreach ($parameters as $key => $value) {
                if (is_array($value)) {
                    if (count($value) === 1 && array_key_exists(0, $value)) {
                        $parameters[$key] = $value[0];
                    } else {
                        $first = array_values($value);
                        $parameters[$key] = isset($first[0]) ? $first[0] : null;
                    }
                }
            }
            $sql = $report->sql_template;
            $results = DB::select($sql, $parameters);
            $format = $scheduledReport->report_format ?? 'csv';
            // Generate file content in memory
            if ($format === 'csv') {
                $csv = '';
                if (!empty($results)) {
                    $header = array_keys((array)$results[0]);
                    $csv .= implode(',', $header) . "\n";
                    foreach ($results as $row) {
                        $csv .= implode(',', array_map(function($v) { return '"'.str_replace('"','""',$v).'"'; }, (array)$row)) . "\n";
                    }
                } else {
                    $csv .= "No results found.\n";
                }
                $content = $csv;
                $mime = 'text/csv';
                $ext = 'csv';
            } elseif ($format === 'pdf') {
                $pdfView = view('admin.scheduled-reports.report-pdf', [
                    'results' => $results,
                    'scheduledReport' => $scheduledReport,
                    'runAt' => $now,
                ])->render();
                $orientation = $scheduledReport->pdf_orientation === 'L' ? 'landscape' : 'portrait';
                $pdf = Pdf::loadHTML($pdfView)->setPaper('a4', $orientation);
                $content = $pdf->output();
                $mime = 'application/pdf';
                $ext = 'pdf';
            } elseif ($format === 'html') {
                $htmlView = view('admin.scheduled-reports.report-html', [
                    'results' => $results,
                    'scheduledReport' => $scheduledReport,
                    'runAt' => $now,
                ])->render();
                $content = $htmlView;
                $mime = 'text/html';
                $ext = 'html';
            } else {
                throw new \Exception('Unsupported report format: ' . $format);
            }
            // Log run in ScheduledReportRun (no file path)
            $run = ScheduledReportRun::create([
                'scheduled_report_id' => $scheduledReport->id,
                'executed_at' => $now,
                'result_path' => null,
                'result_json' => json_encode($results),
                'status' => 'success',
                'error_message' => null,
            ]);
            // Update last_run_at and next_run_at
            $scheduledReport->last_run_at = $now;
            $scheduledReport->next_run_at = $this->getNextRunAt($scheduledReport->cron_expression, $now);
            $scheduledReport->save();
            // Stream file to browser
            $filename = 'scheduled_report_' . $scheduledReport->id . '_' . $now->format('Ymd_His') . '.' . $ext;
            return response($content)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Manual scheduled report run failed', ['id' => $scheduledReport->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $scheduledReport->status = 'error';
            $scheduledReport->save();
            ScheduledReportRun::create([
                'scheduled_report_id' => $scheduledReport->id,
                'executed_at' => $now,
                'result_path' => null,
                'result_json' => null,
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to run scheduled report: ' . $e->getMessage());
        }
    }
    /**
     * Get the next run date for a CRON expression (copied from command)
     */
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
