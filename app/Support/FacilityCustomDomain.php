<?php

namespace App\Support;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityCustomDomain
{
    /** @var list<string> */
    private const RESERVED_PATH_PREFIXES = [
        'admin',
        'api',
        'book',
        'careers',
        'dashboard',
        'debug-upload-view',
        'employee-application',
        'facilities',
        'facility-dashboard',
        'home',
        'hr-portal',
        'livewire',
        'login',
        'logout',
        'my-employment',
        'my-pre-employment',
        'password',
        'pre-employment',
        'register',
        'secure',
        'sitemap.xml',
        'storage',
        'test-urls',
        'up',
    ];

    public static function normalizeHost(?string $host): string
    {
        $host = strtolower(trim((string) $host));

        if (str_contains($host, '://')) {
            $parsed = parse_url($host, PHP_URL_HOST);
            $host = is_string($parsed) ? $parsed : $host;
        }

        $host = preg_replace('~^www\.~i', '', $host) ?? $host;
        $host = rtrim($host, '.');

        return $host;
    }

    /**
     * Hosts that serve the multi-facility operational site (slug URLs), not custom domains.
     *
     * @return list<string>
     */
    public static function applicationHosts(): array
    {
        static $hosts = null;

        if ($hosts !== null) {
            return $hosts;
        }

        $candidates = ['localhost', '127.0.0.1', '[::1]'];

        foreach ([
            config('app.url'),
            config('member-portal.operational_site_production_base'),
            config('member-portal.operational_site_staging_base'),
        ] as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }

            $host = parse_url($url, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $candidates[] = $host;
            }
        }

        $corporateDomain = (string) config('member-portal.corporate_public_domain', '');
        if ($corporateDomain !== '') {
            $candidates[] = $corporateDomain;
            $candidates[] = 'www.' . $corporateDomain;
            $candidates[] = 'staging.' . $corporateDomain;
        }

        $hosts = array_values(array_unique(array_filter(array_map(
            [static::class, 'normalizeHost'],
            $candidates
        ))));

        return $hosts;
    }

    public static function isApplicationHost(string $host): bool
    {
        return in_array(static::normalizeHost($host), static::applicationHosts(), true);
    }

    public static function findByHost(string $host): ?Facility
    {
        $host = static::normalizeHost($host);

        if ($host === '' || static::isApplicationHost($host)) {
            return null;
        }

        return Facility::query()
            ->where('is_active', true)
            ->where('slug', '!=', Facility::corporateSiteSlug())
            ->get()
            ->first(function (Facility $facility) use ($host): bool {
                foreach ([$facility->domain, $facility->subdomain] as $candidate) {
                    if (! filled($candidate)) {
                        continue;
                    }

                    if (static::normalizeHost((string) $candidate) === $host) {
                        return true;
                    }
                }

                return false;
            });
    }

    public static function currentFacility(): ?Facility
    {
        if (! app()->bound('current_facility')) {
            return null;
        }

        $facility = app('current_facility');

        return $facility instanceof Facility ? $facility : null;
    }

    public static function isActive(): bool
    {
        return app()->bound('facility_custom_domain')
            && app('facility_custom_domain') === true
            && static::currentFacility() !== null;
    }

    public static function resolveForRequest(Request $request): ?Facility
    {
        return static::findByHost($request->getHost());
    }

    public static function reservedPathPrefix(string $path): bool
    {
        $segment = explode('/', trim($path, '/'))[0] ?? '';

        return $segment !== '' && in_array($segment, self::RESERVED_PATH_PREFIXES, true);
    }

    public static function rewritePathForFacility(string $path, Facility $facility): string
    {
        $path = trim($path, '/');
        $slug = trim((string) $facility->slug);

        if ($slug === '' || static::reservedPathPrefix($path)) {
            return $path;
        }

        if ($path === '' || $path === $slug) {
            return $slug;
        }

        if (str_starts_with($path, $slug . '/')) {
            return $path;
        }

        return $slug . '/' . $path;
    }

    public static function applyRequestRewrite(Request $request, Facility $facility): void
    {
        $path = trim($request->path(), '/');
        $internalPath = static::rewritePathForFacility($path, $facility);

        if ($internalPath === $path) {
            return;
        }

        $query = $request->getQueryString();
        $uri = '/' . ltrim($internalPath, '/') . ($query ? '?' . $query : '');

        $request->server->set('REQUEST_URI', $uri);
        $request->server->set('PATH_INFO', '/' . ltrim($internalPath, '/'));
    }

    public static function publicPath(?string $slug = null, string $suffix = ''): string
    {
        $suffix = trim($suffix, '/');

        if (static::isActive()) {
            return $suffix === '' ? '/' : '/' . $suffix;
        }

        $slug = $slug ?? static::currentFacility()?->slug ?? '';
        if ($slug === '') {
            return $suffix === '' ? '/' : '/' . $suffix;
        }

        return '/' . $slug . ($suffix !== '' ? '/' . $suffix : '');
    }

    public static function publicUrl(?string $slug = null, string $suffix = '', ?string $fragment = null): string
    {
        $url = url(static::publicPath($slug, $suffix));

        if ($fragment !== null && $fragment !== '') {
            $url .= '#' . ltrim($fragment, '#');
        }

        return $url;
    }

    public static function namedRoute(string $name, ?string $slug = null, array $parameters = []): string
    {
        if (static::isActive()) {
            $customPaths = config('member-portal.facility_custom_domain_routes', []);
            if (isset($customPaths[$name])) {
                return url($customPaths[$name]);
            }
        }

        $slug = $slug ?? static::currentFacility()?->slug;
        if ($slug !== null && $slug !== '') {
            $parameters = array_merge(['facility' => $slug], $parameters);
        }

        return route($name, $parameters);
    }

    public static function legalPageLinkPrefix(?string $slug = null): string
    {
        $currentPath = request()->path();
        $isLegalPage = str_contains($currentPath, 'privacy-policy')
            || str_contains($currentPath, 'terms-of-service')
            || str_contains($currentPath, 'accessibility')
            || str_contains($currentPath, 'notice-of-privacy-practices')
            || str_contains($currentPath, 'webmaster/contact');

        if (static::isActive()) {
            return $isLegalPage ? '/' : '';
        }

        $slug = $slug ?? static::currentFacility()?->slug ?? '';
        if ($slug === '') {
            return '';
        }

        return $isLegalPage ? '/' . $slug : '';
    }
}
