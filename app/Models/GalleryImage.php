<?php

namespace App\Models;

use App\Models\Concerns\HasContentVisibility;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    use BelongsToTenant;
    use HasContentVisibility;

    protected $fillable = [
        'facility_id',
        'gallery_id',
        'title',
        'description',
        'caption',
        'image_url',
        'category',
        'order',
        'is_featured',
        'is_active',
        'visibility',
        'created_by',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (GalleryImage $image) {
            foreach (['image_url', 'thumbnail_url'] as $attribute) {
                $raw = $image->{$attribute} ?? null;
                if (! filled($raw)) {
                    continue;
                }

                $path = ltrim(str_replace('\\', '/', (string) $raw), '/');
                if (str_contains($path, '/storage/')) {
                    $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
                } elseif (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }

                if (filled($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        });
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayCaption(): ?string
    {
        return filled($this->caption) ? $this->caption : ($this->description ?: null);
    }

    public function publicUrl(): ?string
    {
        return $this->image_url ? asset('storage/'.$this->image_url) : null;
    }
}
