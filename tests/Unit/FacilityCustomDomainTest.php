<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Support\FacilityCustomDomain;
use PHPUnit\Framework\TestCase;

class FacilityCustomDomainTest extends TestCase
{
    public function test_normalize_host_strips_www_and_scheme(): void
    {
        $this->assertSame('almadenhrc.com', FacilityCustomDomain::normalizeHost('www.almadenhrc.com'));
        $this->assertSame('almadenhrc.com', FacilityCustomDomain::normalizeHost('https://www.almadenhrc.com'));
    }

    public function test_rewrite_path_maps_custom_domain_urls_to_slug_routes(): void
    {
        $facility = new Facility([
            'slug' => 'almaden-healthcare-and-rehabilitation-center',
        ]);

        $this->assertSame(
            'almaden-healthcare-and-rehabilitation-center',
            FacilityCustomDomain::rewritePathForFacility('', $facility)
        );

        $this->assertSame(
            'almaden-healthcare-and-rehabilitation-center/privacy-policy',
            FacilityCustomDomain::rewritePathForFacility('privacy-policy', $facility)
        );

        $this->assertSame(
            'login',
            FacilityCustomDomain::rewritePathForFacility('login', $facility)
        );
    }

    public function test_public_path_uses_slug_on_operational_site(): void
    {
        $this->assertSame(
            '/almaden-healthcare-and-rehabilitation-center/privacy-policy',
            FacilityCustomDomain::publicPath('almaden-healthcare-and-rehabilitation-center', 'privacy-policy')
        );
    }
}
