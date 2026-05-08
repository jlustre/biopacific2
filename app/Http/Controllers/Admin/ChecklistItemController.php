<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\DocType;
use App\Models\Position;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    public function index(Request $request)
    {
        $query = ChecklistItem::with('docType');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        if ($request->filled('doc_type_id')) {
            $query->where('doc_type_id', $request->doc_type_id);
        }

        $checklistItems = $query->orderBy('section')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20)
            ->appends($request->query());

        $docTypes = DocType::query()->orderBy('name')->get();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();
        $positionsById = $positions->keyBy('position_id');
        $sections = ChecklistItem::query()->select('section')->distinct()->whereNotNull('section')->orderBy('section')->pluck('section');

        return view('admin.checklist-items.index', compact('checklistItems', 'docTypes', 'positions', 'positionsById', 'sections'));
    }

    public function bulkUpdatePositions(Request $request)
    {
        $validated = $request->validate([
            'checklist_item_ids' => ['required', 'array', 'min:1'],
            'checklist_item_ids.*' => ['integer', 'exists:checklist_items,id'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
        ]);

        $checklistItemIds = array_values(array_unique(array_map('intval', $validated['checklist_item_ids'])));
        $applyToEveryone = $request->boolean('apply_to_everyone');
        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        if (!$applyToEveryone && count($positionIds) === 0) {
            return back()->with('error', 'Select at least one position or choose "Apply to everybody".');
        }

        ChecklistItem::query()
            ->whereIn('id', $checklistItemIds)
            ->update([
                'position_ids' => $applyToEveryone ? null : $positionIds,
            ]);

        return back()->with('success', 'Checklist item positions updated successfully.');
    }

    public function create()
    {
        $docTypes = DocType::query()->orderBy('name')->get();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();
        $checklistItem = new ChecklistItem([
            'isExpiring' => false,
            'position_ids' => null,
        ]);

        return view('admin.checklist-items.create', compact('checklistItem', 'docTypes', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateChecklistItem($request);

        ChecklistItem::create($validated);

        return redirect()->route('admin.checklist-items.index')->with('success', 'Checklist item created successfully.');
    }

    public function edit(ChecklistItem $checklistItem)
    {
        $docTypes = DocType::query()->orderBy('name')->get();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.checklist-items.edit', compact('checklistItem', 'docTypes', 'positions'));
    }

    public function update(Request $request, ChecklistItem $checklistItem)
    {
        $validated = $this->validateChecklistItem($request);

        $checklistItem->update($validated);

        return redirect()->route('admin.checklist-items.index')->with('success', 'Checklist item updated successfully.');
    }

    public function destroy(ChecklistItem $checklistItem)
    {
        $checklistItem->delete();

        return redirect()->route('admin.checklist-items.index')->with('success', 'Checklist item deleted successfully.');
    }

    private function validateChecklistItem(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:50'],
            'doc_type_id' => ['required', 'integer', 'exists:doc_types,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'isExpiring' => ['nullable', 'boolean'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ]);

        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        $validated['position_ids'] = count($positionIds) ? $positionIds : null;
        $validated['isExpiring'] = $request->boolean('isExpiring');

        if (empty($validated['order'])) {
            $validated['order'] = (ChecklistItem::max('order') ?? 0) + 1;
        }

        return $validated;
    }
}