<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Facility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = database_path('seeders/data/galleries.php');

        if (! File::exists($dataFile)) {
            return;
        }

        $catalog = require $dataFile;
        if (! is_array($catalog)) {
            throw new \RuntimeException('The gallery seeder data file must return an array.');
        }

        foreach ($catalog as $galleryData) {
            $facility = $this->resolveFacility($galleryData);
            if (! $facility) {
                $this->command?->warn("Skipping gallery '{$galleryData['title']}': facility not found.");

                continue;
            }

            $creatorId = filled($galleryData['creator_email'] ?? null)
                ? User::query()->where('email', $galleryData['creator_email'])->value('id')
                : null;
            $eventId = filled($galleryData['event_title'] ?? null)
                ? Event::query()
                    ->where('title', $galleryData['event_title'])
                    ->where(function ($query) use ($facility) {
                        $query->where('facility_id', $facility->id)->orWhereNull('facility_id');
                    })
                    ->value('id')
                : null;

            $gallery = Gallery::query()->updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'slug' => $galleryData['slug'],
                ],
                [
                    'event_id' => $eventId,
                    'title' => $galleryData['title'],
                    'year' => $galleryData['year'] ?? null,
                    'description' => $galleryData['description'] ?? null,
                    'visibility' => $galleryData['visibility'] ?? 'both',
                    'share_scope' => $galleryData['share_scope'] ?? Gallery::SHARE_SCOPE_FACILITY,
                    'is_active' => (bool) ($galleryData['is_active'] ?? true),
                    'sort_order' => (int) ($galleryData['sort_order'] ?? 0),
                    'created_by' => $creatorId,
                ]
            );

            $sharedFacilityIds = Facility::query()
                ->whereIn('slug', $galleryData['shared_facility_slugs'] ?? [])
                ->pluck('id')
                ->all();
            $gallery->sharedFacilities()->sync($sharedFacilityIds);

            foreach ($galleryData['images'] ?? [] as $imageData) {
                $seedFile = database_path('seeders/data/'.ltrim((string) $imageData['seed_file'], '/\\'));
                $storagePath = ltrim(str_replace('\\', '/', (string) $imageData['storage_path']), '/');

                if (! File::exists($seedFile)) {
                    throw new \RuntimeException("Gallery seed photo is missing: {$seedFile}");
                }

                Storage::disk('public')->put($storagePath, File::get($seedFile));

                $image = GalleryImage::query()->updateOrCreate(
                    [
                        'gallery_id' => $gallery->id,
                        'image_url' => $storagePath,
                    ],
                    [
                        'facility_id' => $facility->id,
                        'title' => $imageData['title'] ?? null,
                        'description' => $imageData['description'] ?? null,
                        'caption' => $imageData['caption'] ?? null,
                        'category' => $imageData['category'] ?? null,
                        'order' => (int) ($imageData['order'] ?? 0),
                        'is_featured' => (bool) ($imageData['is_featured'] ?? false),
                        'is_active' => (bool) ($imageData['is_active'] ?? true),
                        'visibility' => $imageData['visibility'] ?? ($galleryData['visibility'] ?? 'both'),
                        'created_by' => $creatorId,
                    ]
                );

                $image->save();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $galleryData
     */
    protected function resolveFacility(array $galleryData): ?Facility
    {
        if (filled($galleryData['facility_slug'] ?? null)) {
            $facility = Facility::query()->where('slug', $galleryData['facility_slug'])->first();
            if ($facility) {
                return $facility;
            }
        }

        return Facility::query()->where('name', $galleryData['facility_name'] ?? '')->first();
    }
}
