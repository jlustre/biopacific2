<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutSection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'variants',
        'config_schema',
        'component_path',
        'is_active'
    ];

    protected $casts = [
        'variants' => 'array',
        'config_schema' => 'array',
        'is_active' => 'boolean'
    ];

    public function getVariant($variantName)
    {
        $variants = $this->variants ?? [];

        // Handle variants as key-value pairs (our current structure)
        if (is_array($variants) && isset($variants[$variantName])) {
            return $variants[$variantName];
        }

        // Handle variants as array of objects (legacy structure)
        return collect($variants)->firstWhere('name', $variantName) ?? $variants[0] ?? null;
    }

    public function getComponentPath($variant = 'default')
    {
        // Base component path with variant
        $basePath = $this->component_path;

        // If variant is default, check if specific variant file exists
        if ($variant === 'default') {
            return $basePath . '.default';
        }

        // For other variants, append the variant name
        return $basePath . '.' . $variant;
    }
}
