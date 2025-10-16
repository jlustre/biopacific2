<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'facility_id',
        'name',
        'title',
        'title_header',
        'story',
        'quote',
        'relationship',
        'rating',
        'photo_url',
        'is_featured',
        'is_active'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];
    // Delete avatar file from storage when testimonial is deleted
    protected static function booted()
    {
        static::deleting(function ($testimonial) {
            if ($testimonial->photo_url && str_starts_with($testimonial->photo_url, '/storage/')) {
                $path = str_replace('/storage/', '', $testimonial->photo_url);
                Storage::disk('public')->delete($path);
            }
        });
    }
}
