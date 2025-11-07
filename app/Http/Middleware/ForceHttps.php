<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * Redirects HTTP requests to HTTPS in production environment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production
        if (app()->environment('production') && !$request->secure()) {
            // Redirect to HTTPS version of the same URL
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}