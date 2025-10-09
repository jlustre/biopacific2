<?php

namespace App\Helpers;

use App\Models\Facility;
use App\Models\Service;
use App\Models\News;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\ColorScheme;
use Illuminate\Support\Facades\DB;
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
            'accent' => $colorScheme->accent_color ?? '#FACC15'
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
    
    public static function getServices(Facility $facility)
    {
        $globalServices = Service::where('is_global', true)->orderBy('title')->get();
        $facilityServiceIds = DB::table('facility_service')
            ->where('facility_id', $facility->id)
            ->pluck('service_id')
            ->toArray();
        $facilityServices = Service::whereIn('id', $facilityServiceIds)->orderBy('title')->get();
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
}
