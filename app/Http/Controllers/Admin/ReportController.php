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
    public function index()
    {
        $reports = Report::all();
        return view('admin.reports.index', compact('reports'));
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
            $results = session('results', []);
            if ($request->download === 'csv') {
                $csv = session('csv', '');
                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="report.csv"');
            } elseif ($request->download === 'pdf') {
                $results = session('results', []);
                $pdf = Pdf::loadView('admin.reports.pdf', compact('report', 'results'));
                return $pdf->download('report.pdf');
            }
        }
        return view('admin.reports.show', compact('report'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
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
            'name' => 'required',
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

    // public function show($id)
    // {
    //     $report = Report::findOrFail($id);
    //     return view('admin.reports.show', compact('report'));
    // }

    public function run(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $params = $request->input('params', []);
        $outputFormat = $request->input('output_format', 'table');
        $sql = $report->sql_template;
        foreach ($params as $key => $value) {
            $sql = str_replace(':'.$key, DB::getPdo()->quote($value), $sql);
        }
        $results = DB::select($sql);
        $results = array_map(function($row) { return (array)$row; }, $results);

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
}
