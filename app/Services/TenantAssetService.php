<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class TenantAssetService
{
    protected $facility;

    public function __construct()
    {
        $this->facility = app()->bound('current_facility') ? app('current_facility') : null;
    }

    public function getLogoUrl()
    {
        if ($this->facility && $this->facility->logo_url) {
            return $this->facility->logo_url;
        }

        return asset('images/default-logo.png');
    }

    public function getHeroImageUrl()
    {
        if ($this->facility && $this->facility->hero_image_url) {
            return $this->facility->hero_image_url;
        }

        return asset('images/default-hero.jpg');
    }

    public function getFaviconUrl()
    {
        if (!$this->facility) {
            return asset('images/default-favicon.ico');
        }

        $favicon = $this->facility->getSetting('favicon');

        if ($favicon) {
            return Storage::url($favicon);
        }

        return asset('images/default-favicon.ico');
    }

    public function getAssetPath($type, $filename = null)
    {
        if (!$this->facility) {
            return $filename ? "default/{$type}/{$filename}" : "default/{$type}";
        }

        $basePath = "tenants/{$this->facility->id}/{$type}";

        return $filename ? "{$basePath}/{$filename}" : $basePath;
    }

    public function getCustomCSS()
    {
        if (!$this->facility) {
            return '';
        }

        $colors = app(TenantConfigService::class)->getThemeColors();
        $customCSS = $this->facility->getSetting('custom_css', '');

        // Generate CSS variables for theme colors
        $cssVariables = ":root {";
        $cssVariables .= "--color-primary: {$colors['primary']};";
        $cssVariables .= "--color-secondary: {$colors['secondary']};";
        $cssVariables .= "--color-accent: {$colors['accent']};";
        $cssVariables .= "}";

        return $cssVariables . "\n" . $customCSS;
    }
}
