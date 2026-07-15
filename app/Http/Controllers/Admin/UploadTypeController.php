<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\Department;
use App\Models\DocType;
use App\Models\Position;
use App\Models\UploadType;
use App\Services\ChecklistUploadTypeSyncService;
use App\Services\DocumentsManagementSeedService;
use App\Services\DocumentsManagementSeederExporter;
use App\Services\PositionRequirementPresetService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UploadTypeController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'requirements');
        if (! in_array($tab, ['types', 'items', 'requirements'], true)) {
            $tab = 'requirements';
        }

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $employeeFileSections = ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS;
        $positions = Position::query()->with('department')->where('is_active', true)->orderBy('title')->get();
        $uploadTypes = null;
        $checklistItems = null;
        $docTypes = collect();
        $itemSections = collect();
        $documentSetCatalog = [];
        $positionGroupCatalog = [];
        $generalUploadTypes = collect();
        $requirementOverview = [];
        $selectedRequirementPosition = null;
        $selectedRequirementUploadTypeIds = [];

        if ($tab === 'requirements') {
            $presetService = app(PositionRequirementPresetService::class);
            $documentSetCatalog = $presetService->documentSetCatalog();
            $positionGroupCatalog = $presetService->positionGroupCatalog();
            $departmentFilter = $request->filled('department_id') ? (int) $request->input('department_id') : null;
            $overviewSearch = trim((string) $request->input('search', ''));
            $generalUploadTypes = UploadType::query()
                ->generalPositionAssignable()
                ->when(
                    $departmentFilter,
                    fn ($query) => $query->where(function ($scope) use ($departmentFilter) {
                        $scope->whereNull('department_ids')
                            ->orWhereJsonLength('department_ids', 0)
                            ->orWhereJsonContains('department_ids', $departmentFilter);
                    })
                )
                ->orderBy('name')
                ->get(['id', 'name', 'requires_expiry', 'is_license_or_certification', 'department_ids']);
            $requirementOverview = $presetService->paginatePositionRequirementOverview(
                $departmentFilter,
                $overviewSearch !== '' ? $overviewSearch : null,
            );
            $selectedPositionId = $request->filled('position_id') ? (int) $request->input('position_id') : null;
            $selectedRequirementPosition = $selectedPositionId
                ? $positions->firstWhere('id', $selectedPositionId)
                : null;
            if ($selectedRequirementPosition) {
                $selectedRequirementUploadTypeIds = app(\App\Services\EmployeeDocumentRequirementsService::class)
                    ->requiredGeneralUploadTypeIdsForPosition($selectedRequirementPosition);
            }
        } elseif ($tab === 'items') {
            $itemsQuery = ChecklistItem::query()->with('docType');

            if ($request->filled('search')) {
                $itemsQuery->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('section')) {
                $itemsQuery->where('section', $request->section);
            }

            if ($request->filled('doc_type_id')) {
                $itemsQuery->where('doc_type_id', $request->doc_type_id);
            }

            $checklistItems = $itemsQuery->orderBy('section')
                ->orderBy('order')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

            $docTypes = DocType::query()->orderBy('name')->get();
            $itemSections = ChecklistItem::query()
                ->select('section')
                ->distinct()
                ->whereNotNull('section')
                ->orderBy('section')
                ->pluck('section');
        } else {
            $query = UploadType::query()->with('checklistItem')->orderedForDisplay();

            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($scope) use ($search): void {
                    $scope->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('checklist_section')) {
                $section = (string) $request->input('checklist_section');
                if ($section === 'general') {
                    $query->generalDocumentTypes();
                } elseif (in_array($section, ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS, true)) {
                    $query->where('checklist_section', $section);
                }
            }

            if ($request->filled('department_id')) {
                $departmentId = (int) $request->input('department_id');
                $query->whereJsonContains('department_ids', $departmentId);
            }

            $requiresExpiry = $request->input('requires_expiry');
            if (in_array($requiresExpiry, ['0', '1', 0, 1], true)) {
                $query->where('requires_expiry', (int) $requiresExpiry);
            }

            $isLicenseOrCertification = $request->input('is_license_or_certification');
            if (in_array($isLicenseOrCertification, ['0', '1', 0, 1], true)) {
                $query->where('is_license_or_certification', (int) $isLicenseOrCertification);
            }

            $uploadTypes = $query->paginate(20)->withQueryString();
        }

        return view('admin.upload-types.index', compact(
            'tab',
            'uploadTypes',
            'checklistItems',
            'departments',
            'employeeFileSections',
            'docTypes',
            'positions',
            'itemSections',
            'documentSetCatalog',
            'positionGroupCatalog',
            'generalUploadTypes',
            'requirementOverview',
            'selectedRequirementPosition',
            'selectedRequirementUploadTypeIds',
        ));
    }

    public function create(): View
    {
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.upload-types.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:upload_types,name',
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $validated['requires_expiry'] = $request->boolean('requires_expiry');
        $validated['is_license_or_certification'] = $request->boolean('is_license_or_certification');
        $validated['department_ids'] = collect($request->input('department_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        UploadType::query()->create($validated);

        return redirect()
            ->route('admin.upload-types.index')
            ->with('success', config('documents.messages.type_created'));
    }

    public function show(UploadType $uploadType): View
    {
        $uploadType->loadMissing('checklistItem');
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $departmentIds = collect($uploadType->department_ids ?? [])->map(fn ($id) => (int) $id);
        $departmentNames = $departments->whereIn('id', $departmentIds)->pluck('name')->values();
        $uploadCount = $uploadType->uploads()->count();
        $positionCount = $uploadType->positions()->wherePivot('is_required', true)->count();

        return view('admin.upload-types.show', compact(
            'uploadType',
            'departmentNames',
            'uploadCount',
            'positionCount',
        ));
    }

    public function edit(UploadType $uploadType): View
    {
        if ($uploadType->isEmployeeFileChecklistType()) {
            abort(403, 'Employee file document types are managed under Documents Management → Employee file items.');
        }

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.upload-types.edit', compact('uploadType', 'departments'));
    }

    public function update(Request $request, UploadType $uploadType): RedirectResponse
    {
        if ($uploadType->isEmployeeFileChecklistType()) {
            return redirect()
                ->route('admin.upload-types.index')
                ->with('error', 'Employee file document types are managed under Documents Management → Employee file items. Edit the employee file item to change the name or expiry rules.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:upload_types,name,' . $uploadType->id,
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $validated['requires_expiry'] = $request->boolean('requires_expiry');
        $validated['is_license_or_certification'] = $request->boolean('is_license_or_certification');
        $validated['department_ids'] = collect($request->input('department_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $uploadType->update($validated);

        return redirect()
            ->route('admin.upload-types.index')
            ->with('success', config('documents.messages.type_updated'));
    }

    public function destroy(UploadType $uploadType): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole([
            'admin',
            'super-admin',
            'facility-admin',
            'facility-dsd',
            'rdhr',
            'don',
        ])) {
            abort(403, 'You do not have permission to remove document types.');
        }

        if ($uploadType->isEmployeeFileChecklistType()) {
            return redirect()
                ->route('admin.upload-types.index')
                ->with('error', 'Employee file document types cannot be deleted here. Remove or update the employee file item instead.');
        }

        $permanent = $user->hasRole(['admin', 'super-admin']);

        try {
            if ($permanent) {
                DB::transaction(function () use ($uploadType): void {
                    $uploadType->uploads()->update(['upload_type_id' => null]);
                    $uploadType->forceDelete();
                });
            } else {
                $uploadType->delete();
            }
        } catch (QueryException) {
            return redirect()
                ->route('admin.upload-types.index')
                ->with('error', 'Cannot delete this document type because it is in use.');
        }

        return redirect()
            ->route('admin.upload-types.index')
            ->with(
                'success',
                $permanent
                    ? 'Document type permanently deleted.'
                    : 'Document type archived. Existing document history is preserved.'
            );
    }

    public function syncSeeder(DocumentsManagementSeederExporter $exporter): RedirectResponse
    {
        if (! $this->canManageSeeder()) {
            return redirect()->route('admin.upload-types.index')
                ->with('error', 'You do not have permission to update the documents management seeder.');
        }

        try {
            $result = $exporter->writeSeederFile();

            return redirect()->route('admin.upload-types.index')
                ->with(
                    'success',
                    'Seeder updated with ' . $result['count'] . ' general document type(s). '
                    . 'File: database/seeders/data/documents_management_general_types.php. '
                    . 'Commit it so migrate:fresh --seed restores them. '
                    . 'Employee file items stay synced from the Employee file items tab.'
                );
        } catch (\Throwable $e) {
            return redirect()->route('admin.upload-types.index')
                ->with('error', 'Failed to update seeder: ' . $e->getMessage());
        }
    }

    public function runSeeder(DocumentsManagementSeedService $seedService): RedirectResponse
    {
        if (! $this->canManageSeeder()) {
            return redirect()->route('admin.upload-types.index')
                ->with('error', 'You do not have permission to apply the documents management seeder.');
        }

        try {
            $result = $seedService->seedAll();
            $general = $result['general'];
            $checklistSynced = (int) ($result['checklist_synced'] ?? 0);

            return redirect()->route('admin.upload-types.index')
                ->with(
                    'success',
                    'Seeder applied: '
                    . $general['created'] . ' general type(s) created, '
                    . $general['updated'] . ' updated from '
                    . $general['total'] . ' definition(s); '
                    . $checklistSynced . ' checklist-linked type(s) synced.'
                );
        } catch (\Throwable $e) {
            return redirect()->route('admin.upload-types.index')
                ->with('error', 'Failed to apply seeder: ' . $e->getMessage());
        }
    }

    protected function canManageSeeder(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'super-admin']);
    }
}
