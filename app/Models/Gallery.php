<?php

namespace App\Models;

use App\Models\Concerns\HasContentVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasContentVisibility;

    protected $fillable = [
        'facility_id',
        'event_id',
        'title',
        'year',
        'slug',
        'description',
        'visibility',
        'share_scope',
        'is_active',
        'sort_order',
        'created_by',
    ];

    public const SHARE_SCOPE_FACILITY = 'facility';

    public const SHARE_SCOPE_SHARED = 'shared';

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * @return array<string, string>
     */
    public static function shareScopeOptions(): array
    {
        return [
            self::SHARE_SCOPE_FACILITY => 'This facility only',
            self::SHARE_SCOPE_SHARED => 'Share with other facilities (read-only)',
        ];
    }

    public function getShareScopeLabelAttribute(): string
    {
        return self::shareScopeOptions()[$this->share_scope] ?? 'This facility only';
    }

    public function isPublished(): bool
    {
        return (bool) $this->is_active;
    }

    public function isSharedBeyondOwner(): bool
    {
        return $this->share_scope === self::SHARE_SCOPE_SHARED;
    }

    public function isOwnedByFacility(?int $facilityId): bool
    {
        return $facilityId !== null && (int) $this->facility_id === (int) $facilityId;
    }

    public function isSharedWithFacility(?int $facilityId): bool
    {
        if ($facilityId === null) {
            return false;
        }

        if ($this->isOwnedByFacility($facilityId)) {
            return true;
        }

        return $this->sharedFacilities()->where('facilities.id', $facilityId)->exists();
    }

    protected static function booted(): void
    {
        static::creating(function (Gallery $gallery) {
            if (blank($gallery->slug)) {
                $gallery->slug = static::uniqueSlugForFacility(
                    (int) $gallery->facility_id,
                    (string) $gallery->title
                );
            }
        });

        static::updating(function (Gallery $gallery) {
            if ($gallery->isDirty('title') && ! $gallery->isDirty('slug')) {
                $gallery->slug = static::uniqueSlugForFacility(
                    (int) $gallery->facility_id,
                    (string) $gallery->title,
                    (int) $gallery->id
                );
            }
        });
    }

    public static function uniqueSlugForFacility(int $facilityId, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'gallery';
        $slug = $base;
        $n = 1;

        while (static::query()
            ->where('facility_id', $facilityId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function sharedFacilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_gallery')
            ->withTimestamps();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('order')->orderBy('id');
    }

    public function activeImages(): HasMany
    {
        return $this->images()->where('is_active', true);
    }

    public function coverImage(): ?GalleryImage
    {
        return $this->activeImages()
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->orderBy('id')
            ->first();
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->created_by === (int) $user->id;
    }
}
