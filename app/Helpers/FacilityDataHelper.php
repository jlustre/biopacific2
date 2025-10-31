<?php

namespace App\Helpers;

use App\Models\Facility;
use App\Models\Service;
use App\Models\News;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\ColorScheme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacilityDataHelper
{

    /**
     * Get gallery images for a facility, ordered by 'order'
     */
    public static function getGalleryImages($facilityId)
    {
        return \App\Models\GalleryImage::where('facility_id', $facilityId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
    /**
     * Extract sections, section variances, and layout template from active web content
     */
    public static function getLayoutData($activeWebContent)
    {
        $sections = [];
        $sectionVariances = [];
        $layoutTemplate = 'default-template';

        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                $sections = json_decode($activeWebContent->sections, true) ?? [];
            } elseif (is_array($activeWebContent->sections)) {
                $sections = $activeWebContent->sections;
            }
        }

        if ($activeWebContent && isset($activeWebContent->variances)) {
            if (is_string($activeWebContent->variances)) {
                $sectionVariances = json_decode($activeWebContent->variances, true) ?? [];
            } elseif (is_array($activeWebContent->variances)) {
                $sectionVariances = $activeWebContent->variances;
            }
        }

        $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';

        return [
            'sections' => $sections,
            'sectionVariances' => $sectionVariances,
            'layoutTemplate' => $layoutTemplate
        ];
    }

    public static function getColors(Facility $facility)
    {
        $colorScheme = null;
        if (!empty($facility->color_scheme_id)) {
            $colorScheme = ColorScheme::find($facility->color_scheme_id);
        }
        
        return [
            'primary' => $colorScheme->primary_color ?? '#059669',
            'secondary' => $colorScheme->secondary_color ?? '#064E3B',
            'accent' => $colorScheme->accent_color ?? '#FACC15',
            'neutral_dark' => $colorScheme->neutral_dark ?? '#000000',
            'neutral_light' => $colorScheme->neutral_light ?? '#FFFFFF',
        ];
    }

    public static function getFormattedNews(Facility $facility)
    {
        return self::getNews($facility)->map(function($item) {
            return [
                'title' => $item->title,
                'desc' => $item->content,
                'date' => $item->published_at ? Carbon::parse($item->published_at)->format('M d') : '',
                'year' => $item->published_at ? Carbon::parse($item->published_at)->format('Y') : '',
                'type' => $item->is_global ? 'global' : 'facility',
                'color' => $item->is_global ? 'bg-green-500' : 'bg-blue-500',
            ];
        })->values()->toArray();
    }
    
    // Add logging to debug the getServices method
    public static function getServices(Facility $facility)
    {
        $globalServices = Service::where('is_global', true)->orderBy('order')->get();
        $facilityServiceIds = DB::table('facility_service')
            ->where('facility_id', $facility->id)
            ->pluck('service_id')
            ->toArray();
        $facilityServices = Service::whereIn('id', $facilityServiceIds)->orderBy('order')->get();

        return $globalServices->concat($facilityServices)->unique('id')->values();
    }

    public static function getNews(Facility $facility)
    {
        $globalNews = News::where('is_global', true)
            ->where('status', true)
            ->orderBy('published_at', 'desc')
            ->get();
        $facilityNews = $facility->news()
            ->where('status', true)
            ->where('is_global', false)
            ->orderBy('published_at', 'desc')
            ->get();
        return $globalNews->concat($facilityNews)->sortByDesc('published_at')->values();
    }

    public static function getFaqs(Facility $facility)
    {
        $globalFaqs = Faq::where('is_global', true)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        $facilityFaqs = $facility->faqs()
            ->where('is_active', true)
            ->where('is_global', false)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
        return $globalFaqs->concat($facilityFaqs)->unique('id')->values();
    }

    public static function getTestimonials(Facility $facility)
    {
        return Testimonial::where('facility_id', $facility->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    /**
     * Get active sections for a facility from its active web_contents record
     */
    public static function getActiveSections(Facility $facility)
    {
        $activeWebContent = $facility->webcontents()->where('is_active', true)->first();

        if (!$activeWebContent) {
            info('No active web content found for facility.', ['facility_id' => $facility->id]);
        } 
        if ($activeWebContent && $activeWebContent->sections) {
            if (is_string($activeWebContent->sections)) {
                return json_decode($activeWebContent->sections, true) ?: [];
            } elseif (is_array($activeWebContent->sections)) {
                return $activeWebContent->sections;
            } elseif ($activeWebContent->sections instanceof \Illuminate\Support\Collection) {
                return $activeWebContent->sections->toArray();
            } else {
                return (array) $activeWebContent->sections;
            }
        }
        return [];
    }

    public static function getColorsFromColorScheme($colorSchemeId)
    {
        $colorScheme = ColorScheme::find($colorSchemeId);
        return [
            'primary' => $colorScheme->primary_color ?? '#059669',
            'secondary' => $colorScheme->secondary_color ?? '#064E3B',
            'accent' => $colorScheme->accent_color ?? '#FACC15',
            'neutral_dark' => $colorScheme->neutral_dark ?? '#000000',
            'neutral_light' => $colorScheme->neutral_light ?? '#FFFFFF',
        ];
    }

    public static function getLegalPageUpdatedDate($legalTitle)
    {
        // Try to get the file modification date for a matching blade view under resources/views

        $specified = [
            'privacy-policy.blade.php',
            'terms-of-service.blade.php',
            'accessibility.blade.php',
            'notice-privacy-practices.blade.php'
        ];

        foreach ($specified as $fname) {
            $file = base_path('resources/views/' . $fname);
            if (file_exists($file)) {
                return date('F j, Y', filemtime($file));
            }
        }
        $base = base_path('resources/views');
        $raw = trim($legalTitle);
        $name = pathinfo($raw, PATHINFO_FILENAME) ?: $raw;
        $nameLower = mb_strtolower($name);

        $candidates = [];

        // direct variants
        $candidates[] = $base . DIRECTORY_SEPARATOR . $nameLower . '.blade.php';
        $candidates[] = $base . DIRECTORY_SEPARATOR . preg_replace('/[\s_]+/', '-', $nameLower) . '.blade.php'; // kebab
        $candidates[] = $base . DIRECTORY_SEPARATOR . preg_replace('/[\s-]+/', '_', $nameLower) . '.blade.php'; // snake

        // treat dots/slashes as subfolders (e.g. "legal.privacy-policy" or "legal/privacy-policy")
        $candidates[] = $base . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $nameLower) . '.blade.php';
        $candidates[] = $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $nameLower) . '.blade.php';

        // common legal filenames to check
        $common = [
            'privacy-policy', 'privacy', 'terms-of-service', 'terms', 'cookie-policy', 'cookies', 'legal'
        ];
        foreach ($common as $c) {
            $candidates[] = $base . DIRECTORY_SEPARATOR . $c . '.blade.php';
            $candidates[] = $base . DIRECTORY_SEPARATOR . 'legal' . DIRECTORY_SEPARATOR . $c . '.blade.php';
        }

        foreach ($candidates as $file) {
            if (file_exists($file)) {
            return date('F j, Y', filemtime($file));
            }
        }

        // if no view file found, fall back to the WebContent record
        $page = \App\Models\WebContent::where('title', $legalTitle)->first();
        return $page ? $page->updated_at->format('F j, Y') : 'N/A';
    }

    public static function getEmailRecipient($facilityId, $category)
    {
        return DB::table('email_recipients')
            ->where('facility_id', $facilityId)
            ->where('category', $category)
            ->value('email');
    }

}
