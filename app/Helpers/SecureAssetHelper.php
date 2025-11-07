<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

class SecureAssetHelper
{
    /**
     * Generate a secure asset URL.
     * Ensures all assets use HTTPS in production to prevent mixed content.
     */
    public static function secureAsset(string $path): string
    {
        // In production, force HTTPS for all assets
        if (app()->environment('production')) {
            return str_replace('http://', 'https://', asset($path));
        }

        return asset($path);
    }

    /**
     * Generate a secure URL.
     * Ensures all URLs use HTTPS in production.
     */
    public static function secureUrl(string $path = null, array $parameters = []): string
    {
        if (app()->environment('production')) {
            return str_replace('http://', 'https://', url($path, $parameters));
        }

        return url($path, $parameters);
    }

    /**
     * Generate a secure route URL.
     * Ensures all route URLs use HTTPS in production.
     */
    public static function secureRoute(string $name, array $parameters = []): string
    {
        if (app()->environment('production')) {
            return str_replace('http://', 'https://', route($name, $parameters));
        }

        return route($name, $parameters);
    }

    /**
     * Fix mixed content in HTML strings.
     * Converts any http:// URLs to https:// in production.
     */
    public static function fixMixedContent(string $html): string
    {
        if (app()->environment('production')) {
            // Replace http:// with https:// but avoid replacing localhost URLs
            return preg_replace('/http:\/\/(?!localhost)/', 'https://', $html);
        }

        return $html;
    }

    /**
     * Ensure external URLs use HTTPS in production.
     */
    public static function secureExternalUrl(string $url): string
    {
        if (app()->environment('production') && str_starts_with($url, 'http://')) {
            // Only convert to HTTPS if the domain supports it
            // You might want to maintain a whitelist of known HTTPS-supporting domains
            return str_replace('http://', 'https://', $url);
        }

        return $url;
    }
}