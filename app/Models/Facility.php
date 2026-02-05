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
}
