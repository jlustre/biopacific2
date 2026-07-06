<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Carbon;

class ReportSeederExporter
{
    public function seederPath(): string
    {
        return database_path('seeders/ReportSeeder.php');
    }

    /**
     * @return array{count: int, path: string}
     */
    public function writeSeederFile(): array
    {
        $reports = $this->collectReportData();
        $content = $this->buildSeederContents($reports);
        $path = $this->seederPath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders directory not found.');
        }

        $written = file_put_contents($path, $content);

        if ($written === false) {
            throw new \RuntimeException('Could not write report seeder file.');
        }

        return ['count' => count($reports), 'path' => $path];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectReportData(): array
    {
        return Report::query()
            ->with('category:id,name')
            ->where('name', '!=', 'Generate Reports Seeder')
            ->orderBy('name')
            ->get()
            ->map(fn (Report $report) => [
                'category_name' => $report->category?->name,
                'name' => $report->name,
                'description' => $report->description,
                'sql_template' => $report->sql_template,
                'parameters' => $report->parameters ?? [],
                'is_active' => (bool) $report->is_active,
                'is_public' => (bool) ($report->is_public ?? false),
                'visibility' => $report->visibility ?? 'admin',
                'visible_roles' => $report->visible_roles ?? [],
                'visible_facilities' => $report->visible_facilities ?? [],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $reports
     */
    protected function buildSeederContents(array $reports): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $json = json_encode(
            $reports,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException('Failed to encode reports as JSON.');
        }

        return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

/**
 * Auto-generated from Admin Reports -> Add/update seeder.
 * Last exported: {$exportedAt}
 *
 * Do not edit report data by hand; use Admin Reports and re-export.
 */
class ReportSeeder extends Seeder
{
    public function run(): void
    {
        \$reports = json_decode(<<<'REPORTS_JSON'
{$json}
REPORTS_JSON, true) ?? [];

        foreach (\$reports as \$report) {
            \$categoryId = null;

            if (! empty(\$report['category_name'])) {
                \$categoryId = ReportCategory::query()
                    ->where('name', \$report['category_name'])
                    ->value('id');
            }

            Report::query()->updateOrCreate(
                ['name' => \$report['name']],
                [
                    'category_id' => \$categoryId,
                    'description' => \$report['description'] ?? null,
                    'sql_template' => \$report['sql_template'],
                    'parameters' => \$report['parameters'] ?? [],
                    'is_active' => (bool) (\$report['is_active'] ?? true),
                    'is_public' => (bool) (\$report['is_public'] ?? false),
                    'visibility' => \$report['visibility'] ?? 'admin',
                    'visible_roles' => \$report['visible_roles'] ?? [],
                    'visible_facilities' => \$report['visible_facilities'] ?? [],
                ]
            );
        }
    }
}

PHP;
    }
}
