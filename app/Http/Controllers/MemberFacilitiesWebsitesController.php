<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\Facility;
use App\Services\FacilityLeadershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberFacilitiesWebsitesController extends Controller
{
    use ProvidesMemberPortalContext;

    public function index()
    {
        $user = Auth::user();
        $facilities = Facility::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->reject(fn (Facility $facility) => $facility->isCorporatePublicSite());

        $corporate = Facility::corporateSite();
        if ($corporate) {
            $facilities->push($corporate);
        }

        $facilities = $facilities
            ->map(fn (Facility $facility) => $this->summarizeFacility($facility))
            ->values();

        $context = array_merge($this->memberPortalContext($user), [
            'portalPageTitle' => 'Facilities Websites',
            'portalActive' => 'facilities-websites',
            'facilities' => $facilities,
        ]);

        return view('dashboard.member.facilities-websites', $context);
    }

    public function show(Facility $facility): JsonResponse
    {
        $leadership = app(FacilityLeadershipService::class)->rosterForFacility($facility);

        return response()->json([
            'facility' => $this->detailFacility($facility),
            'leadership' => $leadership,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeFacility(Facility $facility): array
    {
        return [
            'id' => $facility->id,
            'name' => $facility->isCorporatePublicSite() ? 'Bio-Pacific Corporate' : $facility->name,
            'is_corporate' => $facility->isCorporatePublicSite(),
            'location' => $this->formatLocation($facility),
            'phone' => $facility->phone,
            'domain' => $this->publicDomainLabel($facility),
            'website_label' => $this->websiteLabel($facility),
            'website_url' => $this->websiteUrl($facility),
            'detail_url' => route('member.facilities.websites.show', $facility),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function detailFacility(Facility $facility): array
    {
        return array_merge($this->summarizeFacility($facility), [
            'legal_name' => $facility->legal_name,
            'tagline' => $facility->tagline,
            'address' => $facility->address,
            'city' => $facility->city,
            'state' => $facility->state,
            'zip' => $facility->zip,
            'email' => $facility->email,
            'facility_number' => $facility->facility_number,
            'region' => $facility->region,
            'beds' => $facility->beds,
            'hours' => $facility->hours,
            'about_text' => $facility->about_text,
            'facebook' => $facility->facebook,
            'twitter' => $facility->twitter,
            'instagram' => $facility->instagram,
        ]);
    }

    protected function formatLocation(Facility $facility): string
    {
        $parts = array_filter([
            $facility->city,
            $facility->state,
        ]);

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        return trim((string) ($facility->address ?? '')) ?: '—';
    }

    protected function websiteUrl(Facility $facility): ?string
    {
        if ($this->shouldUseCurrentRequestHostUrls()) {
            return $this->currentRequestHostUrlFor($facility);
        }

        if ($this->shouldUseStagingSiteUrls()) {
            return $this->stagingSiteUrlFor($facility);
        }

        if ($this->shouldUseConfiguredOperationalSiteUrls()) {
            return $this->configuredOperationalSiteUrlFor($facility);
        }

        if ($facility->isCorporatePublicSite()) {
            return $this->corporatePublicWebsiteUrl();
        }

        foreach ($this->websiteHostCandidates($facility) as $candidate) {
            $url = $this->normalizeExternalUrl($candidate);

            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    protected function websiteLabel(Facility $facility): ?string
    {
        if ($this->shouldUseCurrentRequestHostUrls()
            || $this->shouldUseStagingSiteUrls()
            || $this->shouldUseConfiguredOperationalSiteUrls()) {
            return $this->websiteUrlLabel($this->websiteUrl($facility));
        }

        if ($facility->isCorporatePublicSite()) {
            return $this->publicDomainLabel($facility);
        }

        $domain = trim((string) ($facility->domain ?? ''));

        if ($domain !== '') {
            return $domain;
        }

        $url = $this->websiteUrl($facility);

        if ($url === null) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    protected function shouldUseCurrentRequestHostUrls(): bool
    {
        $host = strtolower((string) request()->getHost());

        return app()->environment('local')
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }

    protected function shouldUseStagingSiteUrls(): bool
    {
        $host = strtolower((string) request()->getHost());

        return app()->environment('staging')
            || str_contains($host, 'staging');
    }

    protected function shouldUseConfiguredOperationalSiteUrls(): bool
    {
        return ! app()->environment('production')
            && filled(config('member-portal.operational_site_base'));
    }

    protected function currentRequestHostUrlFor(Facility $facility): string
    {
        $base = rtrim(request()->getSchemeAndHttpHost(), '/');

        if ($facility->isCorporatePublicSite()) {
            return $base . '/';
        }

        $slug = trim((string) ($facility->slug ?? ''));

        return $slug !== '' ? $base . '/' . $slug : $base . '/';
    }

    protected function stagingSiteBase(): string
    {
        return rtrim((string) config(
            'member-portal.operational_site_staging_base',
            'https://staging.biopacificoperational.com'
        ), '/');
    }

    protected function stagingSiteUrlFor(Facility $facility): string
    {
        return $this->operationalSiteUrlForBase($facility, $this->stagingSiteBase());
    }

    protected function configuredOperationalSiteBase(): string
    {
        return rtrim((string) config('member-portal.operational_site_base'), '/');
    }

    protected function configuredOperationalSiteUrlFor(Facility $facility): string
    {
        return $this->operationalSiteUrlForBase($facility, $this->configuredOperationalSiteBase());
    }

    protected function operationalSiteUrlForBase(Facility $facility, string $base): string
    {
        if ($facility->isCorporatePublicSite()) {
            return $base . '/';
        }

        $slug = trim((string) ($facility->slug ?? ''));

        return $slug !== '' ? $base . '/' . $slug : $base . '/';
    }

    protected function websiteUrlLabel(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        return preg_replace('~^https?://~i', '', rtrim($url, '/')) ?: null;
    }

    /**
     * Prefer the short domain field (e.g. *hcc.com / *hrc.com) over legacy subdomain hostnames.
     *
     * @return list<string|null>
     */
    protected function websiteHostCandidates(Facility $facility): array
    {
        $candidates = [];

        if (filled($facility->domain)) {
            $candidates[] = $facility->domain;
        }

        if (filled($facility->subdomain)) {
            $candidates[] = $facility->subdomain;
        }

        return $candidates;
    }

    protected function normalizeExternalUrl(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '//')) {
            return 'http:' . $raw;
        }

        if (preg_match('~^https?://~i', $raw) === 1) {
            return $raw;
        }

        // Marketing domains (e.g. *hcc.com) use Namecheap HTTP URL forwards — HTTPS often does not respond.
        return 'http://' . ltrim($raw, '/');
    }

    protected function publicDomainLabel(Facility $facility): ?string
    {
        if ($facility->isCorporatePublicSite()) {
            return config('member-portal.corporate_public_domain', 'biopacificoperational.com');
        }

        $domain = trim((string) ($facility->domain ?? ''));

        return $domain !== '' ? $domain : null;
    }

    protected function corporatePublicWebsiteUrl(): string
    {
        if ($this->shouldUseCurrentRequestHostUrls()) {
            return $this->currentRequestHostUrlFor(Facility::corporateSite() ?? new Facility());
        }

        if ($this->shouldUseStagingSiteUrls()) {
            return $this->stagingSiteBase() . '/';
        }

        if ($this->shouldUseConfiguredOperationalSiteUrls()) {
            return $this->configuredOperationalSiteBase() . '/';
        }

        $domain = preg_replace('/^www\./i', '', (string) config(
            'member-portal.corporate_public_domain',
            'biopacificoperational.com'
        ));

        return 'https://www.' . $domain;
    }
}
