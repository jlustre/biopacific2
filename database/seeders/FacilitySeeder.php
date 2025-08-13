<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Facility;
use App\Models\FacilityValue;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\GalleryImage;

class FacilitySeeder extends Seeder
{
  public function run(): void
  {
    $heroImages = [
      'https://images.unsplash.com/photo-1601924928350-3a3a5de9c6e1?auto=format&fit=crop&w=1400&q=80',
      'https://images.unsplash.com/photo-1588776814546-84ef50a2f7b8?auto=format&fit=crop&w=1400&q=80',
      'https://images.unsplash.com/photo-1584556812952-d3f61d44a6f0?auto=format&fit=crop&w=1400&q=80',
    ];
    $aboutImages = [
      'https://images.unsplash.com/photo-1603394727310-9f53e77a1f8d?auto=format&fit=crop&w=1400&q=80',
      'https://images.unsplash.com/photo-1601979031896-4c6642a3c45c?auto=format&fit=crop&w=1400&q=80',
    ];
    $galleryImages = [
      'https://images.unsplash.com/photo-1587517452058-6c7fef7f45b1?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1593032465174-7abf6b5a1b0c?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1576765607924-5e5b5a2f5d8b?auto=format&fit=crop&w=800&q=80',
    ];

    $services = [
      ['title'=>'24/7 Nursing Care','description'=>'Round-the-clock skilled nursing by licensed staff.','icon'=>'<svg class="w-6 h-6" viewBox="0 0 24 24"></svg>'],
      ['title'=>'Rehabilitation','description'=>'Physical, occupational, and speech therapy.','icon'=>'<svg class="w-6 h-6" viewBox="0 0 24 24"></svg>'],
      ['title'=>'Memory Care','description'=>'Specialized care for dementia and Alzheimer\'s.','icon'=>'<svg class="w-6 h-6" viewBox="0 0 24 24"></svg>'],
      ['title'=>'Hospice Care','description'=>'Comfort-focused end-of-life support.','icon'=>'<svg class="w-6 h-6" viewBox="0 0 24 24"></svg>'],
    ];

    $items = [
      ['name'=>'Almaden Health and Rehabilitation Center','address'=>'2065 LOS GATOS-ALMADEN ROAD, SAN JOSE, CA 95124','phone'=>'4083779275','city'=>'SAN JOSE','state'=>'CA','beds'=>77,'ranking_position'=>48,'ranking_total'=>72,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Autumn Hills Health Care Center','address'=>'430 N. GLENDALE AVE, GLENDALE, CA 91206','phone'=>'8182465677','city'=>'GLENDALE','state'=>'CA','beds'=>92,'ranking_position'=>199,'ranking_total'=>365,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Creekside Healthcare Center','address'=>'1900 CHURCH LANE, SAN PABLO, CA 94806','phone'=>'5102355514','city'=>'SAN PABLO','state'=>'CA','beds'=>80,'ranking_position'=>62,'ranking_total'=>135,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Driftwood Healthcare Center - Hayward','address'=>'19700 HESPERIAN BOULEVARD, HAYWARD, CA 94541','phone'=>'5107852880','city'=>'HAYWARD','state'=>'CA','beds'=>88,'ranking_position'=>114,'ranking_total'=>169,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Driftwood Healthcare Center - Santa Cruz','address'=>'675 24TH AVENUE, SANTA CRUZ, CA 95062','phone'=>'8314756323','city'=>'SANTA CRUZ','state'=>'CA','beds'=>92,'ranking_position'=>39,'ranking_total'=>42,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Fremont Healthcare Center','address'=>'39022 PRESIDIO WAY, FREMONT, CA 94538','phone'=>'5107923743','city'=>'FREMONT','state'=>'CA','beds'=>115,'ranking_position'=>46,'ranking_total'=>146,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Fruitvale Healthcare Center','address'=>'3020 EAST 15TH STREET, OAKLAND, CA 94601','phone'=>'5102615613','city'=>'OAKLAND','state'=>'CA','beds'=>140,'ranking_position'=>44,'ranking_total'=>148,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Inglewood Health Care Center','address'=>'100 S. HILLCREST BLVD, INGLEWOOD, CA 90301','phone'=>'3106779114','city'=>'INGLEWOOD','state'=>'CA','beds'=>99,'ranking_position'=>235,'ranking_total'=>382,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'La Crescenta Healthcare Center','address'=>'3050 MONTROSE AVE, LA CRESCENTA, CA 91214','phone'=>'8189570850','city'=>'LA CRESCENTA','state'=>'CA','beds'=>92,'ranking_position'=>150,'ranking_total'=>323,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Monterey Palms Health Care Center','address'=>'44610 MONTEREY AVENUE, PALM DESERT, CA 92260','phone'=>'7607767700','city'=>'PALM DESERT','state'=>'CA','beds'=>99,'ranking_position'=>3,'ranking_total'=>11,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Palm Springs Healthcare and Rehabilitation Center','address'=>'277 S SUNRISE WAY, PALM SPRINGS, CA 92262','phone'=>'7603278541','city'=>'PALM SPRINGS','state'=>'CA','beds'=>99,'ranking_position'=>8,'ranking_total'=>21,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Pine Ridge Care Center','address'=>'45 PROFESSIONAL CENTER PKWY, SAN RAFAEL, CA 94903','phone'=>'4154793610','city'=>'SAN RAFAEL','state'=>'CA','beds'=>101,'ranking_position'=>54,'ranking_total'=>105,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Santa Monica Health Care Center','address'=>'1338 20TH STREET, SANTA MONICA, CA 90404','phone'=>'3102552800','city'=>'SANTA MONICA','state'=>'CA','beds'=>59,'ranking_position'=>16,'ranking_total'=>315,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Santa Monica Rehabilitation Center','address'=>'1338 20TH STREET, SANTA MONICA, CA 90404','phone'=>'3102552800','city'=>'SANTA MONICA','state'=>'CA','beds'=>144,'ranking_position'=>303,'ranking_total'=>315,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Skyline Healthcare Center - San Jose','address'=>'2065 FOREST AVENUE, SAN , CA 95128','phone'=>'4082802500','city'=>'SAN JOSE','state'=>'CA','beds'=>253,'ranking_position'=>91,'ranking_total'=>91,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Vale Healthcare Center','address'=>'13484 SAN PABLO AVENUE, SAN PABLO, CA 94806','phone'=>'5102325945','city'=>'SAN PABLO','state'=>'CA','beds'=>202,'ranking_position'=>80,'ranking_total'=>135,'ownership_role'=>'5% or greater indirect ownership interest'],
      ['name'=>'Village Square Healthcare Center','address'=>'1586 W. SAN MARCOS BLVD, SAN MARCOS, CA 92078','phone'=>'7604712986','city'=>'SAN MARCOS','state'=>'CA','beds'=>118,'ranking_position'=>35,'ranking_total'=>55,'ownership_role'=>'5% or greater indirect ownership interest'],
    ];

    foreach ($items as $i) {
      $facility = Facility::create([
        'name' => $i['name'],
        'slug' => Str::slug($i['name']),
        'logo_url' => 'https://images.unsplash.com/photo-1588776814546-84ef50a2f7b8?auto=format&fit=crop&w=200&q=80',
        'hero_image_url' => $heroImages[array_rand($heroImages)],
        'headline' => 'Compassionate Care in a Comfortable Setting',
        'subheadline' => 'Where comfort meets exceptional care.',
        'about_image_url' => $aboutImages[array_rand($aboutImages)],
        'about_text' => $i['name'].' provides personalized care and support for seniors, ensuring comfort, dignity, and quality of life.',
        'address' => ucwords(strtolower($i['address'])),
        'city' => ucwords(strtolower($i['city'])),
        'state' => $i['state'],
        'beds' => $i['beds'],
        'ranking_position' => $i['ranking_position'],
        'ranking_total' => $i['ranking_total'],
        'ownership_role' => $i['ownership_role'],
        'phone' => $i['phone'],
        'email' => 'info@example.com',
        'facebook' => 'https://facebook.com',
        'twitter' => 'https://twitter.com',
        'instagram' => 'https://instagram.com',
      ]);

      foreach (['Compassion','Integrity','Respect','Excellence'] as $v) {
        FacilityValue::create(['facility_id'=>$facility->id,'value'=>$v]);
      }
      foreach ($services as $svc) {
        Service::create($svc + ['facility_id'=>$facility->id]);
      }
      for ($t=0;$t<3;$t++) {
        Testimonial::create([
          'facility_id'=>$facility->id,
          'name'=> fake()->name(),
          'quote'=> fake()->randomElement([
            'The staff here are caring and professional.',
            'Feels like home, with genuine compassion.',
            'They go above and beyond every day.',
            'Our family is grateful for the amazing care.',
            'The environment is warm and welcoming.',
            'Residents feel truly valued here.',
          ]),
          'photo_url'=>'https://randomuser.me/api/portraits/'.(rand(0,1)?'women':'men').'/'.rand(10,90).'.jpg',
        ]);
      }
      foreach ($galleryImages as $img) {
        GalleryImage::create(['facility_id'=>$facility->id,'thumbnail_url'=>$img]);
      }
    }
  }
}
