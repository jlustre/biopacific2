<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\DocType;
use App\Models\Position;
use App\Services\ChecklistItemsSeederExporter;
use App\Services\ChecklistUploadTypeSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.upload-types.index', array_merge(
            $request->only(['search', 'section', 'doc_type_id', 'page']),
            ['tab' => 'items']
        ));
    }

    public function bulkUpdatePositions(Request $request)
    {
        $validated = $request->validate([
            'checklist_item_ids' => ['required', 'array', 'min:1'],
            'checklist_item_ids.*' => ['integer', 'exists:checklist_items,id'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
            'remove_from_everyone' => ['nullable', 'boolean'],
        ]);

        $checklistItemIds = array_values(array_unique(array_map('intval', $validated['checklist_item_ids'])));
        $applyToEveryone = $request->boolean('apply_to_everyone');
        $removeFromEveryone = $request->boolean('remove_from_everyone');
        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        if ($applyToEveryone && $removeFromEveryone) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
                ->with('error', 'Choose either apply to everybody or remove from everybody, not both.');
        }

        if (! $applyToEveryone && ! $removeFromEveryone && count($positionIds) === 0) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
                ->with('error', 'Select at least one position or choose apply/remove for everybody.');
        }

        $positionIdsValue = match (true) {
            $removeFromEveryone => [],
            $applyToEveryone => null,
            default => $positionIds,
        };

        $sync = app(ChecklistUploadTypeSyncService::class);
        $requirements = app(\App\Services\EmployeeDocumentRequirementsService::class);

        ChecklistItem::query()
            ->whereIn('id', $checklistItemIds)
            ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
            ->with('uploadType')
            ->get()
            ->each(function (ChecklistItem $item) use ($positionIdsValue, $sync, $requirements): void {
                $item->position_ids = $positionIdsValue;
                $item->save();

                $uploadType = $item->uploadType ?: $sync->syncChecklistItem($item);
                if (! $uploadType) {
                    return;
                }

                if ($positionIdsValue === null) {
                    $requirements->setAppliesToAllPositions($uploadType, true);
                } elseif ($positionIdsValue === []) {
                    $requirements->setAppliesToAllPositions($uploadType, false);
                    $uploadType->positions()->detach();
                    $sync->syncUploadType($uploadType->fresh());
                } else {
                    $requirements->setAppliesToAllPositions($uploadType, false);
                    $uploadType->positions()->sync(
                        collect($positionIdsValue)->mapWithKeys(fn ($id) => [(int) $id => ['is_required' => true]])->all()
                    );
                    $sync->syncUploadType($uploadType->fresh());
                }
            });

        $successMessage = match (true) {
            $removeFromEveryone => 'Selected employee file items no longer apply to any position.',
            $applyToEveryone => 'Selected employee file items now apply to all positions.',
            default => 'Employee file item positions updated successfully.',
        };

        return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
            ->with('success', $successMessage);
    }

    public function create()
    {
        $docTypes = DocType::query()->orderBy('name')->get();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();
        $checklistItem = new ChecklistItem([
            'isExpiring' => false,
            'is_required' => true,
            'position_ids' => null,
        ]);

        return view('admin.checklist-items.create', compact('checklistItem', 'docTypes', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateChecklistItem($request);

        $checklistItem = ChecklistItem::create($validated);
        app(ChecklistUploadTypeSyncService::class)->syncChecklistItem($checklistItem);

        return redirect()->route('admin.upload-types.index', ['tab' => 'items'])->with('success', 'Employee file item created successfully.');
    }

    public function edit(ChecklistItem $checklistItem)
    {
        $this->ensureEmployeeFileDocumentItem($checklistItem);

        $docTypes = DocType::query()->orderBy('name')->get();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.checklist-items.edit', compact('checklistItem', 'docTypes', 'positions'));
    }

    public function update(Request $request, ChecklistItem $checklistItem)
    {
        $this->ensureEmployeeFileDocumentItem($checklistItem);

        $validated = $this->validateChecklistItem($request);

        $checklistItem->update($validated);
        app(ChecklistUploadTypeSyncService::class)->syncChecklistItem($checklistItem->fresh());

        return redirect()->route('admin.upload-types.index', ['tab' => 'items'])->with('success', 'Employee file item updated successfully.');
    }

    public function destroy(ChecklistItem $checklistItem)
    {
        $this->ensureEmployeeFileDocumentItem($checklistItem);

        $uploadType = $checklistItem->uploadType;
        if ($uploadType) {
            $uploadType->forceFill([
                'checklist_item_id' => null,
                'checklist_section' => null,
            ])->save();
        }

        $checklistItem->delete();

        return redirect()->route('admin.upload-types.index', ['tab' => 'items'])->with('success', 'Employee file item deleted successfully.');
    }

    protected function ensureEmployeeFileDocumentItem(ChecklistItem $checklistItem): void
    {
        if (! in_array((string) $checklistItem->section, ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS, true)) {
            abort(404, 'PART E orientation items are not documents and cannot be managed here.');
        }
    }

    public function syncSeeder(ChecklistItemsSeederExporter $exporter): RedirectResponse
    {
        if (! $this->canManageSeeder()) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
                ->with('error', 'You do not have permission to update the employee file items seeder.');
        }

        try {
            $result = $exporter->writeSeederFile();

            return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
                ->with(
                    'success',
                    'Seeder updated with ' . $result['count'] . ' employee file item(s). '
                    . 'File: database/seeders/data/checklist_items.php. '
                    . 'Commit it so migrate:fresh --seed restores them. '
                    . 'PART E orientation items are managed separately.'
                );
        } catch (\Throwable $e) {
            return redirect()->route('admin.upload-types.index', ['tab' => 'items'])
                ->with('error', 'Failed to update seeder: ' . $e->getMessage());
        }
    }

    protected function canManageSeeder(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'super-admin']);
    }

    private function validateChecklistItem(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'section' => ['required', 'string', 'in:' . implode(',', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)],
            'doc_type_id' => ['required', 'integer', 'exists:doc_types,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'isExpiring' => ['nullable', 'boolean'],
            'is_required' => ['required', 'boolean'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ]);

        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        $validated['position_ids'] = count($positionIds) ? $positionIds : null;
        $validated['isExpiring'] = $request->boolean('isExpiring');
        $validated['is_required'] = $request->boolean('is_required');

        if (empty($validated['order'])) {
            $validated['order'] = (ChecklistItem::max('order') ?? 0) + 1;
        }

        return $validated;
    }
}