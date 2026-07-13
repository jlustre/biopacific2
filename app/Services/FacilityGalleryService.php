<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Facility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;
use App\Support\ContentVisibility;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacilityGalleryService
{
    public function listForFacility(
        Facility $facility,
        string $channel = 'portal',
        bool $includeInactive = false,
        ?string $search = null,
        ?int $year = null
    ): Collection {
        $facilityId = (int) $facility->id;

        return Gallery::query()
            ->with([
                'event',
                'creator',
                'facility',
                'sharedFacilities',
                'images' => fn ($q) => $q->where('is_active', true)->orderBy('order')->orderBy('id'),
            ])
            ->where(function ($q) use ($facilityId, $includeInactive) {
                $q->where(function ($own) use ($facilityId, $includeInactive) {
                    $own->where('facility_id', $facilityId)
                        ->when(! $includeInactive, fn ($inner) => $inner->where('is_active', true));
                })->orWhere(function ($shared) use ($facilityId) {
                    $shared->where('is_active', true)
                        ->where('share_scope', Gallery::SHARE_SCOPE_SHARED)
                        ->whereHas('sharedFacilities', fn ($f) => $f->where('facilities.id', $facilityId));
                });
            })
            ->when($channel !== 'all', fn ($q) => $q->visibleOn($channel))
            ->when($year, fn ($q) => $q->where('year', $year))
            ->when(filled($search), function ($q) use ($search) {
                $term = '%'.trim($search).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('event', fn ($event) => $event->where('title', 'like', $term));
                });
            })
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return list<int>
     */
    public function availableYearsForFacility(Facility $facility, string $channel = 'portal', bool $includeInactive = false): array
    {
        $facilityId = (int) $facility->id;

        return Gallery::query()
            ->whereNotNull('year')
            ->where(function ($q) use ($facilityId, $includeInactive) {
                $q->where(function ($own) use ($facilityId, $includeInactive) {
                    $own->where('facility_id', $facilityId)
                        ->when(! $includeInactive, fn ($inner) => $inner->where('is_active', true));
                })->orWhere(function ($shared) use ($facilityId) {
                    $shared->where('is_active', true)
                        ->where('share_scope', Gallery::SHARE_SCOPE_SHARED)
                        ->whereHas('sharedFacilities', fn ($f) => $f->where('facilities.id', $facilityId));
                });
            })
            ->when($channel !== 'all', fn ($q) => $q->visibleOn($channel))
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();
    }

    public function facilitiesAvailableForSharing(Facility $ownerFacility): Collection
    {
        return Facility::query()
            ->where('id', '!=', $ownerFacility->id)
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'state']);
    }

    public function ensureDefaultGallery(Facility $facility, ?User $user = null): Gallery
    {
        $existing = Gallery::query()
            ->where('facility_id', $facility->id)
            ->orderBy('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return Gallery::create([
            'facility_id' => $facility->id,
            'title' => trim($facility->name).' Gallery',
            'year' => (int) now()->year,
            'description' => 'Default facility gallery',
            'visibility' => ContentVisibility::BOTH,
            'is_active' => true,
            'sort_order' => 0,
            'created_by' => $user?->id,
        ]);
    }

    public function eventsForFacility(Facility $facility): Collection
    {
        return Event::query()
            ->where(function ($q) use ($facility) {
                $q->where('facility_id', $facility->id)
                    ->orWhere(function ($inner) {
                        $inner->where('scope', 'company')->whereNull('facility_id');
                    })
                    ->orWhere(function ($inner) use ($facility) {
                        $inner->where('scope', 'facility')->where('facility_id', $facility->id);
                    });
            })
            ->orderByDesc('event_date')
            ->orderBy('title')
            ->get();
    }

    public function createGallery(Facility $facility, User $user, array $data): Gallery
    {
        $gallery = Gallery::create([
            'facility_id' => $facility->id,
            'event_id' => $data['event_id'] ?? null,
            'title' => $data['title'],
            'year' => (int) ($data['year'] ?? now()->year),
            'description' => $data['description'] ?? null,
            'visibility' => ContentVisibility::normalize($data['visibility'] ?? null),
            'share_scope' => $this->normalizeShareScope($data['share_scope'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'created_by' => $user->id,
        ]);

        $this->syncSharedFacilities($gallery, $data['shared_facility_ids'] ?? []);

        return $gallery->fresh(['sharedFacilities']);
    }

    public function updateGallery(Gallery $gallery, array $data): Gallery
    {
        $gallery->fill([
            'event_id' => array_key_exists('event_id', $data) ? $data['event_id'] : $gallery->event_id,
            'title' => $data['title'] ?? $gallery->title,
            'year' => array_key_exists('year', $data) ? (int) $data['year'] : $gallery->year,
            'description' => array_key_exists('description', $data) ? $data['description'] : $gallery->description,
            'visibility' => isset($data['visibility'])
                ? ContentVisibility::normalize($data['visibility'])
                : $gallery->visibility,
            'share_scope' => array_key_exists('share_scope', $data)
                ? $this->normalizeShareScope($data['share_scope'])
                : $gallery->share_scope,
            'is_active' => array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : $gallery->is_active,
        ]);
        $gallery->save();

        if (array_key_exists('share_scope', $data) || array_key_exists('shared_facility_ids', $data)) {
            $this->syncSharedFacilities($gallery, $data['shared_facility_ids'] ?? []);
        }

        return $gallery->fresh(['sharedFacilities']);
    }

    /**
     * @param  list<int|string>|mixed  $facilityIds
     */
    protected function syncSharedFacilities(Gallery $gallery, mixed $facilityIds): void
    {
        if ($gallery->share_scope !== Gallery::SHARE_SCOPE_SHARED) {
            $gallery->sharedFacilities()->sync([]);

            return;
        }

        $ids = collect(is_array($facilityIds) ? $facilityIds : [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) $gallery->facility_id)
            ->unique()
            ->values()
            ->all();

        $gallery->sharedFacilities()->sync($ids);
    }

    protected function normalizeShareScope(?string $value): string
    {
        return array_key_exists((string) $value, Gallery::shareScopeOptions())
            ? (string) $value
            : Gallery::SHARE_SCOPE_FACILITY;
    }

    public function deleteGallery(Gallery $gallery): void
    {
        DB::transaction(function () use ($gallery) {
            $gallery->loadMissing('images');
            foreach ($gallery->images as $image) {
                $this->deleteImageFile($image);
                $image->delete();
            }
            $gallery->delete();
        });
    }

    /**
     * @param  list<UploadedFile>|UploadedFile  $files
     * @param  array<int, string|null>  $captions  keyed by upload index
     * @return Collection<int, GalleryImage>
     */
    public function addImages(Gallery $gallery, User $user, $files, array $captions = []): Collection
    {
        $files = is_array($files) ? $files : [$files];
        $created = collect();
        $maxOrder = (int) $gallery->images()->max('order');

        foreach (array_values($files) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('gallery/facility_'.$gallery->facility_id.'/gallery_'.$gallery->id, 'public');
            $maxOrder++;

            $created->push(GalleryImage::create([
                'facility_id' => $gallery->facility_id,
                'gallery_id' => $gallery->id,
                'image_url' => $path,
                'title' => $file->getClientOriginalName(),
                'caption' => $captions[$index] ?? null,
                'description' => $captions[$index] ?? null,
                'order' => $maxOrder,
                'is_active' => true,
                'visibility' => $gallery->visibility ?: ContentVisibility::BOTH,
                'created_by' => $user->id,
            ]));
        }

        return $created;
    }

    public function updateImageCaption(GalleryImage $image, ?string $caption): GalleryImage
    {
        $image->caption = $caption;
        $image->description = $caption;
        $image->save();

        return $image->refresh();
    }

    public function deleteImage(GalleryImage $image): void
    {
        $this->deleteImageFile($image);
        $image->delete();
    }

    protected function deleteImageFile(GalleryImage $image): void
    {
        foreach (['image_url', 'thumbnail_url'] as $attribute) {
            $path = $this->normalizeStoragePath($image->{$attribute} ?? null);
            if ($path === null) {
                continue;
            }

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    protected function normalizeStoragePath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        // Handle values accidentally stored as full public URLs or /storage/... paths.
        if (str_contains($path, '/storage/')) {
            $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return filled($path) ? $path : null;
    }
}
