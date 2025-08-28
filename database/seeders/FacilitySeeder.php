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
      ['name'=>'Almaden Health and Rehabilitation Center','address'=>'2065 LOS GATOS-ALMADEN ROAD, SAN JOSE, CA 95124','phone'=>'4083779275','city'=>'SAN JOSE','state'=>'CA','beds'=>77,'ranking_position'=>48,'ranking_total'=>72,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#2563EB','secondary_color'=>'#1E293B','accent_color'=>'#F59E0B', 'location_map'=> 'https://maps.google.com/maps?q=Almaden+Health+and+Rehabilitation++Center,2065+LOS+GATOS-ALMADEN+ROAD%2CSAN+JOSE%2CCA+95124&output=embed','domain'=>'almadenhealthandrehabilitationcenter.com'],
      ['name'=>'Autumn Hills Health Care Center','address'=>'430 N. GLENDALE AVE, GLENDALE, CA 91206','phone'=>'8182465677','city'=>'GLENDALE','state'=>'CA','beds'=>92,'ranking_position'=>199,'ranking_total'=>365,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#059669','secondary_color'=>'#064E3B','accent_color'=>'#FACC15', 'location_map'=> 'https://www.google.com/maps?q=Autumn+Hills+Health+Care+Center,430+N.GLENDALE+AVE%2CGLENDALE%2CCA+91206&output=embed','domain'=>'autumnhillshealthcarecenter.com'],     
      ['name'=>'Creekside Healthcare Center','address'=>'1900 CHURCH LANE, SAN PABLO, CA 94806','phone'=>'5102355514','city'=>'SAN PABLO','state'=>'CA','beds'=>80,'ranking_position'=>62,'ranking_total'=>135,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#0EA5E9','secondary_color'=>'#0369A1','accent_color'=>'#FBBF24', 'location_map'=> 'https://maps.google.com/maps?q=Creekside+Healthcare+Center,1900+CHURCH+LANE%2CSAN+PABLO%2CCA+94806&output=embed','domain'=>'creeksidehealthcarecenter.com'],         
      ['name'=>'Driftwood Healthcare Center - Hayward','address'=>'19700 HESPERIAN BOULEVARD, HAYWARD, CA 94541','phone'=>'5107852880','city'=>'HAYWARD','state'=>'CA','beds'=>88,'ranking_position'=>114,'ranking_total'=>169,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#10B981','secondary_color'=>'#065F46','accent_color'=>'#FDBA74', 'location_map'=> 'https://maps.google.com/maps?q=Driftwood+Healthcare+Center+-+Hayward,19700+HESPERIAN+BOULEVARD%2CHAYWARD%2CCA+94541&output=embed','domain'=>'driftwoodhealthcarecenter-hayward.com'],     
      ['name'=>'Driftwood Healthcare Center - Santa Cruz','address'=>'675 24TH AVENUE, SANTA CRUZ, CA 95062','phone'=>'8314756323','city'=>'SANTA CRUZ','state'=>'CA','beds'=>92,'ranking_position'=>39,'ranking_total'=>42,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#22D3EE','secondary_color'=>'#0E7490','accent_color'=>'#F472B6', 'location_map'=> 'https://maps.google.com/maps?q=Driftwood+Healthcare+Center+-+Santa+Cruz,675+24TH+AVENUE%2CSANTA+CRUZ%2CCA+95062&output=embed','domain'=>'driftwoodhealthcarecenter-santacruz.com'],     
      ['name'=>'Fremont Healthcare Center','address'=>'39022 PRESIDIO WAY, FREMONT, CA 94538','phone'=>'5107923743','city'=>'FREMONT','state'=>'CA','beds'=>115,'ranking_position'=>46,'ranking_total'=>146,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#7C3AED','secondary_color'=>'#4C1D95','accent_color'=>'#60A5FA', 'location_map'=> 'https://maps.google.com/maps?q=Fremont+Healthcare+Center,39022+PRESIDIO+WAY%2CFREMONT%2CCA+94538&output=embed','domain'=>'fremonthealthcarecenter.com'],     
      ['name'=>'Fruitvale Healthcare Center','address'=>'3020 EAST 15TH STREET, OAKLAND, CA 94601','phone'=>'5102615613','city'=>'OAKLAND','state'=>'CA','beds'=>140,'ranking_position'=>44,'ranking_total'=>148,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#16A34A','secondary_color'=>'#14532D','accent_color'=>'#F59E0B', 'location_map'=> 'https://maps.google.com/maps?q=Fruitvale+Healthcare+Center,3020+EAST+15TH+STREET%2COAKLAND%2CCA+94601&output=embed','domain'=>'hcai.ca.gov/facility/fruitvale-healthcare-center'],     
      ['name'=>'Inglewood Health Care Center','address'=>'100 S. HILLCREST BLVD, INGLEWOOD, CA 90301','phone'=>'3106779114','city'=>'INGLEWOOD','state'=>'CA','beds'=>99,'ranking_position'=>235,'ranking_total'=>382,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#DB2777','secondary_color'=>'#831843','accent_color'=>'#38BDF8', 'location_map'=> 'https://maps.google.com/maps?q=Inglewood+Health+Care+Center,100+S.+HILLCREST+BLVD%2CINGLEWOOD%2CCA+90301&output=embed','domain'=>'inglewoodhealthcarecenter.com'],     
      ['name'=>'La Crescenta Healthcare Center','address'=>'3050 MONTROSE AVE, LA CRESCENTA, CA 91214','phone'=>'8189570850','city'=>'LA CRESCENTA','state'=>'CA','beds'=>92,'ranking_position'=>150,'ranking_total'=>323,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#0EA5A4','secondary_color'=>'#134E4A','accent_color'=>'#A78BFA', 'location_map'=> 'https://maps.google.com/maps?q=La+Crescenta+Healthcare+Center,3050+MONTROSE+AVE%2CLA+CRESCENTA%2CCA+91214&output=embed','domain'=>'lacrescentahealthcarecenter.com'],     
      ['name'=>'Monterey Palms Health Care Center','address'=>'44610 MONTEREY AVENUE, PALM DESERT, CA 92260','phone'=>'7607767700','city'=>'PALM DESERT','state'=>'CA','beds'=>99,'ranking_position'=>3,'ranking_total'=>11,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#F97316','secondary_color'=>'#9A3412','accent_color'=>'#22C55E', 'location_map'=> 'https://maps.google.com/maps?q=Monterey+Palms+Health+Care+Center,44610+MONTEREY+AVENUE%2CPALM+DESERT%2CCA+92260&output=embed','domain'=>'montereypalmshealthcarecenter.com'],     
      ['name'=>'Palm Springs Healthcare and Rehabilitation Center','address'=>'277 S SUNRISE WAY, PALM SPRINGS, CA 92262','phone'=>'7603278541','city'=>'PALM SPRINGS','state'=>'CA','beds'=>99,'ranking_position'=>8,'ranking_total'=>21,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#E11D48','secondary_color'=>'#881337','accent_color'=>'#84CC16', 'location_map'=> 'https://maps.google.com/maps?q=Palm+Springs+Healthcare++and++Rehabilitation+Center,277+S+SUNRISE+WAY%2CPALM+SPRINGS%2CCA+92262&output=embed','domain'=>'palmspringshealthandrehabilitationcenter.com'],     
      ['name'=>'Pine Ridge Care Center','address'=>'45 PROFESSIONAL CENTER PKWY, SAN RAFAEL, CA 94903','phone'=>'4154793610','city'=>'SAN RAFAEL','state'=>'CA','beds'=>101,'ranking_position'=>54,'ranking_total'=>105,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#2563EB','secondary_color'=>'#1D4ED8','accent_color'=>'#FDE047', 'location_map'=> 'https://maps.google.com/maps?q=Pine+Ridge+Care+Center,45+PROFESSIONAL+CENTER+PKWY%2CSAN+RAFAEL%2CCA+94903&output=embed','domain'=>'pineridgecarecenter.com'],     
      ['name'=>'Santa Monica Health Care Center','address'=>'1338 20TH STREET, SANTA MONICA, CA 90404','phone'=>'3102552800','city'=>'SANTA MONICA','state'=>'CA','beds'=>59,'ranking_position'=>16,'ranking_total'=>315,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#64748B','secondary_color'=>'#0F172A','accent_color'=>'#F59E0B', 'location_map'=> 'https://maps.google.com/maps?q=Santa+Monica+Health+Care+Center,1320+20TH+STREET%2CSANTA+MONICA%2CCA+90404&output=embed','domain'=>'santamonicahealthcarecenter.com'],     
      ['name'=>'Santa Monica Rehabilitation Center','address'=>'1338 20TH STREET, SANTA MONICA, CA 90404','phone'=>'3102552800','city'=>'SANTA MONICA','state'=>'CA','beds'=>144,'ranking_position'=>303,'ranking_total'=>315,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#9333EA','secondary_color'=>'#581C87','accent_color'=>'#34D399', 'location_map'=> 'https://maps.google.com/maps?q=Santa+Monica+Health+Care+Center,1320+20TH+STREET%2CSANTA+MONICA%2CCA+90404&output=embed','domain'=>'hcai.ca.gov/facility/the-rehabilitation-center-of-santa-monica'],     
      ['name'=>'Skyline Healthcare Center - San Jose','address'=>'2065 FOREST AVENUE, SAN , CA 95128','phone'=>'4082802500','city'=>'SAN JOSE','state'=>'CA','beds'=>253,'ranking_position'=>91,'ranking_total'=>91,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#0EA5E9','secondary_color'=>'#155E75','accent_color'=>'#F59E0B', 'location_map'=> 'https://maps.google.com/maps?q=Skyline+Healthcare+Center+-+San+Jose,2065+FOREST+AVENUE%2CSAN+JOSE%2CCA+95128&output=embed','domain'=>'skylinehealthcarecenter-sanjose.com'],     
      ['name'=>'Vale Healthcare Center','address'=>'13484 SAN PABLO AVENUE, SAN PABLO, CA 94806','phone'=>'5102325945','city'=>'SAN PABLO','state'=>'CA','beds'=>202,'ranking_position'=>80,'ranking_total'=>135,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#06B6D4','secondary_color'=>'#155E75','accent_color'=>'#F59E0B', 'location_map'=> 'https://maps.google.com/maps?q=Vale+Healthcare+Center,13484+SAN+PABLO+AVENUE%2CSAN+PABLO%2CCA+94806&output=embed','domain'=>'hcai.ca.gov/facility/vale-healthcare-center'],     
      ['name'=>'Village Square Healthcare Center','address'=>'1586 W. SAN MARCOS BLVD, SAN MARCOS, CA 92078','phone'=>'7604712986','city'=>'SAN MARCOS','state'=>'CA','beds'=>118,'ranking_position'=>35,'ranking_total'=>55,'ownership_role'=>'5% or greater indirect ownership interest','primary_color'=>'#15803D','secondary_color'=>'#052E16','accent_color'=>'#84CC16', 'location_map'=> 'https://maps.google.com/maps?q=Village+Square+Healthcare+Center,1586+W.+SAN+MARCOS+BLVD%2CSAN+MARCOS%2CCA+92078&output=embed','domain'=>'healthy.kaiserpermanente.org/southern-california/facilities/village-square-healthcare-center-328603'],
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
        'domain' => $i['domain'],
        'ranking_position' => $i['ranking_position'],
        'ranking_total' => $i['ranking_total'],
        'ownership_role' => $i['ownership_role'],
        'phone' => $i['phone'],
        'email' => 'info@example.com',
        'facebook' => 'https://facebook.com',
        'twitter' => 'https://twitter.com',
        'instagram' => 'https://instagram.com',
        'primary_color'   => $i['primary_color'],
        'secondary_color' => $i['secondary_color'],
        'accent_color'    => $i['accent_color'],
        'location_map'    => $i['location_map'],
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
