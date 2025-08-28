<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
  protected $fillable = [
    'name','slug','tagline','logo_url','hero_image_url','headline','subheadline',
    'about_image_url','about_text','address','city','state','beds',
    'ranking_position','ranking_total','ownership_role','phone','email',
    'facebook','twitter','instagram','primary_color', 'secondary_color', 'accent_color',
    'domain', 'subdomain', 'is_active', 'settings', 'layout_template', 'layout_config',
    'location_map'
  ];

  protected $casts = [
    'settings' => 'array',
    'layout_config' => 'array',
    'is_active' => 'boolean'
  ];

  public function getRouteKeyName() { return 'slug'; }

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

  public function getSetting($key, $default = null)
  {
    $settings = $this->settings ?? [];
    return data_get($settings, $key, $default);
  }

  public function setSetting($key, $value)
  {
    $settings = $this->settings ?? [];
    data_set($settings, $key, $value);
    $this->update(['settings' => $settings]);
  }

  // Existing relationships
  public function values() { return $this->hasMany(FacilityValue::class); }
  public function services() { return $this->hasMany(Service::class)->orderBy('order'); }
  public function testimonials() { return $this->hasMany(Testimonial::class); }
  public function galleryImages() { return $this->hasMany(GalleryImage::class); }
}
