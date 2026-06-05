<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Content;

class Facility extends Model
{
  // Job Openings relationship
  public function jobOpenings()
  {
    return $this->hasMany(JobOpening::class);
  }
  // FAQs relationship (many-to-many)
  public function faqs()
  {
    return $this->belongsToMany(\App\Models\Faq::class, 'facility_faq', 'facility_id', 'faq_id');
  }
  public function colorScheme()
  {
    return $this->belongsTo(ColorScheme::class, 'color_scheme_id');
  }

  // News relationship (many-to-many)
  public function news()
  {
    return $this->belongsToMany(News::class, 'facility_news');
  }

  public function services()
  {
    return $this->belongsToMany(Service::class, 'facility_service');
  }

  public function __toString()
  {
    return (string) $this->name;
  }

  public static function corporateSiteSlug(): string
  {
    return (string) config('member-portal.corporate_facility_slug', 'bio-pacific-corporate');
  }

  public static function corporateSite(): ?self
  {
    return static::query()->where('slug', static::corporateSiteSlug())->first();
  }

  public function isCorporatePublicSite(): bool
  {
    $settings = $this->settings;

    if (is_array($settings) && !empty($settings['is_corporate_public_site'])) {
      return true;
    }

    return $this->slug === static::corporateSiteSlug();
  }

  /**
   * Same-origin path for public header CTAs (Login, etc.).
   * Avoids broken links when APP_URL still points at a local/dev host on staging.
   */
  public static function publicCtaUrlForRoute(string $routeName = 'login'): string
  {
    $fallback = '/login';

    if (!\Illuminate\Support\Facades\Route::has($routeName)) {
      return $fallback;
    }

    $parts = parse_url(route($routeName));
    $path = $parts['path'] ?? $fallback;

    if (!empty($parts['query'])) {
      $path .= '?' . $parts['query'];
    }

    return $path !== '' ? $path : $fallback;
  }

  /**
   * @return array{label: string, url: string}|null
   */
  public function publicHeaderCta(): ?array
  {
    $settings = $this->settings;

    if (!is_array($settings)) {
      return $this->isCorporatePublicSite()
        ? static::publicHeaderCtaForAuthState()
        : null;
    }

    $cta = $settings['public_header_cta'] ?? null;

    if (is_array($cta) && !empty($cta['label'])) {
      $routeName = $cta['route'] ?? 'login';

      if (auth()->check() && $routeName === 'login') {
        return static::publicHeaderCtaForAuthState();
      }

      return [
        'label' => (string) $cta['label'],
        'url' => static::publicCtaUrlForRoute($routeName),
      ];
    }

    return $this->isCorporatePublicSite()
      ? static::publicHeaderCtaForAuthState()
      : null;
  }

  /**
   * Login for guests; Dashboard for authenticated users on the public site header.
   *
   * @return array{label: string, url: string}
   */
  public static function publicHeaderCtaForAuthState(): array
  {
    if (!auth()->check()) {
      return ['label' => 'Login', 'url' => static::publicCtaUrlForRoute('login')];
    }

    $user = auth()->user();
    $routeName = ($user && method_exists($user, 'hasRole') && $user->hasRole('admin'))
      ? 'admin.dashboard.index'
      : 'dashboard.index';

    return ['label' => 'Dashboard', 'url' => static::publicCtaUrlForRoute($routeName)];
  }

  public function getMeta(string $key, ?string $section = null): ?string
  {
    $sources = [];
    $settings = $this->settings ?? null;

    if (is_string($settings)) {
      $decoded = json_decode($settings, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $settings = $decoded;
      }
    }

    if (is_array($settings)) {
      $sources[] = $settings['meta'] ?? $settings;
    }

    $sources[] = $this->getAttribute($key);

    foreach ($sources as $source) {
      if (is_string($source)) {
        $decoded = json_decode($source, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          $source = $decoded;
        } else {
          return $source;
        }
      }

      if (!is_array($source)) {
        continue;
      }

      if ($section !== null) {
        if (isset($source[$section]) && is_array($source[$section]) && array_key_exists($key, $source[$section])) {
          $value = $source[$section][$key];
          return is_string($value) ? $value : null;
        }

        if (isset($source[$section]) && is_string($source[$section])) {
          return $source[$section];
        }
      }

      if (array_key_exists($key, $source)) {
        $value = $source[$key];
        return is_string($value) ? $value : null;
      }
    }

    return null;
  }
    protected $fillable = [
      'name','slug','tagline','logo_url','hero_image_url','headline','subheadline',
      'about_image_url','about_text','address','city','state','zip','beds', 'years',
      'phone','email','facebook','twitter','instagram','domain', 'subdomain', 'is_active', 'settings', 'layout_template', 
      'layout_config','location_map', 'facility_image', 'hours', 'hero_video_id',
      'hipaa_flags', 'npp_url', 'color_scheme_id', 'facility_number', 
      'legal_name', 'administrator', 'don', 'dsd', 'staffer', 'region',
  // meta_title removed; now stored in meta JSON
      // Shutdown fields
      'is_shutdown', 'shutdown_message', 'shutdown_eta'
    ];

  protected $casts = [
    'settings' => 'array',
    'layout_config' => 'array',
    'is_active' => 'boolean',
    'hipaa_flags' => 'array',
  ];

  // Allow both id and slug for route model binding
  public function getRouteKeyName()
  {
    $value = request()->route('facility');
    if (is_numeric($value)) {
      return 'id';
    }
    return 'slug';
  }

  // Multi-tenant methods
  public static function findByDomain($domain)
  {
    return static::where('domain', $domain)
                ->where('is_active', true)
                ->first();
  }

  public function getLayoutConfig($section = null)
  {
    $config = $this->layout_config ?? [];

    if ($section) {
      return $config[$section] ?? [];
    }

    return $config;
  }

  public function setLayoutConfig($section, $config)
  {
    $layoutConfig = $this->layout_config ?? [];
    $layoutConfig[$section] = $config;
    $this->update(['layout_config' => $layoutConfig]);
  }

  public function webContents()
{
    return $this->hasMany(WebContent::class);
}

  // Existing relationships
  public function values() { return $this->hasMany(FacilityValue::class); }
  public function testimonials() { return $this->hasMany(Testimonial::class); }
  public function galleryImages() { return $this->hasMany(GalleryImage::class); }

  public function leadershipAssignments()
  {
    return $this->hasMany(FacilityLeadershipAssignment::class)->orderBy('sort_order')->orderBy('id');
  }
}
