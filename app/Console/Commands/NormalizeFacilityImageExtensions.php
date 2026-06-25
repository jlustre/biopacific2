<?php

namespace App\Console\Commands;

use App\Helpers\FacilityDataHelper;
use App\Models\Facility;
use Illuminate\Console\Command;

class NormalizeFacilityImageExtensions extends Command
{
    protected $signature = 'facilities:normalize-image-extensions {--dry-run : Show changes without updating the database}';

    protected $description = 'Update facility image fields to match files on disk (e.g. .png → .jpg after image conversion)';

    /**
     * @var list<string>
     */
    protected array $imageFields = [
        'hero_image_url' => ['images'],
        'about_image_url' => ['images'],
        'logo_url' => ['images'],
        'facility_image' => ['images', 'images/facilities'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changes = 0;

        Facility::query()->orderBy('id')->each(function (Facility $facility) use ($dryRun, &$changes) {
            $updates = [];

            foreach ($this->imageFields as $field => $directories) {
                $original = $facility->{$field};

                if (blank($original)) {
                    continue;
                }

                $storedName = basename(str_replace('\\', '/', (string) $original));
                $normalized = FacilityDataHelper::normalizeImageFilename($storedName, $directories);

                if ($normalized && $normalized !== $storedName) {
                    $updates[$field] = $normalized;
                    $this->line(sprintf(
                        '  [%s] %s: %s → %s',
                        $facility->slug,
                        $field,
                        $original,
                        $normalized
                    ));
                } elseif ($storedName !== $original && $normalized) {
                    $updates[$field] = $normalized;
                    $this->line(sprintf(
                        '  [%s] %s: %s → %s',
                        $facility->slug,
                        $field,
                        $original,
                        $normalized
                    ));
                }
            }

            if ($updates === []) {
                return;
            }

            $changes += count($updates);

            if (! $dryRun) {
                $facility->update($updates);
            }
        });

        if ($changes === 0) {
            $this->info('No facility image fields needed updating.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? 'Would update ' : 'Updated ') . $changes . ' image field(s).');

        if ($dryRun) {
            $this->comment('Run without --dry-run to apply changes.');
        }

        return self::SUCCESS;
    }
}
