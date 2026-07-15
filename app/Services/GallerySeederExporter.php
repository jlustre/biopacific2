<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GallerySeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/galleries.php');
    }

    public function mediaDirectory(): string
    {
        return database_path('seeders/data/gallery_media');
    }

    /**
     * @return array{galleries: int, photos: int, bytes: int, path: string}
     */
    public function writeSeederFiles(): array
    {
        $mediaDirectory = $this->mediaDirectory();

        File::deleteDirectory($mediaDirectory);
        File::ensureDirectoryExists($mediaDirectory);

        $photoCount = 0;
        $bytes = 0;

        $galleries = Gallery::query()
            ->with(['facility', 'event', 'creator', 'sharedFacilities', 'images'])
            ->orderBy('facility_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Gallery $gallery) use (&$photoCount, &$bytes): array {
                if (! $gallery->facility) {
                    throw new \RuntimeException("Gallery {$gallery->id} has no facility.");
                }

                $facilityKey = $gallery->facility->slug ?: Str::slug($gallery->facility->name);
                $galleryKey = $gallery->slug ?: Str::slug($gallery->title);
                $galleryVisibility = (string) $gallery->visibility;

                $images = $gallery->images
                    ->map(function (GalleryImage $image) use ($facilityKey, $galleryKey, $galleryVisibility, &$photoCount, &$bytes): array {
                        $sourcePath = $this->normalizeStoragePath($image->image_url);

                        if (! $sourcePath || ! Storage::disk('public')->exists($sourcePath)) {
                            throw new \RuntimeException(
                                "Photo file is missing for gallery image {$image->id}: ".($image->image_url ?: '(empty path)')
                            );
                        }

                        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
                        $baseName = Str::slug((string) pathinfo($sourcePath, PATHINFO_FILENAME)) ?: 'photo';
                        $snapshotName = sprintf(
                            '%06d_%s%s',
                            (int) $image->id,
                            $baseName,
                            $extension !== '' ? '.'.$extension : ''
                        );
                        $seedRelativePath = implode('/', [
                            'gallery_media',
                            $facilityKey,
                            $galleryKey,
                            $snapshotName,
                        ]);
                        $seedAbsolutePath = database_path('seeders/data/'.$seedRelativePath);

                        File::ensureDirectoryExists(dirname($seedAbsolutePath));
                        File::copy(Storage::disk('public')->path($sourcePath), $seedAbsolutePath);

                        $size = (int) File::size($seedAbsolutePath);
                        $photoCount++;
                        $bytes += $size;

                        return [
                            'seed_file' => $seedRelativePath,
                            'storage_path' => $sourcePath,
                            'title' => filled($image->title) ? (string) $image->title : null,
                            'description' => filled($image->description) ? (string) $image->description : null,
                            'caption' => filled($image->caption) ? (string) $image->caption : null,
                            'category' => filled($image->category) ? (string) $image->category : null,
                            'order' => (int) $image->order,
                            'is_featured' => (bool) $image->is_featured,
                            'is_active' => (bool) $image->is_active,
                            'visibility' => (string) ($image->visibility ?: $galleryVisibility),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'facility_slug' => (string) ($gallery->facility->slug ?? ''),
                    'facility_name' => (string) $gallery->facility->name,
                    'title' => (string) $gallery->title,
                    'year' => $gallery->year ? (int) $gallery->year : null,
                    'slug' => (string) $gallery->slug,
                    'description' => filled($gallery->description) ? (string) $gallery->description : null,
                    'visibility' => (string) $gallery->visibility,
                    'share_scope' => (string) $gallery->share_scope,
                    'is_active' => (bool) $gallery->is_active,
                    'sort_order' => (int) $gallery->sort_order,
                    'creator_email' => $gallery->creator?->email,
                    'event_title' => $gallery->event?->title,
                    'shared_facility_slugs' => $gallery->sharedFacilities
                        ->pluck('slug')
                        ->filter()
                        ->map(fn ($slug) => (string) $slug)
                        ->values()
                        ->all(),
                    'images' => $images,
                ];
            })
            ->values()
            ->all();

        $content = $this->buildDataFileContents($galleries);
        if (file_put_contents($this->dataFilePath(), $content) === false) {
            throw new \RuntimeException('Could not write the galleries seeder data file.');
        }

        return [
            'galleries' => count($galleries),
            'photos' => $photoCount,
            'bytes' => $bytes,
            'path' => $this->dataFilePath(),
        ];
    }

    protected function normalizeStoragePath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim((string) $path)), '/');

        if (str_contains($path, '/storage/')) {
            $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return filled($path) ? $path : null;
    }

    /**
     * @param  list<array<string, mixed>>  $galleries
     */
    protected function buildDataFileContents(array $galleries): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $export = var_export($galleries, true);

        return <<<PHP
<?php

/**
 * Facility galleries and photo metadata.
 *
 * Photo binaries are stored under database/seeders/data/gallery_media and are
 * copied back to the public storage disk by GallerySeeder.
 *
 * Auto-generated from Photo Galleries → Update seeder on {$exportedAt}.
 *
 * @return list<array<string, mixed>>
 */
return {$export};

PHP;
    }
}
