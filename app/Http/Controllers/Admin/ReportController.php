<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
    

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('category');

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
        return view('admin.reports.index', compact('reports', 'categories'));
    }

    public function create()
    {
        return view('admin.reports.form');
    }

    public function show(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        // Handle CSV/PDF download
        if ($request->has('download')) {
            // Try to get params from query string or session, fallback to empty
            $params = $request->query('params', session('last_params', []));
            if (!is_array($params)) {
                $params = [];
            }
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
                $pdf = Pdf::loadView('admin.reports.pdf', compact('report', 'results'));
                return $pdf->stream('report.pdf');
            }
        }
        return view('admin.reports.show', compact('report'));
    }

    public function store(Request $request)
    {
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

    public function edit(Report $report)
    {
        return view('admin.reports.form', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
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

    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted.');
    }

 
    public function run(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $params = $request->input('params', []);
        $outputFormat = $request->input('output_format', 'table');
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
                ->with(['results' => $results, 'output_format' => 'pdf']);
        } else {
            return redirect()
                ->route('admin.reports.show', $report->id)
                ->with(['results' => $results, 'output_format' => 'table']);
        }
    }

    // JSON endpoint for modal fetch
    public function json($id)
    {
        $report = Report::findOrFail($id);
        return response()->json([
            'id' => $report->id,
            'name' => $report->name,
            'description' => $report->description,
            'parameters' => $report->parameters,
        ]);
    }

        /**
     * Validate SQL syntax for the report form (AJAX)
     */
    public function validateSql(Request $request)
    {
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
    public function download(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $params = $request->query('params', []);
        if (!is_array($params)) {
            $params = [];
        }
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
            $pdf = Pdf::loadView('admin.reports.pdf', compact('report', 'results'));
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
