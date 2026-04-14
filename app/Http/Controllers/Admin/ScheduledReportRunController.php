<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduledReportRun;
use Illuminate\Support\Facades\DB;

class ScheduledReportRunController extends Controller
{
    public function index(Request $request)
    {
        $query = ScheduledReportRun::with('scheduledReport');

        // Search by report name
        if ($request->filled('report_name')) {
            $query->whereHas('scheduledReport', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('report_name') . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('executed_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('executed_at', '<=', $request->input('date_to'));
        }

        $runs = $query->orderByDesc('executed_at')->paginate(20)->appends($request->all());
        return view('admin.scheduled-report-runs.index', compact('runs'));
    }

    public function show(ScheduledReportRun $run)
    {
        $run->load('scheduledReport');
        return view('admin.scheduled-report-runs.show', compact('run'));
    }

    public function archive(ScheduledReportRun $run)
    {
        $run->status = 'archived';
        $run->save();
        return redirect()->route('admin.scheduled-report-runs.index')->with('success', 'Run archived.');
    }

    public function destroy(ScheduledReportRun $run)
    {
        $run->delete();
        return redirect()->route('admin.scheduled-report-runs.index')->with('success', 'Run deleted.');
    }

     // Show the actual generated report as HTML (for modal preview)
    public function showReport(ScheduledReportRun $run)
    {
        $scheduledReport = $run->scheduledReport;
        $runAt = $run->executed_at;
        // Use stored results if available
        if (!empty($run->result_json)) {
            $results = is_array($run->result_json) ? $run->result_json : json_decode($run->result_json, true);
        } else {
            // fallback: try to re-run the report query (legacy runs)
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
