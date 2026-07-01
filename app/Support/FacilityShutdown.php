<?php

namespace App\Support;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacilityShutdown
{
    /** @var list<string> */
    private const RESERVED_FIRST_SEGMENTS = [
        'admin',
        'api',
        'careers',
        'dashboard',
        'debug-upload-view',
        'facility-dashboard',
        'home',
        'hr-portal',
        'login',
        'logout',
        'my-pre-employment',
        'password',
        'register',
        'sitemap.xml',
        'test-urls',
        'up',
        'webmaster',
    ];

    public static function isGloballyShutdown(): bool
    {
        if (!Schema::hasTable('global_shutdowns')) {
            return false;
        }

        $global = DB::table('global_shutdowns')->orderByDesc('id')->first();

        return (bool) ($global?->is_shutdown ?? false);
    }

    public static function isFacilityShutdown(Facility $facility): bool
    {
        return (bool) $facility->is_shutdown;
    }

    public static function shouldBlockPublicAccess(Facility $facility): bool
    {
        return self::isGloballyShutdown() || self::isFacilityShutdown($facility);
    }

    public static function responseFor(?Facility $facility): ?Response
    {
        if (self::isGloballyShutdown()) {
            $global = DB::table('global_shutdowns')->orderByDesc('id')->first();

            return response()->view('shutdown', [
                'message' => $global->shutdown_message ?? null,
                'eta' => $global->shutdown_eta ?? null,
                'isGlobal' => true,
            ]);
        }

        if ($facility && self::isFacilityShutdown($facility)) {
            return response()->view('shutdown', [
                'message' => $facility->shutdown_message,
                'eta' => $facility->shutdown_eta,
                'isGlobal' => false,
                'facilityName' => (string) $facility,
            ]);
        }

        return null;
    }

    public static function resolveFromRequest(Request $request): ?Facility
    {
        if ($facility = \App\Support\FacilityCustomDomain::currentFacility()) {
            return $facility;
        }

        $routeFacility = $request->route('facility');

        if ($routeFacility instanceof Facility) {
            return $routeFacility;
        }

        if (is_string($routeFacility) && $routeFacility !== '') {
            return Facility::where('slug', $routeFacility)->first();
        }

        $routeId = $request->route('id');
        if (is_numeric($routeId)) {
            return Facility::find((int) $routeId);
        }

        $slug = $request->segment(1);
        if (!$slug || in_array($slug, self::RESERVED_FIRST_SEGMENTS, true)) {
            return null;
        }

        return Facility::where('slug', $slug)->first();
    }
}
