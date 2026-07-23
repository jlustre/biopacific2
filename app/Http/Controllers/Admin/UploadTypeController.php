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
use Illuminate\Validation\Rule;
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
                ->catalogPositionAssignable()
                ->when(
                    $departmentFilter,
                    fn ($query) => $query->where(function ($scope) use ($departmentFilter) {
                        $scope->whereNull('department_ids')
                            ->orWhereJsonLength('department_ids', 0)
                            ->orWhereJsonContains('department_ids', $departmentFilter);
                    })
                )
                ->orderedForDisplay()
                ->get([
                    'id',
                    'name',
                    'requires_expiry',
                    'is_license_or_certification',
                    'department_ids',
                    'checklist_section',
                    'applies_to_all_positions',
                ]);
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
            // Documents Management only manages PART A–D employee-file items.
            // PART E orientation rows are not documents and stay out of this catalog.
            $itemsQuery = ChecklistItem::query()
                ->with('docType')
                ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS);

            if ($request->filled('search')) {
                $itemsQuery->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('section')) {
                $section = (string) $request->section;
                if (in_array($section, ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS, true)) {
                    $itemsQuery->where('section', $section);
                }
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
            $itemSections = collect(ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS);
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
        $docTypes = DocType::query()->orderBy('name')->get();
        $employeeFileSections = ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS;

        return view('admin.upload-types.create', compact('departments', 'docTypes', 'employeeFileSections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUploadType($request);
        $uploadType = UploadType::query()->create($validated);

        if ($uploadType->applies_to_all_positions) {
            $uploadType->positions()->detach();
        }

        return redirect()
            ->route('admin.upload-types.index', ['tab' => 'types'])
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
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $docTypes = DocType::query()->orderBy('name')->get();
        $employeeFileSections = ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS;

        return view('admin.upload-types.edit', compact('uploadType', 'departments', 'docTypes', 'employeeFileSections'));
    }

    public function update(Request $request, UploadType $uploadType): RedirectResponse
    {
        $validated = $this->validateUploadType($request, $uploadType);
        $uploadType->update($validated);

        if ($uploadType->applies_to_all_positions) {
            $uploadType->positions()->detach();
        }

        if (! $uploadType->checklist_section) {
            UploadType::withoutEvents(function () use ($uploadType): void {
                $uploadType->forceFill(['checklist_item_id' => null])->save();
            });
        }

        return redirect()
            ->route('admin.upload-types.index', ['tab' => 'types'])
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

        $permanent = $user->hasRole(['admin', 'super-admin']);

        try {
            if ($permanent) {
                DB::transaction(function () use ($uploadType): void {
                    $checklistItemId = $uploadType->checklist_item_id;
                    $uploadType->uploads()->update(['upload_type_id' => null, 'checklist_item_id' => null]);
                    $uploadType->positions()->detach();
                    $uploadType->forceDelete();
                    if ($checklistItemId) {
                        \App\Models\ChecklistItem::query()->whereKey($checklistItemId)->delete();
                    }
                });
            } else {
                $uploadType->delete();
            }
        } catch (QueryException) {
            return redirect()
                ->route('admin.upload-types.index', ['tab' => 'types'])
                ->with('error', 'Cannot delete this document type because it is in use.');
        }

        return redirect()
            ->route('admin.upload-types.index', ['tab' => 'types'])
            ->with(
                'success',
                $permanent
                    ? 'Document type permanently deleted.'
                    : 'Document type archived. Existing document history is preserved.'
            );
    }

    protected function validateUploadType(Request $request, ?UploadType $uploadType = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('upload_types', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($uploadType?->id),
            ],
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'checklist_section' => 'nullable|string|in:' . implode(',', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS),
            'doc_type_id' => 'nullable|integer|exists:doc_types,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['requires_expiry'] = $request->boolean('requires_expiry');
        $validated['is_license_or_certification'] = $request->boolean('is_license_or_certification');
        $validated['applies_to_all_positions'] = $request->boolean('applies_to_all_positions');
        $validated['checklist_section'] = $validated['checklist_section'] ?? null;
        $validated['doc_type_id'] = $validated['doc_type_id'] ?? null;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['department_ids'] = collect($request->input('department_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $validated;
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
