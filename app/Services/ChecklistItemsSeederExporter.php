<?php

namespace App\Services;

use App\Models\ChecklistItem;
use Illuminate\Support\Carbon;

class ChecklistItemsSeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/checklist_items.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $items = $this->collectItemData();
        $content = $this->buildDataFileContents($items);
        $path = $this->dataFilePath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders data directory not found.');
        }

        $written = file_put_contents($path, $content);

        if ($written === false) {
            throw new \RuntimeException('Could not write seeder data file.');
        }

        return ['count' => count($items), 'path' => $path];
    }

    /**
     * @return list<array{
     *     name: string,
     *     section: string,
     *     doc_type_id: int,
     *     isExpiring: bool,
     *     is_required: bool,
     *     is_license_or_certification: bool,
     *     position_ids: list<int>|null,
     *     order: int
     * }>
     */
    public function collectItemData(): array
    {
        return ChecklistItem::query()
            ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
            ->orderBy('section')
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function (ChecklistItem $item) {
                $positionIds = collect($item->position_ids ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'name' => (string) $item->name,
                    'section' => (string) $item->section,
                    'doc_type_id' => (int) $item->doc_type_id,
                    'isExpiring' => (bool) $item->isExpiring,
                    'is_required' => (bool) ($item->is_required ?? true),
                    'is_license_or_certification' => (bool) ($item->is_license_or_certification ?? false),
                    'position_ids' => $positionIds === [] ? null : $positionIds,
                    'order' => (int) ($item->order ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function buildDataFileContents(array $items): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $export = var_export($items, true);

        return <<<PHP
<?php

/**
 * Employee file document type items (PART A–D) for ChecklistItemsSeeder.
 *
 * Auto-generated from Documents Management → Employee file items → Update items seeder on {$exportedAt}.
 *
 * @return list<array{
 *     name: string,
 *     section: string,
 *     doc_type_id: int,
 *     isExpiring: bool,
 *     is_license_or_certification: bool,
 *     position_ids: list<int>|null,
 *     order: int
 * }>
 */
return {$export};

PHP;
    }
}
