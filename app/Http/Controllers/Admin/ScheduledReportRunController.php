<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledReportRun;
use App\Services\ScheduledReportRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScheduledReportRunController extends Controller
{
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if ($user && ! $user->hasRole(['admin', 'super-admin']) && $user->facility_id) {
            return (int) $user->facility_id;
        }

        return null;
    }

    protected function canManageScheduledReports(Request $request): bool
    {
        return (bool) $request->user()?->hasAnyRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);
    }

    protected function applyFacilityScopeToRunsQuery($query, Request $request)
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return $query;
        }

        return $query->whereHas('scheduledReport', function ($q) use ($request) {
            app(ScheduledReportController::class)->applyFacilityScopeToScheduledReportsQuery($q, $request);
        });
    }

    protected function authorizeRun(Request $request, ScheduledReportRun $run): void
    {
        $run->load(['scheduledReport.report', 'scheduledReport.creator']);
        if ($run->scheduledReport) {
            app(ScheduledReportController::class)->authorizeScheduledReport($request, $run->scheduledReport);
        }
    }

    public function index(Request $request)
    {
        $query = ScheduledReportRun::with('scheduledReport');
        $this->applyFacilityScopeToRunsQuery($query, $request);

        if ($request->filled('report_name')) {
            $query->whereHas('scheduledReport', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('report_name').'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('executed_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('executed_at', '<=', $request->input('date_to'));
        }

        $runs = $query->orderByDesc('executed_at')->paginate(20)->withQueryString();

        return view('admin.scheduled-report-runs.index', compact('runs'));
    }

    public function show(Request $request, ScheduledReportRun $run)
    {
        $this->authorizeRun($request, $run);
        $run->load('scheduledReport');

        return view('admin.scheduled-report-runs.show', compact('run'));
    }

    public function archive(Request $request, ScheduledReportRun $run)
    {
        if (! $this->canManageScheduledReports($request)) {
            abort(403, 'Only system administrators can archive scheduled report runs.');
        }
        $this->authorizeRun($request, $run);
        $run->status = 'archived';
        $run->save();

        return redirect()->route('admin.scheduled-report-runs.index')->with('success', 'Run archived.');
    }

    public function destroy(Request $request, ScheduledReportRun $run)
    {
        if (! $this->canManageScheduledReports($request)) {
            abort(403, 'Only system administrators can delete scheduled report runs.');
        }
        $this->authorizeRun($request, $run);

        if ($run->result_path && Storage::disk('local')->exists($run->result_path)) {
            Storage::disk('local')->delete($run->result_path);
        }

        $run->delete();

        return redirect()->route('admin.scheduled-report-runs.index')->with('success', 'Run deleted.');
    }

    public function download(Request $request, ScheduledReportRun $run)
    {
        $this->authorizeRun($request, $run);
        $scheduledReport = $run->scheduledReport;
        if (! $scheduledReport) {
            return back()->with('error', 'Scheduled report not found for this run.');
        }
        if ($run->status !== 'success') {
            return back()->with('error', 'This run did not complete successfully, so no file is available.');
        }

        try {
            $export = app(ScheduledReportRunner::class)->exportForRun($scheduledReport, $run);

            return response($export['content'])
                ->header('Content-Type', $export['mime'])
                ->header('Content-Disposition', 'attachment; filename="'.$export['filename'].'"');
        } catch (\Throwable $e) {
            Log::error('Scheduled report run download failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Download failed: '.$e->getMessage());
        }
    }

    public function showReport(Request $request, ScheduledReportRun $run)
    {
        $this->authorizeRun($request, $run);
        $scheduledReport = $run->scheduledReport;
        if (! $scheduledReport) {
            abort(404, 'Scheduled report not found for this run.');
        }

        $format = $scheduledReport->report_format ?: 'html';

        // PDF/CSV should be served as the generated file; HTML can preview inline.
        if (in_array($format, ['pdf', 'csv'], true)) {
            try {
                $export = app(ScheduledReportRunner::class)->exportForRun($scheduledReport, $run);
                $disposition = $format === 'pdf' ? 'inline' : 'attachment';

                return response($export['content'])
                    ->header('Content-Type', $export['mime'])
                    ->header('Content-Disposition', $disposition.'; filename="'.$export['filename'].'"');
            } catch (\Throwable $e) {
                Log::error('Scheduled report run preview failed', [
                    'run_id' => $run->id,
                    'error' => $e->getMessage(),
                ]);
                abort(500, 'Unable to generate report file: '.$e->getMessage());
            }
        }

        $runAt = $run->executed_at;
        if (! empty($run->result_json)) {
            $results = is_array($run->result_json) ? $run->result_json : json_decode($run->result_json, true);
        } else {
            $results = [];
            if ($scheduledReport->report) {
                try {
                    $parameters = $scheduledReport->parameters ?? [];
                    $sql = $scheduledReport->report->sql_template;
                    $results = DB::select($sql, $parameters);
                } catch (\Exception $e) {
                    $results = [];
                }
            }
        }

        return view('admin.scheduled-reports.report-html', [
            'results' => $results,
            'scheduledReport' => $scheduledReport,
            'runAt' => $runAt,
        ]);
    }
}
