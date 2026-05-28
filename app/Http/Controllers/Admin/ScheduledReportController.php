<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Models\ScheduledReportTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledReportController extends Controller
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
        return (bool) $request->user()?->hasAnyRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);
    }

    protected function ensureCanManage(Request $request): void
    {
        if (! $this->canManageScheduledReports($request)) {
            abort(403, 'You do not have permission to manage scheduled reports.');
        }
    }

    protected function reportsForUser(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $isRdhr = $user->hasRole('rdhr');
        $roles = $user->getRoleNames()->toArray();
        $userFacilityIds = collect();

        if (method_exists($user, 'facilities')) {
            $userFacilityIds = $user->facilities->pluck('id');
        } elseif ($user->facility_id) {
            $userFacilityIds = collect([$user->facility_id]);
        }

        if ($isAdmin) {
            return Report::orderBy('name')->get();
        }

        return Report::where('is_active', true)
            ->where(function ($q) use ($roles, $userFacilityIds, $isRdhr) {
                $q->where('visibility', 'all');
                if (! empty($roles)) {
                    $q->orWhere(function ($q2) use ($roles) {
                        $q2->where('visibility', 'roles');
                        foreach ($roles as $role) {
                            $q2->orWhereJsonContains('visible_roles', $role);
                        }
                    });
                }
                $q->orWhere(function ($q2) use ($userFacilityIds) {
                    $q2->where('visibility', 'facilities');
                    foreach ($userFacilityIds as $fid) {
                        $q2->orWhereJsonContains('visible_facilities', $fid);
                    }
                });
                if ($isRdhr) {
                    $q->orWhere('visibility', 'admin');
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function applyFacilityScopeToScheduledReportsQuery($query, Request $request)
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return $query;
        }

        $visibleReportIds = $this->reportsForUser($request)->pluck('id');

        return $query->where(function ($q) use ($facilityId, $visibleReportIds) {
            $q->where(function ($pq) use ($facilityId) {
                $pq->where('parameters->facility_id', $facilityId)
                    ->orWhere('parameters->facility_id', (string) $facilityId);
            })
                ->orWhereHas('creator', fn ($uq) => $uq->where('facility_id', $facilityId))
                ->orWhereIn('report_id', $visibleReportIds);
        });
    }

    public function authorizeScheduledReport(Request $request, ScheduledReport $scheduledReport): void
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return;
        }

        $scheduledReport->loadMissing(['report', 'creator']);
        $visibleReportIds = $this->reportsForUser($request)->pluck('id');
        $parameters = $scheduledReport->parameters ?? [];
        $paramFacilityId = $parameters['facility_id'] ?? null;

        $allowed = ($paramFacilityId && (int) $paramFacilityId === $facilityId)
            || ($scheduledReport->creator && (int) $scheduledReport->creator->facility_id === $facilityId)
            || $visibleReportIds->contains($scheduledReport->report_id);

        if (! $allowed) {
            abort(403, 'You do not have access to this scheduled report.');
        }
    }

    public function index(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canManageScheduledReports = $this->canManageScheduledReports($request);

        $query = ScheduledReport::with(['report', 'creator']);

        $this->applyFacilityScopeToScheduledReportsQuery($query, $request);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('report', function ($qr) use ($search) {
                        $qr->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('report_id')) {
            $query->where('report_id', $request->input('report_id'));
        }

        $scheduledReports = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $reports = $this->reportsForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        $templatesQuery = ScheduledReportTemplate::with(['report', 'facility', 'creator']);
        $this->applyFacilityScopeToTemplatesQuery($templatesQuery, $request);
        $templates = $templatesQuery->orderByDesc('created_at')->get();

        $facilities = $scopedFacilityId
            ? Facility::where('id', $scopedFacilityId)->orderBy('name')->get()
            : Facility::orderBy('name')->get();

        return view('admin.scheduled-reports.index', compact(
            'scheduledReports',
            'reports',
            'scopedFacility',
            'scopedFacilityId',
            'canManageScheduledReports',
            'templates',
            'facilities'
        ));
    }

    public function applyFacilityScopeToTemplatesQuery($query, Request $request)
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return $query;
        }

        return $query->where(function ($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)
                ->orWhereNull('facility_id');
        });
    }

    protected function validateTemplatePayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_id' => 'required|exists:reports,id',
            'facility_id' => 'nullable|exists:facilities,id',
            'parameters' => 'nullable|string',
            'cron_expression' => 'required|string|max:255',
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

        if (! empty($validated['parameters'])) {
            $decoded = json_decode($validated['parameters'], true);
            $validated['parameters'] = is_array($decoded) ? $decoded : [];
        } else {
            $validated['parameters'] = [];
        }

        $scopedFacilityId = $this->scopedFacilityId($request);
        if ($scopedFacilityId) {
            $validated['facility_id'] = $scopedFacilityId;
            $validated['parameters']['facility_id'] = $scopedFacilityId;
        }

        $validated['notify_roles'] = $request->input('notify_roles', []);
        $validated['notify_emails'] = $request->input('notify_emails', '');
        $validated['notifications_enabled'] = $request->boolean('notifications_enabled');
        $validated['created_by'] = Auth::id();

        if (empty($validated['facility_id'])) {
            $validated['facility_id'] = null;
        }

        return $validated;
    }

    public function storeTemplate(Request $request)
    {
        $validated = $this->validateTemplatePayload($request);

        ScheduledReportTemplate::create($validated);

        return redirect()
            ->route('admin.scheduled-reports.index')
            ->with('success', 'Scheduled report template saved successfully.');
    }

    public function destroyTemplate(Request $request, ScheduledReportTemplate $scheduledReportTemplate)
    {
        $facilityId = $this->scopedFacilityId($request);

        if ($facilityId && $scheduledReportTemplate->facility_id && (int) $scheduledReportTemplate->facility_id !== $facilityId) {
            abort(403, 'You do not have access to this template.');
        }

        if (! $this->canManageScheduledReports($request) && (int) $scheduledReportTemplate->created_by !== (int) $request->user()->id) {
            abort(403, 'You can only delete templates you created.');
        }

        $scheduledReportTemplate->delete();

        return redirect()
            ->route('admin.scheduled-reports.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function create(Request $request)
    {
        $this->ensureCanManage($request);
        $reports = $this->reportsForUser($request);
        $template = null;

        if ($request->filled('template')) {
            $template = ScheduledReportTemplate::with('report')->find($request->input('template'));
            if ($template) {
                $facilityId = $this->scopedFacilityId($request);
                if ($facilityId && $template->facility_id && (int) $template->facility_id !== $facilityId) {
                    abort(403, 'You do not have access to this template.');
                }
            }
        }

        $prefillFacilityId = $request->integer('facility_id') ?: $this->scopedFacilityId($request);

        return view('admin.scheduled-reports.create', compact('reports', 'template', 'prefillFacilityId'));
    }

    public function store(Request $request)
    {
        $this->ensureCanManage($request);

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

        if (! empty($validated['parameters'])) {
            $decoded = json_decode($validated['parameters'], true);
            $validated['parameters'] = is_array($decoded) ? $decoded : [];
        } else {
            $validated['parameters'] = [];
        }

        $scopedFacilityId = $this->scopedFacilityId($request);
        if ($scopedFacilityId && empty($validated['parameters']['facility_id'])) {
            $validated['parameters']['facility_id'] = $scopedFacilityId;
        }

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
            return back()->withInput()->withErrors(['error' => 'Failed to save scheduled report: '.$e->getMessage()]);
        }
    }

    public function history(Request $request, ScheduledReport $scheduledReport)
    {
        $scheduledReport->load(['report', 'creator']);
        $this->authorizeScheduledReport($request, $scheduledReport);
        $runs = $scheduledReport->runs()->orderByDesc('executed_at')->get();
        $canManageScheduledReports = $this->canManageScheduledReports($request);

        return view('admin.scheduled-reports.history', compact('scheduledReport', 'runs', 'canManageScheduledReports'));
    }

    public function download(Request $request, ScheduledReport $scheduledReport, $runId)
    {
        $this->authorizeScheduledReport($request, $scheduledReport);

        return back()->with('error', 'Download not available. Please re-run the report.');
    }

    public function edit(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $reports = $this->reportsForUser($request);

        return view('admin.scheduled-reports.edit', compact('scheduledReport', 'reports'));
    }

    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'nullable|string',
            'cron_expression' => 'required|string',
            'status' => 'required|in:active,paused',
            'report_format' => 'required|in:csv,pdf,html',
            'pdf_orientation' => 'nullable|in:P,L',
        ]);

        if (! empty($validated['parameters'])) {
            $decoded = json_decode($validated['parameters'], true);
            $validated['parameters'] = is_array($decoded) ? $decoded : [];
        } else {
            $validated['parameters'] = [];
        }

        $validated['pdf_orientation'] = $request->input('pdf_orientation');

        $scheduledReport->update($validated);

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report updated successfully.');
    }

    public function destroy(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $scheduledReport->delete();

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report deleted.');
    }

    public function runNow(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);

        $now = now();
        $report = $scheduledReport->report;
        if (! $report) {
            return back()->with('error', 'Report not found for this schedule.');
        }
        try {
            $parameters = $scheduledReport->parameters ?? [];
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
            $sql = $report->sql_template;
            $results = DB::select($sql, $parameters);
            $format = $scheduledReport->report_format ?? 'csv';
            if ($format === 'csv') {
                $csv = '';
                if (! empty($results)) {
                    $header = array_keys((array) $results[0]);
                    $csv .= implode(',', $header)."\n";
                    foreach ($results as $row) {
                        $csv .= implode(',', array_map(function ($v) {
                            return '"'.str_replace('"', '""', $v).'"';
                        }, (array) $row))."\n";
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
                throw new \Exception('Unsupported report format: '.$format);
            }

            ScheduledReportRun::create([
                'scheduled_report_id' => $scheduledReport->id,
                'executed_at' => $now,
                'result_path' => null,
                'result_json' => json_encode($results),
                'status' => 'success',
                'error_message' => null,
            ]);

            $scheduledReport->last_run_at = $now;
            $scheduledReport->next_run_at = $this->getNextRunAt($scheduledReport->cron_expression, $now);
            $scheduledReport->save();

            $filename = 'scheduled_report_'.$scheduledReport->id.'_'.$now->format('Ymd_His').'.'.$ext;

            return response($content)
                ->header('Content-Type', $mime)
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
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

            return back()->with('error', 'Failed to run scheduled report: '.$e->getMessage());
        }
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
