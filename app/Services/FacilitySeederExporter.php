<?php

namespace App\Services;

use App\Helpers\FacilityDataHelper;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilitySeederExporter
{
    public function dataPath(): string
    {
        return database_path('seeders/data/facilities.json');
    }

    public function seederPath(): string
    {
        return database_path('seeders/FacilitySeeder.php');
    }

    public function shouldSyncFromRequest(Request $request): bool
    {
        return $request->boolean('update_facility_seeder');
    }

    /**
     * @return array{synced: bool, facility?: string, total?: int, path?: string, error?: string}
     */
    public function syncFromRequest(Request $request, Facility $facility): array
    {
        if (! $this->shouldSyncFromRequest($request)) {
            return ['synced' => false];
        }

        return $this->syncFacility($facility);
    }

    /**
     * @return array{synced: bool, facility: string, total: int, path: string}
     */
    public function syncFacility(Facility $facility): array
    {
        $facility->refresh();
        $facility->load(['services:id', 'values', 'webContents']);

        $items = $this->readFacilitiesData();
        $payload = $this->facilityToSeedArray($facility);
        $matchKey = $this->matchKeyFor($payload);

        $updated = false;
        foreach ($items as $index => $item) {
            if ($this->matchKeyFor($item) === $matchKey) {
                $items[$index] = $payload;
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            $items[] = $payload;
        }

        usort($items, fn (array $a, array $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));

        $this->writeFacilitiesData($items);
        $this->touchSeederFile();

        return [
            'synced' => true,
            'facility' => (string) $facility->name,
            'total' => count($items),
            'path' => 'database/seeders/data/facilities.json',
        ];
    }

    /**
     * @return array{synced: bool, total: int, path: string}
     */
    public function syncAllFacilities(): array
    {
        $items = Facility::query()
            ->with(['services:id', 'values', 'webContents'])
            ->orderBy('name')
            ->get()
            ->map(fn (Facility $facility) => $this->facilityToSeedArray($facility))
            ->values()
            ->all();

        $this->writeFacilitiesData($items);
        $this->touchSeederFile();

        return [
            'synced' => true,
            'total' => count($items),
            'path' => 'database/seeders/data/facilities.json',
        ];
    }

    public function seederSyncMessage(array $sync): ?string
    {
        if (! empty($sync['synced'])) {
            $facility = $sync['facility'] ?? 'facility';

            return ' FacilitySeeder data updated for ' . $facility
                . ' (' . ($sync['total'] ?? 0) . ' total). Commit database/seeders/data/facilities.json so migrate:fresh --seed restores settings.';
        }

        if (! empty($sync['error'])) {
            return ' FacilitySeeder update failed: ' . $sync['error'];
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function readFacilitiesData(): array
    {
        $path = $this->dataPath();

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function writeFacilitiesData(array $items): void
    {
        $path = $this->dataPath();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Could not create seeders data directory.');
        }

        $json = json_encode(
            $items,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException('Failed to encode facilities as JSON.');
        }

        if (file_put_contents($path, $json . PHP_EOL) === false) {
            throw new \RuntimeException('Could not write facilities.json.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function facilityToSeedArray(Facility $facility): array
    {
        $activeWebContent = $facility->webContents->firstWhere('is_active', true)
            ?? $facility->webContents->sortByDesc('updated_at')->first();

        $sections = $activeWebContent?->sections;
        if (is_string($sections)) {
            $sections = json_decode($sections, true) ?: [];
        }

        $variances = $activeWebContent?->variances;
        if (is_string($variances)) {
            $variances = json_decode($variances, true) ?: [];
        }

        $payload = [
            'id' => $facility->id,
            'slug' => $facility->slug,
            'name' => $facility->name,
            'tagline' => $facility->tagline,
            'headline' => $facility->headline,
            'subheadline' => $facility->subheadline,
            'address' => $facility->address,
            'phone' => $facility->phone,
            'city' => $facility->city,
            'state' => $facility->state,
            'zip' => $facility->zip,
            'beds' => $facility->beds,
            'color_scheme_id' => $facility->color_scheme_id,
            'about_image_url' => FacilityDataHelper::normalizeImageFilename($facility->about_image_url) ?? $facility->about_image_url,
            'location_map' => $facility->location_map,
            'subdomain' => $facility->subdomain,
            'domain' => $facility->domain,
            'years' => $facility->years,
            'facility_image' => FacilityDataHelper::normalizeImageFilename(
                $facility->facility_image,
                ['images', 'images/facilities']
            ) ?? $facility->facility_image,
            'hours' => $facility->hours,
            'hero_video_id' => $facility->hero_video_id,
            'hero_image_url' => FacilityDataHelper::normalizeImageFilename($facility->hero_image_url) ?? $facility->hero_image_url,
            'region' => $facility->region,
            'facility_number' => $facility->facility_number,
            'legal_name' => $facility->legal_name,
            'administrator' => $facility->administrator,
            'don' => $facility->don,
            'dsd' => $facility->dsd,
            'staffer' => $facility->staffer,
            'latitude' => $facility->latitude,
            'longitude' => $facility->longitude,
            'logo_url' => FacilityDataHelper::normalizeImageFilename($facility->logo_url) ?? $facility->logo_url,
            'about_text' => $facility->about_text,
            'email' => $facility->email,
            'facebook' => $facility->facebook,
            'twitter' => $facility->twitter,
            'instagram' => $facility->instagram,
            'is_active' => (bool) $facility->is_active,
            'meta_description' => $facility->meta_description,
            'hipaa_flags' => $facility->hipaa_flags,
            'npp_url' => $facility->npp_url,
            'settings' => $facility->settings,
            'layout_template' => $activeWebContent?->layout_template,
            'sections' => is_array($sections) ? array_values($sections) : [],
            'variances' => is_array($variances) ? $variances : [],
            'service_ids' => $facility->services->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'values' => $facility->values->pluck('value')->filter()->values()->all(),
        ];

        return array_filter($payload, function ($value) {
            if (is_bool($value) || is_array($value)) {
                return true;
            }

            return $value !== null && $value !== '';
        });
    }

    protected function matchKeyFor(array $item): string
    {
        if (! empty($item['slug'])) {
            return 'slug:' . $item['slug'];
        }

        if (! empty($item['facility_number'])) {
            return 'facility_number:' . $item['facility_number'];
        }

        if (! empty($item['id'])) {
            return 'id:' . $item['id'];
        }

        return 'name:' . Str::lower((string) ($item['name'] ?? ''));
    }

    protected function touchSeederFile(): void
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $path = $this->seederPath();

        if (! is_file($path)) {
            return;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return;
        }

        $contents = preg_replace(
            '/Last exported: .*/',
            'Last exported: ' . $exportedAt,
            $contents,
            1
        );

        file_put_contents($path, $contents);
    }
}
