<?php

namespace App\Http\Middleware;

use App\Support\FacilityCustomDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveFacilityCustomDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $facility = FacilityCustomDomain::resolveForRequest($request);

        if ($facility === null) {
            return $next($request);
        }

        app()->instance('current_facility', $facility);
        app()->instance('facility_custom_domain', true);

        URL::forceRootUrl($request->getSchemeAndHttpHost());

        View::share('facility', $facility->toArray());
        View::share('facilityCustomDomain', true);

        FacilityCustomDomain::applyRequestRewrite($request, $facility);

        return $next($request);
    }
}
