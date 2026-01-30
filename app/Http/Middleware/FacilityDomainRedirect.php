<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacilityDomainRedirect
{
    /**
     * Map of custom domains to facility slugs.
     * Add all your domains and their corresponding slugs here.
     */
    protected $domainMap = [
        'www.almadenhrc.com' => 'almaden-healthcare-and-rehabilitation-center',
        // 'www.examplefacility.com' => 'example-facility-slug',
        // Add more domains and slugs as needed
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        if (isset($this->domainMap[$host])) {
            $slug = $this->domainMap[$host];
            // Internally rewrite the request to the facility slug route
            $request->server->set('REQUEST_URI', "/facility/{$slug}");
            $request->server->set('PATH_INFO', "/facility/{$slug}");
        }
        return $next($request);
    }
}
