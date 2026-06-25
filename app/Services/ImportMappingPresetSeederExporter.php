<?php

namespace App\Services;

use App\Models\ImportMappingPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ImportMappingPresetSeederExporter
{
    public function seederPath(): string
    {
        return database_path('seeders/ImportMappingPresetsTableSeeder.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $presets = $this->collectPresetData();
        $content = $this->buildSeederContents($presets);
        $path = $this->seederPath();

        if (!is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders directory not found.');
        }

        $written = file_put_contents($path, $content);

        if ($written === false) {
            throw new \RuntimeException('Could not write seeder file.');
        }

        return ['count' => count($presets), 'path' => $path];
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
        if (!$this->shouldSyncFromRequest($request)) {
            return ['synced' => false];
        }

        try {
            $result = $this->writeSeederFile();

            return [
                'synced' => true,
                'count' => $result['count'],
                'path' => 'database/seeders/ImportMappingPresetsTableSeeder.php',
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
        if (!empty($sync['synced'])) {
            return ' Seeder updated with ' . ($sync['count'] ?? 0)
                . ' preset(s). Commit database/seeders/ImportMappingPresetsTableSeeder.php so migrate:fresh --seed restores them.';
        }

        if (!empty($sync['error'])) {
            return ' Seeder update failed: ' . $sync['error'];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function seederSyncResponsePayload(array $sync): array
    {
        return ['seeder' => $sync];
    }

    /**
     * @return array<int, array{name: string, facility_id: int, owner_email: string, mappings: array}>
     */
    public function collectPresetData(): array
    {
        return ImportMappingPreset::query()
            ->with('user:id,email')
            ->orderBy('facility_id')
            ->orderBy('name')
            ->get()
            ->map(function (ImportMappingPreset $preset) {
                return [
                    'name' => $preset->name,
                    'facility_id' => (int) $preset->facility_id,
                    'owner_email' => $preset->user?->email
                        ?? config('member-portal.super_admin_email', 'super-admin@biopacific.com'),
                    'mappings' => array_values($preset->mappings ?? []),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{name: string, facility_id: int, owner_email: string, mappings: array}>  $presets
     */
    protected function buildSeederContents(array $presets): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $json = json_encode(
            $presets,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException('Failed to encode presets as JSON.');
        }

        return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\ImportMappingPreset;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Auto-generated from Import Preset Management → Update seeder.
 * Last exported: {$exportedAt}
 *
 * Do not edit preset data by hand; use the admin UI and re-export.
 */
class ImportMappingPresetsTableSeeder extends Seeder
{
    public function run(): void
    {
        \$presets = json_decode(<<<'IMPORT_MAPPING_PRESETS_JSON'
{$json}
IMPORT_MAPPING_PRESETS_JSON, true) ?? [];

        foreach (\$presets as \$preset) {
            \$userId = User::where('email', \$preset['owner_email'])->value('id');

            if (! \$userId) {
                \$fallbackEmail = config('member-portal.super_admin_email', 'super-admin@biopacific.com');
                \$userId = User::where('email', \$fallbackEmail)->value('id');
            }

            if (! \$userId) {
                \$this->command?->warn(
                    'ImportMappingPresetsTableSeeder: skipped preset "' . \$preset['name'] . '" — user not found: ' . \$preset['owner_email']
                );
                continue;
            }

            ImportMappingPreset::updateOrCreate(
                [
                    'name' => \$preset['name'],
                    'facility_id' => \$preset['facility_id'],
                ],
                [
                    'user_id' => \$userId,
                    'mappings' => \$preset['mappings'],
                ]
            );
        }
    }
}

PHP;
    }
}
