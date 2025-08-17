<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sections',
        'default_config',
        'preview_image',
        'is_active'
    ];

    protected $casts = [
        'sections' => 'array',
        'default_config' => 'array',
        'is_active' => 'boolean'
    ];

    public function facilities()
    {
        return $this->hasMany(Facility::class, 'layout_template', 'slug');
    }

    public function getSectionsWithConfig()
    {
        $sections = [];

        foreach ($this->sections as $sectionSlug) {
            $section = LayoutSection::where('slug', $sectionSlug)
                                  ->where('is_active', true)
                                  ->first();

            if ($section) {
                $sections[] = [
                    'section' => $section,
                    'default_config' => $this->default_config[$sectionSlug] ?? []
                ];
            }
        }

        return $sections;
    }
}
