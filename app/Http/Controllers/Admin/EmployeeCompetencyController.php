<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCompetencyItem;
use App\Models\Position;
use App\Services\EmployeeCompetencyItemsSeederExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeCompetencyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $sectionsQuery = EmployeeCompetencyItem::query()
            ->selectRaw('section, COUNT(*) as items_count, MIN(`order`) as sort_order, MIN(id) as sample_id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('section', 'like', "%{$search}%")
                        ->orWhere('item', 'like', "%{$search}%");
                });
            })
            ->groupBy('section')
            ->orderBy('section');

        $sectionRows = $sectionsQuery->get();

        $sampleIds = $sectionRows->pluck('sample_id')->filter()->all();
        $samples = EmployeeCompetencyItem::query()
            ->whereIn('id', $sampleIds)
            ->get(['id', 'section', 'position_ids'])
            ->keyBy('section');

        $competencies = $sectionRows->map(function ($row) use ($samples) {
            $sample = $samples->get($row->section);

            return (object) [
                'section' => $row->section,
                'section_key' => EmployeeCompetencyItem::encodeSectionKey($row->section),
                'items_count' => (int) $row->items_count,
                'position_ids' => $sample?->position_ids ?? ['global'],
                'applies_to_everyone' => $sample?->appliesToEveryone() ?? true,
            ];
        });

        $positions = Position::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']);

        return view('admin.competencies.index', compact('competencies', 'positions', 'search'));
    }

    public function create(): View
    {
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.competencies.create', compact('positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'max:255'],
            'items_text' => ['nullable', 'string', 'max:20000'],
            'order' => ['nullable', 'integer', 'min:0'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
        ]);

        $section = trim($validated['section']);
        if (EmployeeCompetencyItem::query()->where('section', $section)->exists()) {
            return back()->withInput()->with('error', 'A competency with that name already exists.');
        }

        $positionIds = EmployeeCompetencyItem::normalizePositionIds(
            $request->boolean('apply_to_everyone') || empty($validated['position_ids'] ?? []),
            $validated['position_ids'] ?? []
        );

        $lines = preg_split("/\r\n|\n|\r/", (string) ($validated['items_text'] ?? '')) ?: [];
        $itemLines = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));

        if ($itemLines === []) {
            $itemLines = ['New competency item'];
        }

        $baseOrder = (int) ($validated['order'] ?? 0);

        DB::transaction(function () use ($section, $itemLines, $positionIds, $baseOrder) {
            foreach ($itemLines as $index => $line) {
                EmployeeCompetencyItem::create([
                    'section' => $section,
                    'item' => $line,
                    'position_ids' => $positionIds,
                    'order' => $baseOrder + $index,
                ]);
            }
        });

        return redirect()
            ->route('admin.competencies.show', EmployeeCompetencyItem::encodeSectionKey($section))
            ->with('success', 'Competency created successfully.');
    }

    public function show(string $sectionKey): View
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        $items = EmployeeCompetencyItem::query()
            ->forSection($section)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        abort_if($items->isEmpty(), 404, 'Competency not found.');

        $sample = $items->first();
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.competencies.show', [
            'section' => $section,
            'sectionKey' => $sectionKey,
            'items' => $items,
            'sample' => $sample,
            'positions' => $positions,
        ]);
    }

    public function update(Request $request, string $sectionKey): RedirectResponse
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        abort_unless(EmployeeCompetencyItem::query()->forSection($section)->exists(), 404);

        $validated = $request->validate([
            'section' => ['required', 'string', 'max:255'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
        ]);

        $newSection = trim($validated['section']);
        if ($newSection !== $section
            && EmployeeCompetencyItem::query()->where('section', $newSection)->exists()) {
            return back()->withInput()->with('error', 'Another competency already uses that name.');
        }

        $positionIds = EmployeeCompetencyItem::normalizePositionIds(
            $request->boolean('apply_to_everyone') || empty($validated['position_ids'] ?? []),
            $validated['position_ids'] ?? []
        );

        EmployeeCompetencyItem::query()
            ->forSection($section)
            ->update([
                'section' => $newSection,
                'position_ids' => json_encode(array_values($positionIds)),
            ]);

        return redirect()
            ->route('admin.competencies.show', EmployeeCompetencyItem::encodeSectionKey($newSection))
            ->with('success', 'Competency updated successfully.');
    }

    public function destroy(string $sectionKey): RedirectResponse
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        $deleted = EmployeeCompetencyItem::query()->forSection($section)->delete();

        return redirect()
            ->route('admin.competencies.index')
            ->with('success', $deleted > 0
                ? "Competency deleted ({$deleted} item(s) removed)."
                : 'Competency not found.');
    }

    public function storeItem(Request $request, string $sectionKey): RedirectResponse
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        $sample = EmployeeCompetencyItem::query()->forSection($section)->first();
        abort_unless($sample, 404);

        $validated = $request->validate([
            'item' => ['required', 'string', 'max:5000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        EmployeeCompetencyItem::create([
            'section' => $section,
            'item' => trim($validated['item']),
            'order' => (int) ($validated['order'] ?? ((int) EmployeeCompetencyItem::query()->forSection($section)->max('order') + 1)),
            'position_ids' => $sample->position_ids ?? ['global'],
        ]);

        return redirect()
            ->route('admin.competencies.show', $sectionKey)
            ->with('success', 'Competency item added.');
    }

    public function editItem(string $sectionKey, EmployeeCompetencyItem $item): View
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        abort_unless($item->section === $section, 404);

        return view('admin.competencies.edit-item', [
            'section' => $section,
            'sectionKey' => $sectionKey,
            'item' => $item,
        ]);
    }

    public function updateItem(Request $request, string $sectionKey, EmployeeCompetencyItem $item): RedirectResponse
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        abort_unless($item->section === $section, 404);

        $validated = $request->validate([
            'item' => ['required', 'string', 'max:5000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update([
            'item' => trim($validated['item']),
            'order' => (int) ($validated['order'] ?? 0),
        ]);

        return redirect()
            ->route('admin.competencies.show', $sectionKey)
            ->with('success', 'Competency item updated.');
    }

    public function destroyItem(string $sectionKey, EmployeeCompetencyItem $item): RedirectResponse
    {
        $section = EmployeeCompetencyItem::decodeSectionKey($sectionKey);
        abort_unless($item->section === $section, 404);

        $item->delete();

        $remaining = EmployeeCompetencyItem::query()->forSection($section)->count();
        if ($remaining === 0) {
            return redirect()
                ->route('admin.competencies.index')
                ->with('success', 'Last item removed; competency deleted.');
        }

        return redirect()
            ->route('admin.competencies.show', $sectionKey)
            ->with('success', 'Competency item deleted.');
    }

    public function syncSeeder(EmployeeCompetencyItemsSeederExporter $exporter): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole(['admin', 'super-admin']), 403);

        try {
            $result = $exporter->writeSeederFile();

            return redirect()->route('admin.competencies.index')
                ->with(
                    'success',
                    'Seeder updated with '.$result['count'].' competency item(s). '
                    .'File: database/seeders/data/employee_competency_items.php. '
                    .'Commit this file so migrate:fresh --seed restores the catalog and position assignments.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.competencies.index')
                ->with('error', 'Could not update the competency seeder: '.$e->getMessage());
        }
    }

    public function bulkUpdatePositions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_keys' => ['required', 'array', 'min:1'],
            'section_keys.*' => ['string'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
            'remove_from_everyone' => ['nullable', 'boolean'],
        ]);

        $applyToEveryone = $request->boolean('apply_to_everyone');
        $removeFromEveryone = $request->boolean('remove_from_everyone');
        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        if ($applyToEveryone && $removeFromEveryone) {
            return redirect()->route('admin.competencies.index')
                ->with('error', 'Choose either apply to everybody or remove from everybody, not both.');
        }

        if (! $applyToEveryone && ! $removeFromEveryone && $positionIds === []) {
            return redirect()->route('admin.competencies.index')
                ->with('error', 'Select at least one position or choose apply/remove for everybody.');
        }

        $positionIdsValue = match (true) {
            $removeFromEveryone => [],
            $applyToEveryone => ['global'],
            default => $positionIds,
        };

        $sections = collect($validated['section_keys'])
            ->map(fn ($key) => EmployeeCompetencyItem::decodeSectionKey($key))
            ->unique()
            ->values();

        EmployeeCompetencyItem::query()
            ->whereIn('section', $sections->all())
            ->update(['position_ids' => json_encode(array_values($positionIdsValue))]);

        return redirect()->route('admin.competencies.index')
            ->with('success', 'Competency position assignments updated.');
    }
}
