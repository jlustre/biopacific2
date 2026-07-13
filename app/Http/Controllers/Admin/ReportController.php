<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportSeederExporter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
    

class ReportController extends Controller
{
    protected function userCanManageReports(?User $user): bool
    {
        return (bool) $user?->hasRole(['admin', 'super-admin']);
    }

    protected function globalReportRoles(): array
    {
        return ['admin', 'super-admin', 'rdhr'];
    }

    protected function userFacilityIds(?User $user)
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

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        if ($employee?->currentAssignment?->facility_id) {
            $facilityIds->push((int) $employee->currentAssignment->facility_id);
        }

        return $facilityIds->filter()->unique()->values();
    }

    protected function applyReportAccessScope($query, Request $request)
    {
        $user = $request->user();

        if ($this->userCanManageReports($user)) {
            return $query;
        }

        $roles = $user?->getRoleNames()->values()->all() ?? [];
        $facilityIds = $this->userFacilityIds($user);

        return $query
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
            });
    }

    protected function authorizeReportAccess(Request $request, Report $report): void
    {
        $allowed = $this->applyReportAccessScope(Report::query(), $request)
            ->whereKey($report->getKey())
            ->exists();

        abort_unless($allowed, 403, 'You do not have access to this report.');
    }

    protected function ensureCanManageReports(Request $request): void
    {
        abort_unless(
            $this->userCanManageReports($request->user()),
            403,
            'You do not have permission to manage reports.'
        );
    }

    /**
     * Force facility scope and reject corporate org filters for facility-bound roles.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function scopedRunParameters(Request $request, array $params): array
    {
        $user = $request->user();

        if ($user?->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return $params;
        }

        $forcedFacilityId = \App\Support\SelectedFacility::forcedFacilityIdForUser($user)
            ?? \App\Support\SelectedFacility::id($request);

        abort_unless(
            $forcedFacilityId,
            403,
            'No facility is assigned to your account for facility-scoped reports.'
        );

        $params['facility_id'] = $forcedFacilityId;

        $departmentId = isset($params['department_id']) ? (int) $params['department_id'] : 0;
        if ($departmentId > 0) {
            $allowed = \App\Models\Department::query()
                ->whereKey($departmentId)
                ->where('type', 'facility')
                ->exists();
            abort_unless($allowed, 403, 'You do not have access to that department.');
        }

        foreach (['position_id', 'reports_to'] as $positionKey) {
            $positionId = isset($params[$positionKey]) ? (int) $params[$positionKey] : 0;
            if ($positionId <= 0) {
                continue;
            }

            $allowed = \App\Models\Position::query()
                ->whereKey($positionId)
                ->whereHas('department', fn ($q) => $q->where('type', 'facility'))
                ->exists();
            abort_unless($allowed, 403, 'You do not have access to that position.');
        }

        return $params;
    }

    protected function defaultPdfOrientation(Report $report): string
    {
        return str_contains($report->name, 'Expiring Licenses & Certifications')
            ? 'landscape'
            : 'portrait';
    }

    protected function pdfOrientation(Request $request, Report $report): string
    {
        $orientation = strtolower((string) $request->input(
            'pdf_orientation',
            $request->query('pdf_orientation', $this->defaultPdfOrientation($report))
        ));

        return in_array($orientation, ['portrait', 'landscape'], true)
            ? $orientation
            : $this->defaultPdfOrientation($report);
    }

    protected function pdfViewData(Request $request, Report $report, array $results, string $pdfOrientation): array
    {
        return [
            'report' => $report,
            'results' => $results,
            'pdfOrientation' => $pdfOrientation,
            'logoPath' => public_path('images/bplogo.png'),
            'generatedAt' => now(),
            'generatedBy' => $this->generatedByLabel($request),
            'dateScope' => $this->reportDateScope($request, $report, $results),
        ];
    }

    protected function generatedByLabel(Request $request): string
    {
        $user = $request->user();

        if (! $user) {
            return 'System';
        }

        return trim($user->name . ' (' . $user->email . ')');
    }

    protected function reportDateScope(Request $request, Report $report, array $results): string
    {
        $params = $request->input('params', $request->query('params', []));
        $params = is_array($params) ? $params : [];
        $from = $params['date_from']
            ?? $params['start_date']
            ?? $params['from_date']
            ?? $params['from']
            ?? null;
        $to = $params['date_to']
            ?? $params['end_date']
            ?? $params['to_date']
            ?? $params['to']
            ?? null;

        if ($from || $to) {
            return trim(($from ?: 'Beginning') . ' to ' . ($to ?: 'Present'));
        }

        if (str_contains($report->name, 'Expiring Licenses & Certifications')) {
            return 'Expired items and items expiring through ' . now()->addDays(120)->format('M j, Y');
        }

        $dateValues = collect($results)
            ->flatMap(function (array $row) {
                return collect($row)
                    ->filter(fn ($value, $key) => str_contains((string) $key, 'date') || str_contains((string) $key, '_at'))
                    ->values();
            })
            ->filter()
            ->map(function ($value) {
                try {
                    return Carbon::parse((string) $value)->startOfDay();
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->sort()
            ->values();

        if ($dateValues->isNotEmpty()) {
            return $dateValues->first()->format('M j, Y') . ' to ' . $dateValues->last()->format('M j, Y');
        }

        return 'All available records';
    }

    public function index(Request $request)
    {
        $query = Report::with('category');
        $this->applyReportAccessScope($query, $request);

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%") ;
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $reports = $query->orderBy('name')->paginate(10)->withQueryString();
        $categories = \App\Models\ReportCategory::orderBy('name')->get();
        $user = $request->user();
        $canManageReports = $this->userCanManageReports($user);
        $canFilterAllFacilities = (bool) $user?->hasRole(['admin', 'super-admin', 'rdhr']);
        $canScheduleReports = $canManageReports
            || (bool) $user?->hasAnyRole(['rdhr', 'facility-admin', 'facility-dsd']);

        $forcedFacilityId = \App\Support\SelectedFacility::forcedFacilityIdForUser($user)
            ?? \App\Support\SelectedFacility::id($request);

        if ($canFilterAllFacilities) {
            $facilities = \App\Models\Facility::query()->orderBy('name')->get(['id', 'name']);
            $selectedFacilityId = (int) $request->input('facility_id', 0);
            $departments = \App\Models\Department::query()->orderBy('name')->get(['id', 'name']);
            $positions = \App\Models\Position::query()->orderBy('title')->get(['id', 'title']);
            $supervisorPositions = \App\Models\Position::query()
                ->supervisorRoles()
                ->orderBy('title')
                ->get(['id', 'title']);
        } else {
            $lockedFacility = $forcedFacilityId
                ? \App\Models\Facility::query()->find($forcedFacilityId)
                : null;
            $facilities = $lockedFacility ? collect([$lockedFacility]) : collect();
            $selectedFacilityId = (int) ($lockedFacility?->id ?? 0);

            // Facility roles (DSD, etc.) only see facility org options — not corporate.
            $departments = \App\Models\Department::query()
                ->where('type', 'facility')
                ->orderBy('name')
                ->get(['id', 'name']);
            $positions = \App\Models\Position::query()
                ->whereHas('department', fn ($q) => $q->where('type', 'facility'))
                ->orderBy('title')
                ->get(['id', 'title']);
            $supervisorPositions = \App\Models\Position::query()
                ->supervisorRoles()
                ->whereHas('department', fn ($q) => $q->where('type', 'facility'))
                ->orderBy('title')
                ->get(['id', 'title']);
        }

        $selectedDepartmentId = (int) $request->input('department_id', 0);
        $selectedPositionId = (int) $request->input('position_id', 0);
        $selectedReportsTo = (int) $request->input('reports_to', 0);

        if (! $canFilterAllFacilities) {
            $allowedDepartmentIds = $departments->pluck('id')->map(fn ($id) => (int) $id);
            $allowedPositionIds = $positions->pluck('id')->map(fn ($id) => (int) $id);
            $allowedSupervisorIds = $supervisorPositions->pluck('id')->map(fn ($id) => (int) $id);

            if ($selectedDepartmentId && ! $allowedDepartmentIds->contains($selectedDepartmentId)) {
                $selectedDepartmentId = 0;
            }
            if ($selectedPositionId && ! $allowedPositionIds->contains($selectedPositionId)) {
                $selectedPositionId = 0;
            }
            if ($selectedReportsTo && ! $allowedSupervisorIds->contains($selectedReportsTo)) {
                $selectedReportsTo = 0;
            }
        }

        return view('admin.reports.index', compact(
            'reports',
            'categories',
            'canManageReports',
            'canScheduleReports',
            'canFilterAllFacilities',
            'facilities',
            'departments',
            'positions',
            'supervisorPositions',
            'selectedFacilityId',
            'selectedDepartmentId',
            'selectedPositionId',
            'selectedReportsTo',
        ));
    }

    public function create(Request $request)
    {
        $this->ensureCanManageReports($request);

        return view('admin.reports.form');
    }

    public function show(Request $request, Report $report)
    {
        $this->authorizeReportAccess($request, $report);

        // Handle CSV/PDF download
        if ($request->has('download')) {
            // Try to get params from query string or session, fallback to empty
            $params = $request->query('params', session('last_params', []));
            if (!is_array($params)) {
                $params = [];
            }
            $params = $this->scopedRunParameters($request, $params);
            $sql = $report->sql_template;
            foreach ($params as $key => $value) {
                $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
            }
            $results = DB::select($sql);
            $results = array_map(function($row) { return (array)$row; }, $results);
            if ($request->download === 'csv') {
                $csv = '';
                if (!empty($results)) {
                    $csv .= implode(',', array_keys($results[0])) . "\n";
                    foreach ($results as $row) {
                        $csv .= implode(',', array_map(function($v) {
                            return '"' . str_replace('"', '""', $v) . '"';
                        }, $row)) . "\n";
                    }
                }
                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="report.csv"');
            } elseif ($request->download === 'pdf') {
                $pdfOrientation = $this->pdfOrientation($request, $report);
                $pdf = Pdf::loadView('admin.reports.pdf', $this->pdfViewData($request, $report, $results, $pdfOrientation))
                    ->setPaper('a4', $pdfOrientation);
                return $pdf->stream('report.pdf');
            }
        }
        $canManageReports = $this->userCanManageReports($request->user());

        return view('admin.reports.show', compact('report', 'canManageReports'));
    }

    public function store(Request $request)
    {
        $this->ensureCanManageReports($request);

        $data = $request->validate([
            'category_id' => 'required|exists:report_categories,id',
                'name' => 'required|string|max:255|unique:reports,name',
            'description' => 'nullable',
            'sql_template' => 'required',
            'parameters' => 'nullable',
            'is_active' => 'boolean',
            'visibility' => 'required|in:admin,all,roles,facilities',
            'visible_roles' => 'array',
            'visible_roles.*' => 'string',
            'visible_facilities' => 'array',
            'visible_facilities.*' => 'integer',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['parameters'] = $data['parameters'] ? json_decode($data['parameters'], true) : [];
        // Only keep relevant visibility fields
        if ($data['visibility'] !== 'roles') {
            $data['visible_roles'] = [];
        } else {
            $data['visible_roles'] = $request->input('visible_roles', []);
        }
        if ($data['visibility'] !== 'facilities') {
            $data['visible_facilities'] = [];
        } else {
            $data['visible_facilities'] = $request->input('visible_facilities', []);
        }
        Report::create($data);
        return redirect()->route('admin.reports.index')->with('success', 'Report created.');
    }

    public function edit(Request $request, Report $report)
    {
        $this->ensureCanManageReports($request);

        return view('admin.reports.form', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $this->ensureCanManageReports($request);

        $data = $request->validate([
            'category_id' => 'required|exists:report_categories,id',
                'name' => 'required|string|max:255|unique:reports,name,' . $report->id,
            'description' => 'nullable',
            'sql_template' => 'required',
            'parameters' => 'nullable',
            'is_active' => 'boolean',
            'visibility' => 'required|in:admin,all,roles,facilities',
            'visible_roles' => 'array',
            'visible_roles.*' => 'string',
            'visible_facilities' => 'array',
            'visible_facilities.*' => 'integer',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['parameters'] = $data['parameters'] ? json_decode($data['parameters'], true) : [];
        if ($data['visibility'] !== 'roles') {
            $data['visible_roles'] = [];
        } else {
            $data['visible_roles'] = $request->input('visible_roles', []);
        }
        if ($data['visibility'] !== 'facilities') {
            $data['visible_facilities'] = [];
        } else {
            $data['visible_facilities'] = $request->input('visible_facilities', []);
        }
        $report->update($data);
        return redirect()->route('admin.reports.index')->with('success', 'Report updated.');
    }

    public function destroy(Request $request, Report $report)
    {
        $this->ensureCanManageReports($request);

        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted.');
    }

    public function syncSeeder(Request $request, ReportSeederExporter $exporter)
    {
        $this->ensureCanManageReports($request);

        try {
            $result = $exporter->writeSeederFile();

            return redirect()
                ->route('admin.reports.index')
                ->with(
                    'success',
                    'Report seeder updated with ' . $result['count'] . ' report(s).'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.reports.index')
                ->with('error', 'Failed to update report seeder: ' . $e->getMessage());
        }
    }

 
    public function run(Request $request, Report $report)
    {
        $this->authorizeReportAccess($request, $report);

        $params = $this->scopedRunParameters($request, $request->input('params', []));
        $outputFormat = $request->input('output_format', 'table');
        $pdfOrientation = $this->pdfOrientation($request, $report);
        $sql = $report->sql_template;
        foreach ($params as $key => $value) {
            // Cast numeric values to int/float for SQL
            if (is_numeric($value)) {
                $value = strpos($value, '.') !== false ? (float)$value : (int)$value;
            }
            $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
        }
        try {
            $results = DB::select($sql);
            $results = array_map(function($row) { return (array)$row; }, $results);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['results' => [], 'error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['sql' => $e->getMessage()]);
        }

        // Store last params in session for download
        session(['last_params' => $params]);

        // If AJAX/JSON request, return JSON
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['results' => $results]);
        }

        if ($outputFormat === 'csv') {
            $csv = '';
            if (!empty($results)) {
                $csv .= implode(',', array_keys($results[0])) . "\n";
                foreach ($results as $row) {
                    $csv .= implode(',', array_map(function($v) {
                        return '"' . str_replace('"', '""', $v) . '"';
                    }, $row)) . "\n";
                }
            }
            return redirect()
                ->route('admin.reports.show', $report->id)
                ->with(['results' => $results, 'output_format' => 'csv', 'csv' => $csv]);
        } elseif ($outputFormat === 'json') {
            return redirect()
                ->route('admin.reports.show', $report->id)
                ->with(['results' => $results, 'output_format' => 'json']);
        } elseif ($outputFormat === 'pdf') {
            return redirect()
                ->route('admin.reports.show', $report->id)
                ->with(['results' => $results, 'output_format' => 'pdf', 'pdf_orientation' => $pdfOrientation]);
        } else {
            return redirect()
                ->route('admin.reports.show', $report->id)
                ->with(['results' => $results, 'output_format' => 'table']);
        }
    }

    // JSON endpoint for modal fetch
    public function json(Request $request, Report $report)
    {
        $this->authorizeReportAccess($request, $report);

        return response()->json([
            'id' => $report->id,
            'name' => $report->name,
            'description' => $report->description,
            'parameters' => $report->parameters,
            'default_pdf_orientation' => $this->defaultPdfOrientation($report),
        ]);
    }

        /**
     * Validate SQL syntax for the report form (AJAX)
     */
    public function validateSql(Request $request)
    {
        $this->ensureCanManageReports($request);

        $sql = $request->input('sql');
        // Remove parameters like :param for validation
        $sqlForValidation = preg_replace('/:[a-zA-Z0-9_]+/', 'NULL', $sql);
        try {
            // Use DB::select with LIMIT 0 to check syntax only (no data returned)
            $testSql = $sqlForValidation;
            if (!preg_match('/limit\s+\d+/i', $testSql)) {
                $testSql .= ' LIMIT 0';
            }
            DB::select($testSql);
            return response()->json(['valid' => true]);
        } catch (\Throwable $e) {
            return response()->json(['valid' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle GET download for PDF/CSV/JSON output.
     */
    public function download(Request $request, Report $report)
    {
        $this->authorizeReportAccess($request, $report);

        $params = $request->query('params', []);
        if (!is_array($params)) {
            $params = [];
        }
        $params = $this->scopedRunParameters($request, $params);
        $sql = $report->sql_template;
        foreach ($params as $key => $value) {
            $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
        }
        $results = DB::select($sql);
        $results = array_map(function($row) { return (array)$row; }, $results);
        $outputFormat = $request->query('download', 'table');
        if ($outputFormat === 'csv') {
            $csv = '';
            if (!empty($results)) {
                $csv .= implode(',', array_keys($results[0])) . "\n";
                foreach ($results as $row) {
                    $csv .= implode(',', array_map(function($v) {
                        return '"' . str_replace('"', '""', $v) . '"';
                    }, $row)) . "\n";
                }
            }
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="report.csv"');
        } elseif ($outputFormat === 'pdf') {
            $pdfOrientation = $this->pdfOrientation($request, $report);
            $pdf = Pdf::loadView('admin.reports.pdf', $this->pdfViewData($request, $report, $results, $pdfOrientation))
                ->setPaper('a4', $pdfOrientation);
            return $pdf->stream('report.pdf');
        } elseif ($outputFormat === 'json') {
            return response()->json($results);
        } else {
            // Table: fallback to show page
            return redirect()->route('admin.reports.show', $report->id);
        }
    }

       /**
     * Handle report requisition requests from non-admin users.
     */
    public function requestReport(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email',
            'report_title' => 'required|string|max:255',
            'report_description' => 'required|string',
            'sample_columns' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Find admin emails (all users with admin role)
        $admins = \App\Models\User::role('admin')->get();
        $adminEmails = $admins->pluck('email')->all();

        // Send notification email to all admins
        \Mail::send([], [], function ($message) use ($data, $adminEmails) {
            $message->to($adminEmails)
                ->subject('New Report Template Request')
                ->setBody(
                    "A user has requested a new report template.\n\n" .
                    "Name: {$data['user_name']}\n" .
                    "Email: {$data['user_email']}\n" .
                    "Report Title: {$data['report_title']}\n" .
                    "Description: {$data['report_description']}\n" .
                    "Sample Columns: " . ($data['sample_columns'] ?? '-') . "\n" .
                    "Notes: " . ($data['notes'] ?? '-') . "\n",
                    'text/plain'
                );
        });

        return response()->json([
            'success' => true,
            'message' => 'Your request has been sent to the admin.'
        ]);
    }
}
