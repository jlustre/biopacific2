<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Carbon;

class DepartmentSeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/departments.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $departments = $this->collectDepartmentData();
        $path = $this->dataFilePath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders data directory not found.');
        }

        if (file_put_contents($path, $this->buildDataFileContents($departments)) === false) {
            throw new \RuntimeException('Could not write the departments seeder data file.');
        }

        return [
            'count' => count($departments),
            'path' => $path,
        ];
    }

    /**
     * @return list<array{name: string, type: string, description: ?string}>
     */
    public function collectDepartmentData(): array
    {
        return Department::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Department $department): array => [
                'name' => (string) $department->name,
                'type' => (string) $department->type,
                'description' => filled($department->description)
                    ? (string) $department->description
                    : null,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{name: string, type: string, description: ?string}>  $departments
     */
    protected function buildDataFileContents(array $departments): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $export = var_export($departments, true);

        return <<<PHP
<?php

/**
 * Facility and corporate departments.
 *
 * Auto-generated from Departments Management → Update seeder on {$exportedAt}.
 *
 * @return list<array{name: string, type: string, description: ?string}>
 */
return {$export};

PHP;
    }
}
