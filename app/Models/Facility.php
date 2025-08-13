<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
  protected $fillable = [
    'name','slug','logo_url','hero_image_url','headline','subheadline',
    'about_image_url','about_text','address','city','state','beds',
    'ranking_position','ranking_total','ownership_role','phone','email',
    'facebook','twitter','instagram'
  ];

  public function getRouteKeyName() { return 'slug'; }

  public function values() { return $this->hasMany(FacilityValue::class); }
  public function services() { return $this->hasMany(Service::class)->orderBy('order'); }
  public function testimonials() { return $this->hasMany(Testimonial::class); }
  public function galleryImages() { return $this->hasMany(GalleryImage::class); }
}
