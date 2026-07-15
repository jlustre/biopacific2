<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTrainingItem;
use App\Models\Position;
use App\Services\EmployeeTrainingItemsSeederExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeTrainingItemController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $frequency = $request->input('frequency');

        $items = EmployeeTrainingItem::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(in_array($frequency, EmployeeTrainingItem::frequencyKeys(), true), function ($query) use ($frequency) {
                $query->where('frequency', $frequency);
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $positions = Position::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']);

        return view('admin.training-items.index', compact('items', 'positions', 'search', 'frequency'));
    }

    public function create()
    {
        $trainingItem = new EmployeeTrainingItem([
            'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
            'position_ids' => ['global'],
            'is_active' => true,
            'order' => (int) EmployeeTrainingItem::query()->max('order') + 1,
        ]);
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.training-items.create', compact('trainingItem', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);
        EmployeeTrainingItem::create($validated);

        return redirect()->route('admin.training-items.index')
            ->with('success', 'Training created successfully.');
    }

    public function edit(EmployeeTrainingItem $training_item)
    {
        $trainingItem = $training_item;
        $positions = Position::query()->where('is_active', true)->orderBy('title')->get();

        return view('admin.training-items.edit', compact('trainingItem', 'positions'));
    }

    public function update(Request $request, EmployeeTrainingItem $training_item)
    {
        $validated = $this->validateItem($request);
        $training_item->update($validated);

        return redirect()->route('admin.training-items.index')
            ->with('success', 'Training updated successfully.');
    }

    public function destroy(EmployeeTrainingItem $training_item)
    {
        $training_item->delete();

        return redirect()->route('admin.training-items.index')
            ->with('success', 'Training deleted successfully.');
    }

    public function bulkUpdatePositions(Request $request)
    {
        $validated = $request->validate([
            'training_item_ids' => ['required', 'array', 'min:1'],
            'training_item_ids.*' => ['integer', 'exists:employee_training_items,id'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
            'remove_from_everyone' => ['nullable', 'boolean'],
        ]);

        $applyToEveryone = $request->boolean('apply_to_everyone');
        $removeFromEveryone = $request->boolean('remove_from_everyone');
        $positionIds = array_values(array_unique(array_map('intval', $validated['position_ids'] ?? [])));

        if ($applyToEveryone && $removeFromEveryone) {
            return redirect()->route('admin.training-items.index')
                ->with('error', 'Choose either apply to everybody or remove from everybody, not both.');
        }

        if (! $applyToEveryone && ! $removeFromEveryone && $positionIds === []) {
            return redirect()->route('admin.training-items.index')
                ->with('error', 'Select at least one position or choose apply/remove for everybody.');
        }

        $positionIdsValue = match (true) {
            $removeFromEveryone => [],
            $applyToEveryone => ['global'],
            default => $positionIds,
        };

        EmployeeTrainingItem::query()
            ->whereIn('id', $validated['training_item_ids'])
            ->get()
            ->each(function (EmployeeTrainingItem $item) use ($positionIdsValue): void {
                $item->position_ids = $positionIdsValue;
                $item->save();
            });

        return redirect()->route('admin.training-items.index')
            ->with('success', 'Training position assignments updated.');
    }

    public function syncSeeder(EmployeeTrainingItemsSeederExporter $exporter): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole(['admin', 'super-admin']), 403);

        try {
            $result = $exporter->writeSeederFile();

            return redirect()->route('admin.training-items.index')
                ->with(
                    'success',
                    'Seeder updated with '.$result['count'].' training module(s). '
                    .'File: database/seeders/data/employee_training_items.php. '
                    .'Commit this file so migrate:fresh --seed restores the catalog and position assignments.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.training-items.index')
                ->with('error', 'Could not update the training seeder: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateItem(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_url' => ['nullable', 'string', 'max:500'],
            'provider_label' => ['nullable', 'string', 'max:120'],
            'frequency' => ['required', Rule::in(EmployeeTrainingItem::frequencyKeys())],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'apply_to_everyone' => ['nullable', 'boolean'],
        ]);

        $applyToEveryone = $request->boolean('apply_to_everyone')
            || empty($validated['position_ids'] ?? []);

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content_url' => filled($validated['content_url'] ?? null) ? trim($validated['content_url']) : null,
            'provider_label' => filled($validated['provider_label'] ?? null) ? trim($validated['provider_label']) : null,
            'frequency' => $validated['frequency'],
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'position_ids' => $applyToEveryone
                ? ['global']
                : array_values(array_unique(array_map('intval', $validated['position_ids'] ?? []))),
        ];
    }
}
