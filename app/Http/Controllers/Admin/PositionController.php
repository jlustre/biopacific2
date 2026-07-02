<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use App\Services\EmployeeDocumentRequirementsService;
use App\Services\PositionDocumentRequirementsSeedService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(
        protected EmployeeDocumentRequirementsService $documentRequirements
    ) {
    }

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

        $query = Position::with(['department', 'reportsToPosition'])
            ->withCount(['requiredUploadTypes as required_documents_count' => function ($relation) {
                $relation->where('position_upload_type_requirements.is_required', true)
                    ->whereNull('upload_types.checklist_item_id');
            }]);

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
        $uploadTypes = $this->documentRequirements->generalUploadTypesForDepartment(null);

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

        $requiredUploadTypeIds = collect($validated['required_upload_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();
        unset($validated['required_upload_type_ids']);

        $position = Position::create($validated);

        $this->documentRequirements->syncPositionRequirements($position, $requiredUploadTypeIds);

        return redirect()->route('admin.positions.index')->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load(['department', 'reportsToPosition', 'requiredUploadTypes' => function ($query) {
            $query->wherePivot('is_required', true)->whereNull('upload_types.checklist_item_id');
        }]);

        $requiredDocuments = $this->documentRequirements->requiredGeneralTypesSummaryForPosition($position);

        return view('admin.positions.show', compact('position', 'requiredDocuments'));
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
        $uploadTypes = $this->documentRequirements->generalUploadTypesForDepartment((int) $position->department_id);
        $copyTargetPositions = Position::with('department')
            ->where('id', '!=', $position->id)
            ->orderBy('title')
            ->get();

        $position->load(['requiredUploadTypes' => function ($query) {
            $query->wherePivot('is_required', true)->whereNull('upload_types.checklist_item_id');
        }]);

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

        $requiredUploadTypeIds = collect($validated['required_upload_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();
        unset($validated['required_upload_type_ids']);

        $position->update($validated);

        $this->documentRequirements->syncPositionRequirements($position, $requiredUploadTypeIds);

        return redirect()->route('admin.positions.index')->with('success', 'Position updated successfully.');
    }

    public function copyRequirements(Request $request, Position $position)
    {
        $validated = $request->validate([
            'target_position_ids' => 'required|array|min:1',
            'target_position_ids.*' => 'integer|exists:positions,id|different:' . $position->id,
        ]);

        $targets = Position::query()
            ->whereIn('id', $validated['target_position_ids'])
            ->where('id', '!=', $position->id)
            ->get();

        $count = $this->documentRequirements->copyRequirementsToPositions($position, $targets);

        return redirect()
            ->route('admin.positions.edit', $position)
            ->with('success', 'Required documents copied to ' . $count . ' position(s).');
    }

    public function uploadTypesForDepartment(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : null;

        $types = $this->documentRequirements->generalUploadTypesForDepartment($departmentId)
            ->map(fn ($type) => [
                'id' => (int) $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'requires_expiry' => (bool) $type->requires_expiry,
                'department_ids' => $type->department_ids ?? [],
            ])
            ->values();

        return response()->json(['upload_types' => $types]);
    }

    public function seedDocumentRequirements(Request $request, PositionDocumentRequirementsSeedService $seedService)
    {
        if (!$request->user()?->hasRole(['admin', 'super-admin', 'rdhr'])) {
            abort(403);
        }

        $force = $request->boolean('force');
        $result = $seedService->seed(onlyWhenEmpty: ! $force, includeUnmappedPositions: true);

        $message = sprintf(
            'Default document requirements applied to %d position(s). %d skipped (already configured).',
            $result['positions_processed'],
            $result['positions_skipped']
        );

        if (! empty($result['types_missing'])) {
            $message .= ' Some document types were not found — run Documents Management seeder first.';
        }

        return redirect()
            ->route('admin.positions.index')
            ->with('success', $message);
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
