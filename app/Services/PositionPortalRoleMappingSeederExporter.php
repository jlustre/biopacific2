<?php

namespace App\Services;

use App\Models\PositionPortalRoleMapping;
use Illuminate\Support\Carbon;

class PositionPortalRoleMappingSeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/position_portal_role_mappings.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $mappings = $this->collectMappingData();
        $path = $this->dataFilePath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders data directory not found.');
        }

        if (file_put_contents($path, $this->buildDataFileContents($mappings)) === false) {
            throw new \RuntimeException('Could not write the position portal role mappings seeder data file.');
        }

        return [
            'count' => count($mappings),
            'path' => $path,
        ];
    }

    /**
     * @return list<array{position_title: string, role_name: string, is_active: bool}>
     */
    public function collectMappingData(): array
    {
        return PositionPortalRoleMapping::query()
            ->with('position:id,title')
            ->get()
            ->filter(fn (PositionPortalRoleMapping $mapping) => filled($mapping->position?->title))
            ->sortBy(fn (PositionPortalRoleMapping $mapping) => strtolower((string) $mapping->position->title))
            ->map(fn (PositionPortalRoleMapping $mapping): array => [
                'position_title' => (string) $mapping->position->title,
                'role_name' => (string) $mapping->role_name,
                'is_active' => (bool) $mapping->is_active,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{position_title: string, role_name: string, is_active: bool}>  $mappings
     */
    protected function buildDataFileContents(array $mappings): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $export = var_export($mappings, true);

        return <<<PHP
<?php

/**
 * Position-to-portal-role mappings.
 *
 * Position titles are used instead of IDs so mappings survive migrate:fresh.
 *
 * Auto-generated from Position Portal Roles → Update seeder on {$exportedAt}.
 *
 * @return list<array{position_title: string, role_name: string, is_active: bool}>
 */
return {$export};

PHP;
    }
}
