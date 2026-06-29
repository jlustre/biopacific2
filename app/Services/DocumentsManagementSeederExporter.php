<?php

namespace App\Services;

use App\Models\UploadType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DocumentsManagementSeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/documents_management_general_types.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $types = $this->collectGeneralTypeData();
        $content = $this->buildDataFileContents($types);
        $path = $this->dataFilePath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders data directory not found.');
        }

        $written = file_put_contents($path, $content);

        if ($written === false) {
            throw new \RuntimeException('Could not write seeder data file.');
        }

        return ['count' => count($types), 'path' => $path];
    }

    public function shouldSyncFromRequest(Request $request): bool
    {
        return $request->boolean('update_seeder');
    }

    /**
     * @return array{synced: bool, count?: int, path?: string, error?: string}
     */
    public function syncFromRequest(Request $request): array
    {
        if (! $this->shouldSyncFromRequest($request)) {
            return ['synced' => false];
        }

        try {
            $result = $this->writeSeederFile();

            return [
                'synced' => true,
                'count' => $result['count'],
                'path' => 'database/seeders/data/documents_management_general_types.php',
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'synced' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function seederSyncMessage(array $sync): ?string
    {
        if (! empty($sync['synced'])) {
            return ' Seeder updated with ' . ($sync['count'] ?? 0)
                . ' general document type(s). Commit database/seeders/data/documents_management_general_types.php '
                . 'so migrate:fresh --seed restores them.';
        }

        if (! empty($sync['error'])) {
            return ' Seeder update failed: ' . $sync['error'];
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, description: string, requires_expiry: bool, is_license_or_certification: bool}>
     */
    public function collectGeneralTypeData(): array
    {
        return UploadType::query()
            ->generalDocumentTypes()
            ->orderBy('name')
            ->get()
            ->map(function (UploadType $type) {
                return [
                    'name' => (string) $type->name,
                    'description' => (string) ($type->description ?? ''),
                    'requires_expiry' => (bool) $type->requires_expiry,
                    'is_license_or_certification' => (bool) $type->is_license_or_certification,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{name: string, description: string, requires_expiry: bool, is_license_or_certification: bool}>  $types
     */
    protected function buildDataFileContents(array $types): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $export = var_export($types, true);

        return <<<PHP
<?php

/**
 * General document types for Documents Management (not tied to checklist items).
 *
 * Auto-generated from Documents Management → Update seeder on {$exportedAt}.
 *
 * @return list<array{
 *     name: string,
 *     description: string,
 *     requires_expiry: bool,
 *     is_license_or_certification: bool
 * }>
 */
return {$export};

PHP;
    }
}
