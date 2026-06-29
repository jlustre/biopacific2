<?php

namespace App\Http\Middleware;

use App\Support\SelectedFacility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PersistSelectedFacility
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            SelectedFacility::captureFromRequest($request);
        }

        return $next($request);
    }
}
