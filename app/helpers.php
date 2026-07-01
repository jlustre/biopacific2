<?php

use App\Support\FacilityCustomDomain;

if (! function_exists('facility_public_path')) {
    function facility_public_path(string $suffix = '', ?string $slug = null): string
    {
        return FacilityCustomDomain::publicPath($slug, $suffix);
    }
}

if (! function_exists('facility_public_url')) {
    function facility_public_url(string $suffix = '', ?string $slug = null, ?string $fragment = null): string
    {
        return FacilityCustomDomain::publicUrl($slug, $suffix, $fragment);
    }
}

if (! function_exists('facility_public_route')) {
    function facility_public_route(string $name, ?string $slug = null, array $parameters = []): string
    {
        return FacilityCustomDomain::namedRoute($name, $slug, $parameters);
    }
}
