<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledReportRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduledReportRunController extends Controller
{
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && $user->facility_id) {
            return (int) $user->facility_id;
        }

        return null;
    }

    protected function canManageScheduledReports(Request $request): bool
    {
        return (bool) $request->user()?->hasAnyRole(['admin', 'rdhr', 'facility-admin', 'facility-dsd']);
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
        $run->delete();

        return redirect()->route('admin.scheduled-report-runs.index')->with('success', 'Run deleted.');
    }

    public function showReport(Request $request, ScheduledReportRun $run)
    {
        $this->authorizeRun($request, $run);
        $scheduledReport = $run->scheduledReport;
        $runAt = $run->executed_at;
        if (! empty($run->result_json)) {
            $results = is_array($run->result_json) ? $run->result_json : json_decode($run->result_json, true);
        } else {
            $results = [];
            if ($scheduledReport && $scheduledReport->report) {
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
