<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\ScheduledReport;
use App\Models\ScheduledReportRun;
use App\Models\ScheduledReportTemplate;
use App\Models\User;
use App\Support\SelectedFacility;
use App\Services\ScheduledReportRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScheduledReportController extends Controller
{
    /**
     * Facility-bound roles (DSD, facility admin, etc.) are locked to their facility.
     * Admin / super-admin / RDHR may schedule across facilities.
     */
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (SelectedFacility::userCanChooseFacility($user)) {
            return null;
        }

        return SelectedFacility::forcedFacilityIdForUser($user)
            ?? ($user->facility_id ? (int) $user->facility_id : null);
    }

    protected function canManageScheduledReports(Request $request): bool
    {
        return (bool) $request->user()?->hasAnyRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);
    }

    /**
     * Only admin / super-admin create and maintain report templates (including optional parameters).
     */
    protected function canManageReportTemplates(Request $request): bool
    {
        return (bool) $request->user()?->hasAnyRole(['admin', 'super-admin']);
    }

    protected function ensureCanManage(Request $request): void
    {
        if (! $this->canManageScheduledReports($request)) {
            abort(403, 'You do not have permission to manage scheduled reports.');
        }
    }

    protected function ensureCanManageTemplates(Request $request): void
    {
        if (! $this->canManageReportTemplates($request)) {
            abort(403, 'Only administrators can manage report templates.');
        }
    }

    protected function globalReportRoles(): array
    {
        return ['admin', 'super-admin', 'rdhr'];
    }

    protected function userFacilityIds(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $facilityIds = collect();

        if (method_exists($user, 'facilities')) {
            $facilityIds = $user->facilities()->pluck('facilities.id');
        }

        if ($user->facility_id) {
            $facilityIds->push((int) $user->facility_id);
        }

        $forced = SelectedFacility::forcedFacilityIdForUser($user);
        if ($forced) {
            $facilityIds->push($forced);
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        if ($employee?->currentAssignment?->facility_id) {
            $facilityIds->push((int) $employee->currentAssignment->facility_id);
        }

        return $facilityIds->filter()->unique()->values();
    }

    protected function reportsForUser(Request $request)
    {
        $user = $request->user();

        if ($user?->hasRole(['admin', 'super-admin'])) {
            return Report::orderBy('name')->get();
        }

        $roles = $user?->getRoleNames()->values()->all() ?? [];
        $facilityIds = $this->userFacilityIds($user);

        return Report::query()
            ->where('is_active', true)
            ->where(function ($q) use ($roles, $facilityIds, $user) {
                if ($user?->hasRole($this->globalReportRoles())) {
                    $q->orWhereIn('visibility', ['admin', 'all']);
                } else {
                    $q->orWhere('visibility', 'all');
                }

                if ($roles !== []) {
                    $q->orWhere(function ($roleQuery) use ($roles) {
                        $roleQuery->where('visibility', 'roles')
                            ->where(function ($jsonQuery) use ($roles) {
                                foreach ($roles as $role) {
                                    $jsonQuery->orWhereJsonContains('visible_roles', $role);
                                }
                            });
                    });
                }

                if ($facilityIds->isNotEmpty()) {
                    $q->orWhere(function ($facilityQuery) use ($facilityIds) {
                        $facilityQuery->where('visibility', 'facilities')
                            ->where(function ($jsonQuery) use ($facilityIds) {
                                foreach ($facilityIds as $facilityId) {
                                    $jsonQuery->orWhereJsonContains('visible_facilities', (int) $facilityId);
                                }
                            });
                    });
                }
            })
            ->orderBy('name')
            ->get();
    }

    protected function ensureReportAccessible(Request $request, int $reportId): void
    {
        $allowed = $this->reportsForUser($request)->contains(fn (Report $report) => (int) $report->id === $reportId);

        abort_unless($allowed, 403, 'You do not have access to this report.');
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    protected function applyScopedParameters(Request $request, array $parameters): array
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            $parameters['facility_id'] = $scopedFacilityId;

            return $parameters;
        }

        $paramFacilityId = isset($parameters['facility_id']) ? (int) $parameters['facility_id'] : null;

        if ($paramFacilityId && ! SelectedFacility::userCanAccessFacility($request->user(), $paramFacilityId)) {
            abort(403, 'You do not have access to that facility.');
        }

        return $parameters;
    }

    protected function decodeParameters(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function applyFacilityScopeToScheduledReportsQuery($query, Request $request)
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return $query;
        }

        return $query->where(function ($q) use ($facilityId) {
            $q->where(function ($pq) use ($facilityId) {
                $pq->where('parameters->facility_id', $facilityId)
                    ->orWhere('parameters->facility_id', (string) $facilityId);
            })->orWhere(function ($cq) use ($facilityId) {
                $cq->whereNull('parameters->facility_id')
                    ->whereHas('creator', fn ($uq) => $uq->where('facility_id', $facilityId));
            });
        });
    }

    public function authorizeScheduledReport(Request $request, ScheduledReport $scheduledReport): void
    {
        $facilityId = $this->scopedFacilityId($request);

        if (! $facilityId) {
            return;
        }

        $scheduledReport->loadMissing(['creator']);
        $parameters = $scheduledReport->parameters ?? [];
        $paramFacilityId = isset($parameters['facility_id']) ? (int) $parameters['facility_id'] : null;

        $allowed = ($paramFacilityId === $facilityId)
            || ($paramFacilityId === null
                && $scheduledReport->creator
                && (int) $scheduledReport->creator->facility_id === $facilityId);

        abort_unless($allowed, 403, 'You do not have access to this scheduled report.');
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
        $canManageReportTemplates = $this->canManageReportTemplates($request);

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
            'canManageReportTemplates',
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
        $this->ensureCanManageTemplates($request);

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

        $this->ensureReportAccessible($request, (int) $validated['report_id']);

        $validated['parameters'] = $this->applyScopedParameters(
            $request,
            $this->decodeParameters($validated['parameters'] ?? null)
        );

        $scopedFacilityId = $this->scopedFacilityId($request);
        if ($scopedFacilityId) {
            $validated['facility_id'] = $scopedFacilityId;
        } elseif (! empty($validated['facility_id'])
            && ! SelectedFacility::userCanAccessFacility($request->user(), (int) $validated['facility_id'])) {
            abort(403, 'You do not have access to that facility.');
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
        $this->ensureCanManageTemplates($request);

        $facilityId = $this->scopedFacilityId($request);

        if ($facilityId && $scheduledReportTemplate->facility_id && (int) $scheduledReportTemplate->facility_id !== $facilityId) {
            abort(403, 'You do not have access to this template.');
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
        $scopedFacilityId = $this->scopedFacilityId($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        if ($request->filled('template')) {
            $template = ScheduledReportTemplate::with('report')->find($request->input('template'));
            if ($template) {
                if ($scopedFacilityId && $template->facility_id && (int) $template->facility_id !== $scopedFacilityId) {
                    abort(403, 'You do not have access to this template.');
                }
            }
        }

        $requestedFacilityId = $request->integer('facility_id') ?: null;
        if ($scopedFacilityId) {
            $prefillFacilityId = $scopedFacilityId;
        } elseif ($requestedFacilityId && SelectedFacility::userCanAccessFacility($request->user(), $requestedFacilityId)) {
            $prefillFacilityId = $requestedFacilityId;
        } else {
            $prefillFacilityId = null;
        }

        return view('admin.scheduled-reports.create', compact(
            'reports',
            'template',
            'prefillFacilityId',
            'scopedFacilityId',
            'scopedFacility'
        ));
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

        $this->ensureReportAccessible($request, (int) $validated['report_id']);

        $validated['created_by'] = Auth::id();
        $validated['parameters'] = $this->applyScopedParameters(
            $request,
            $this->decodeParameters($validated['parameters'] ?? null)
        );

        $validated['notify_roles'] = $request->input('notify_roles', []);
        $validated['notify_emails'] = $request->input('notify_emails', '');
        $validated['start_at'] = $request->input('start_at');
        $validated['end_at'] = $request->input('end_at');
        $validated['notifications_enabled'] = $request->has('notifications_enabled') ? 1 : 0;
        $validated['pdf_orientation'] = $request->input('pdf_orientation');

        try {
            $validated['next_run_at'] = app(ScheduledReportRunner::class)
                ->getNextRunAt($validated['cron_expression'], now());

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

        $run = ScheduledReportRun::where('scheduled_report_id', $scheduledReport->id)->findOrFail($runId);
        if ($run->status !== 'success') {
            return back()->with('error', 'This run did not complete successfully, so no file is available.');
        }

        try {
            $export = app(ScheduledReportRunner::class)->exportForRun($scheduledReport, $run);

            return response($export['content'])
                ->header('Content-Type', $export['mime'])
                ->header('Content-Disposition', 'attachment; filename="'.$export['filename'].'"');
        } catch (\Throwable $e) {
            Log::error('Scheduled report download failed', [
                'scheduled_report_id' => $scheduledReport->id,
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Download failed: '.$e->getMessage());
        }
    }

    public function edit(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $this->authorizeScheduledReport($request, $scheduledReport);
        $reports = $this->reportsForUser($request);
        $scopedFacilityId = $this->scopedFacilityId($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.scheduled-reports.edit', compact(
            'scheduledReport',
            'reports',
            'scopedFacilityId',
            'scopedFacility'
        ));
    }

    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $this->authorizeScheduledReport($request, $scheduledReport);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'nullable|string',
            'cron_expression' => 'required|string',
            'status' => 'required|in:active,paused',
            'report_format' => 'required|in:csv,pdf,html',
            'pdf_orientation' => 'nullable|in:P,L',
        ]);

        $this->ensureReportAccessible($request, (int) $validated['report_id']);

        $validated['parameters'] = $this->applyScopedParameters(
            $request,
            $this->decodeParameters($validated['parameters'] ?? null)
        );
        $validated['pdf_orientation'] = $request->input('pdf_orientation');
        $validated['next_run_at'] = app(ScheduledReportRunner::class)
            ->getNextRunAt($validated['cron_expression'], now());

        $scheduledReport->update($validated);

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report updated successfully.');
    }

    public function destroy(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $this->authorizeScheduledReport($request, $scheduledReport);
        $scheduledReport->delete();

        return redirect()->route('admin.scheduled-reports.index')->with('success', 'Scheduled report deleted.');
    }

    public function runNow(Request $request, ScheduledReport $scheduledReport)
    {
        $this->ensureCanManage($request);
        $this->authorizeScheduledReport($request, $scheduledReport);

        $report = $scheduledReport->report;
        if (! $report) {
            return back()->with('error', 'Report not found for this schedule.');
        }

        $runner = app(ScheduledReportRunner::class);
        $parameters = $this->applyScopedParameters($request, $scheduledReport->parameters ?? []);
        $run = $runner->execute($scheduledReport, $parameters, true, false);

        if ($run->status !== 'success') {
            return back()->with('error', 'Failed to run scheduled report: '.($run->error_message ?: 'Unknown error'));
        }

        try {
            $export = $runner->exportForRun($scheduledReport, $run);

            return response($export['content'])
                ->header('Content-Type', $export['mime'])
                ->header('Content-Disposition', 'attachment; filename="'.$export['filename'].'"');
        } catch (\Throwable $e) {
            Log::error('Manual scheduled report download failed', [
                'id' => $scheduledReport->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.scheduled-reports.history', $scheduledReport)
                ->with('success', 'Report ran and was saved to history, but the download failed: '.$e->getMessage());
        }
    }

    protected function getNextRunAt($cron, $from)
    {
        return app(ScheduledReportRunner::class)->getNextRunAt($cron, $from);
    }
}
