<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * Adds security headers including HSTS to all responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add HSTS in production and when using HTTPS
        if (app()->environment('production') && $request->secure()) {
            // HSTS header - forces HTTPS for 1 year, includes subdomains
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Security headers (apply in all environments)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy - adjust as needed for your application
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self';";
        
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}