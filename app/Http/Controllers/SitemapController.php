<?php
namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Facility;

class SitemapController extends Controller
{
    public function index()
    {
        $facilities = Facility::where('is_active', true)->get();
        $urls = [];
        foreach ($facilities as $facility) {
            $base = url($facility->slug);
            $urls[] = [ 'loc' => $base, 'priority' => '1.0' ];
            $urls[] = [ 'loc' => $base . '/privacy-policy', 'priority' => '0.8' ];
            $urls[] = [ 'loc' => $base . '/notice-of-privacy-practices', 'priority' => '0.8' ];
            $urls[] = [ 'loc' => $base . '/terms-of-service', 'priority' => '0.8' ];
            $urls[] = [ 'loc' => $base . '/accessibility', 'priority' => '0.7' ];
            $urls[] = [ 'loc' => $base . '/webmaster/contact', 'priority' => '0.7' ];
            // $urls[] = [ 'loc' => $base . '/blog', 'priority' => '0.7' ];
        }
        $xml = view('sitemap.xml', compact('urls'))->render();
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
