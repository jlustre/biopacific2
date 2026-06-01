<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use App\Models\UploadType;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isDon = $user?->hasRole('don') ?? false;
        $nursingDepartmentId = $isDon
            ? Department::query()->where('name', 'Nursing')->value('id')
            : null;

        if ($isDon && $nursingDepartmentId) {
            $request->merge(['department' => $request->input('department', $nursingDepartmentId)]);
        }

        $query = Position::with(['department', 'reportsToPosition']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        } elseif ($nursingDepartmentId) {
            $query->where('department_id', $nursingDepartmentId);
        }

        $positions = $query->orderBy('title')->paginate(15);
        $departments = $this->availableDepartmentsForUser($user);
        $selectedDepartmentId = $request->input('department');

        return view('admin.positions.index', compact('positions', 'departments', 'selectedDepartmentId', 'isDon'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = request()->user();
        $departments = $this->availableDepartmentsForUser($user);
        $reportingPositions = Position::orderBy('title')->get();
        $uploadTypes = UploadType::orderBy('name')->get();

        return view('admin.positions.create', compact('departments', 'reportingPositions', 'uploadTypes'));
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
            'required_upload_type_ids' => 'nullable|array',
            'required_upload_type_ids.*' => 'integer|exists:upload_types,id',
        ]);

        $requiredUploadTypeIds = collect($validated['required_upload_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        unset($validated['required_upload_type_ids']);

        $position = Position::create($validated);

        $allowedUploadTypeIds = $this->availableUploadTypesForDepartment((int) $position->department_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $position->requiredUploadTypes()->sync(
            $requiredUploadTypeIds
                ->intersect($allowedUploadTypeIds)
                ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
                ->all()
        );

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
        $user = request()->user();
        $departments = $this->availableDepartmentsForUser($user);
        $reportingPositions = Position::where('id', '!=', $position->id)
            ->orderBy('title')
            ->get();
        $uploadTypes = $this->availableUploadTypesForDepartment((int) $position->department_id);
        $copyTargetPositions = Position::with('department')
            ->where('id', '!=', $position->id)
            ->orderBy('title')
            ->get();

        $position->load(['requiredUploadTypes']);

        return view('admin.positions.edit', compact('position', 'departments', 'reportingPositions', 'uploadTypes', 'copyTargetPositions'));
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
            'required_upload_type_ids' => 'nullable|array',
            'required_upload_type_ids.*' => 'integer|exists:upload_types,id',
        ]);

        $requiredUploadTypeIds = collect($validated['required_upload_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        unset($validated['required_upload_type_ids']);

        $position->update($validated);

        $allowedUploadTypeIds = $this->availableUploadTypesForDepartment((int) $position->department_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $position->requiredUploadTypes()->sync(
            $requiredUploadTypeIds
                ->intersect($allowedUploadTypeIds)
                ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
                ->all()
        );

        return redirect()->route('admin.positions.index')->with('success', 'Position updated successfully.');
    }

    public function copyRequirements(Request $request, Position $position)
    {
        $validated = $request->validate([
            'target_position_ids' => 'required|array|min:1',
            'target_position_ids.*' => 'integer|exists:positions,id|different:' . $position->id,
        ]);

        $sourceRequiredUploadTypeIds = $position->requiredUploadTypes()
            ->wherePivot('is_required', true)
            ->pluck('upload_types.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $targets = Position::query()
            ->whereIn('id', $validated['target_position_ids'])
            ->where('id', '!=', $position->id)
            ->get();

        foreach ($targets as $target) {
            $allowedUploadTypeIds = $this->availableUploadTypesForDepartment((int) $target->department_id)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $payload = $sourceRequiredUploadTypeIds
                ->intersect($allowedUploadTypeIds)
                ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
                ->all();

            $target->requiredUploadTypes()->sync($payload);
        }

        return redirect()
            ->route('admin.positions.edit', $position)
            ->with('success', 'Required documents copied to ' . $targets->count() . ' position(s).');
    }

    private function availableUploadTypesForDepartment(int $departmentId)
    {
        return UploadType::query()
            ->where(function ($query) use ($departmentId) {
                $query->whereNull('department_ids')
                    ->orWhereJsonLength('department_ids', 0)
                    ->orWhereJsonContains('department_ids', $departmentId);
            })
            ->orderBy('name')
            ->get();
    }

    private function availableDepartmentsForUser($user)
    {
        $query = Department::query();

        if ($user?->hasRole('don')) {
            $query->where('name', 'Nursing');
        }

        return $query->orderBy('name')->get();
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
