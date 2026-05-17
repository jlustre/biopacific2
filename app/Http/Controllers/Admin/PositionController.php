<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Position::with(['department', 'reportsToPosition']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $positions = $query->orderBy('title')->paginate(15);
        $departments = Department::orderBy('name')->get();

        return view('admin.positions.index', compact('positions', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $reportingPositions = Position::orderBy('title')->get();

        return view('admin.positions.create', compact('departments', 'reportingPositions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:positions',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'reports_to_position_id' => 'nullable|exists:positions,id|different:id',
        ]);

        Position::create($validated);

        return redirect()->route('admin.positions.index')->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load(['department', 'reportsToPosition']);
        return view('admin.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        $departments = Department::orderBy('name')->get();
        $reportingPositions = Position::where('id', '!=', $position->id)
            ->orderBy('title')
            ->get();

        return view('admin.positions.edit', compact('position', 'departments', 'reportingPositions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:positions,title,' . $position->id,
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'reports_to_position_id' => 'nullable|exists:positions,id|different:' . $position->id,
        ]);

        $position->update($validated);

        return redirect()->route('admin.positions.index')->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Position deleted successfully.');
    }

    /**
     * Lookup department and reporting position for a job opening title.
     */
    public function lookup(Request $request)
    {
        $title = trim((string) $request->query('title', ''));

        if ($title === '' || $title === 'Other') {
            return response()->json(['department' => null, 'reporting_to' => null]);
        }

        $position = Position::with(['department', 'reportsToPosition'])
            ->where('title', $title)
            ->first();

        return response()->json([
            'department' => $position?->department?->name,
            'reporting_to' => $position?->reportsToPosition?->title,
        ]);
    }
}
