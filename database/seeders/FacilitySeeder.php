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
  [
    'name'=>'Almaden Health and Rehabilitation Center',
    'tagline'=>'Compassion and Care You Can Trust.',
    'headline'=>'Healing With Heart in San Jose',
    'subheadline'=>'Providing compassionate rehabilitation and skilled nursing care you can trust.',
    'address'=>'2065 LOS GATOS-ALMADEN ROAD','phone'=>'4083779275','city'=>'SAN JOSE','state'=>'CA','zip'=>'95124','beds'=>77,
    'primary_color'=>'#2563EB','secondary_color'=>'#1E293B','accent_color'=>'#F59E0B',
    'location_map'=> 'https://maps.google.com/maps?q=Almaden+Health+and+Rehabilitation++Center,2065+LOS+GATOS-ALMADEN+ROAD%2CSAN+JOSE%2CCA+95124&output=embed',
    'domain'=>'almadenhealthandrehabilitationcenter.com','years'=>'20'
  ],
  [
    'name'=>'Autumn Hills Health Care Center',
    'tagline'=>'Comfort, Dignity, and Healing Every Day',
    'headline'=>'Care That Feels Like Home',
    'subheadline'=>'Bringing comfort, dignity, and healing to every day in Glendale.',
    'address'=>'430 N. GLENDALE AVE','phone'=>'8182465677','city'=>'GLENDALE','state'=>'CA','zip'=>'91206','beds'=>92,
    'primary_color'=>'#059669','secondary_color'=>'#064E3B','accent_color'=>'#FACC15',
    'location_map'=> 'https://www.google.com/maps?q=Autumn+Hills+Health+Care+Center,430+N.GLENDALE+AVE%2CGLENDALE%2CCA+91206&output=embed',
    'domain'=>'autumnhillshealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Creekside Health Care Center',
    'tagline'=>'Where Community and Care Come Together.',
    'headline'=>'Your Community, Your Care',
    'subheadline'=>'Dedicated to bringing together community support and compassionate nursing.',
    'address'=>'1900 CHURCH LANE','phone'=>'5102355514','city'=>'SAN PABLO','state'=>'CA','zip'=>'94806','beds'=>80,
    'primary_color'=>'#0EA5E9','secondary_color'=>'#0369A1','accent_color'=>'#FBBF24',
    'location_map'=> 'https://maps.google.com/maps?q=Creekside+Healthcare+Center,1900+CHURCH+LANE%2CSAN+PABLO%2CCA+94806&output=embed',
    'domain'=>'creeksidehealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Driftwood Health Care Center - Hayward',
    'tagline'=>'Restoring Health, Rebuilding Lives',
    'headline'=>'Rebuilding Lives, One Step at a Time',
    'subheadline'=>'Offering skilled nursing and rehabilitation designed to restore health and independence.',
    'address'=>'19700 HESPERIAN BOULEVARD','phone'=>'5107852880','city'=>'HAYWARD','state'=>'CA','zip'=>'94541','beds'=>88,
    'primary_color'=>'#10B981','secondary_color'=>'#065F46','accent_color'=>'#FDBA74',
    'location_map'=> 'https://maps.google.com/maps?q=Driftwood+Healthcare+Center+-+Hayward,19700+HESPERIAN+BOULEVARD%2CHAYWARD%2CCA+94541&output=embed',
    'domain'=>'driftwoodhealthcarecenter-hayward.com','years'=>'20'
  ],
  [
    'name'=>'Driftwood Health Care Center - Santa Cruz',
    'tagline'=>'Compassionate Care by the Coastline',
    'headline'=>'Compassion by the Coast',
    'subheadline'=>'Delivering dignified care and trusted support for Santa Cruz families.',
    'address'=>'675 24TH AVENUE','phone'=>'8314756323','city'=>'SANTA CRUZ','state'=>'CA','zip'=>'95062','beds'=>92,
    'primary_color'=>'#22D3EE','secondary_color'=>'#0E7490','accent_color'=>'#F472B6',
    'location_map'=> 'https://maps.google.com/maps?q=Driftwood+Healthcare+Center+-+Santa+Cruz,675+24TH+AVENUE%2CSANTA+CRUZ%2CCA+95062&output=embed',
    'domain'=>'driftwoodhealthcarecenter-santacruz.com','years'=>'20'
  ],
  [
    'name'=>'Fremont Health Care Center',
    'tagline'=>'Dedicated to Excellence in Every Step',
    'headline'=>'Excellence in Every Journey of Care',
    'subheadline'=>'Skilled professionals in Fremont dedicated to recovery and wellness.',
    'address'=>'39022 PRESIDIO WAY','phone'=>'5107923743','city'=>'FREMONT','state'=>'CA','zip'=>'94538','beds'=>115,
    'primary_color'=>'#7C3AED','secondary_color'=>'#4C1D95','accent_color'=>'#60A5FA',
    'location_map'=> 'https://maps.google.com/maps?q=Fremont+Healthcare+Center,39022+PRESIDIO+WAY%2CFREMONT%2CCA+94538&output=embed',
    'domain'=>'fremonthealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Fruitvale Health Care Center',
    'tagline'=>'Serving Oakland Families With Heart',
    'headline'=>'Serving Oakland With Heart',
    'subheadline'=>'Providing quality healthcare for generations, rooted in dignity and compassion.',
    'address'=>'3020 EAST 15TH STREET','phone'=>'5102615613','city'=>'OAKLAND','state'=>'CA','zip'=>'94601','beds'=>140,
    'primary_color'=>'#16A34A','secondary_color'=>'#14532D','accent_color'=>'#F59E0B',
    'location_map'=> 'https://maps.google.com/maps?q=Fruitvale+Healthcare+Center,3020+EAST+15TH+STREET%2COAKLAND%2CCA+94601&output=embed',
    'domain'=>'fruitvalehealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Glendale Transitional Care Center',
    'tagline'=>'Guiding Recovery With Compassion and Expertise',
    'headline'=>'Your Path to Healing in Glendale',
    'subheadline'=>'Providing personalized transitional care and rehabilitation services to restore independence and quality of life.',
    'address'=>'1509 Wilson Terrace, North Tower, 2nd Floor','phone'=>'8184098072','city'=>'GLENDALE','state'=>'CA','zip'=>'91206','beds'=>92,
    'primary_color'=>'#22D3EE','secondary_color'=>'#0E7490','accent_color'=>'#F472B6',
    'location_map'=> 'https://maps.google.com/maps?q=Glendale+Transitional+Care+Center,1509+Wilson+Terrace%2C+North+Tower%2C+2nd+Floor%2CGLENDALE%2CCA+91206&output=embed',
    'domain'=>'glendaletransitionalcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Hayward Hills Health Care Center',
    'tagline'=>'Compassionate Care, Close to Home',
    'headline'=>'Restoring Health in the Heart of Hayward',
    'subheadline'=>'Delivering personalized skilled nursing and rehabilitation services designed to support recovery, dignity, and independence',
    'address'=>'1768 B Street','phone'=>'5105384424','city'=>'HAYWARD','state'=>'CA','zip'=>'94541','beds'=>92,
    'primary_color'=>'#22D3EE','secondary_color'=>'#0E7490','accent_color'=>'#F472B6',
    'location_map'=> 'https://maps.google.com/maps?q=Hayward+Hills+Health+Care+Center,1768+B+Street%2CHAYWARD%2CCA+94541&output=embed',
    'domain'=>'haywardhillshealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Inglewood Health Care Center',
    'tagline'=>'Caring for Life, Caring for You',
    'headline'=>'Caring for Life in Inglewood',
    'subheadline'=>'Delivering trusted long-term care and rehabilitation for your loved ones.',
    'address'=>'100 S. HILLCREST BLVD','phone'=>'3106779114','city'=>'INGLEWOOD','state'=>'CA','zip'=>'90301','beds'=>99,
    'primary_color'=>'#DB2777','secondary_color'=>'#831843','accent_color'=>'#38BDF8',
    'location_map'=> 'https://maps.google.com/maps?q=Inglewood+Health+Care+Center,100+S.+HILLCREST+BLVD%2CINGLEWOOD%2CCA+90301&output=embed',
    'domain'=>'inglewoodhealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'La Crescenta Health Care Center',
    'tagline'=>'Trusted Care, Close to Home',
    'headline'=>'Trusted Care Close to Home',
    'subheadline'=>'Enhancing lives in La Crescenta with compassion, comfort, and dignity.',
    'address'=>'3050 MONTROSE AVE','phone'=>'8189570850','city'=>'LA CRESCENTA','state'=>'CA','zip'=>'91214','beds'=>92,
    'primary_color'=>'#0EA5A4','secondary_color'=>'#134E4A','accent_color'=>'#A78BFA',
    'location_map'=> 'https://maps.google.com/maps?q=La+Crescenta+Healthcare+Center,3050+MONTROSE+AVE%2CLA+CRESCENTA%2CCA+91214&output=embed',
    'domain'=>'lacrescentahealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Monterey Palms Health Care Center',
    'tagline'=>'Healing With Warmth Under the Palms.',
    'headline'=>'Compassionate Care in Palm Desert',
    'subheadline'=>'Offering healing warmth, skilled nursing, and rehabilitation under the palms.',
    'address'=>'44610 MONTEREY AVENUE','phone'=>'7607767700','city'=>'PALM DESERT','state'=>'CA','zip'=>'92260','beds'=>99,
    'primary_color'=>'#F97316','secondary_color'=>'#9A3412','accent_color'=>'#22C55E',
    'location_map'=> 'https://maps.google.com/maps?q=Monterey+Palms+Health+Care+Center,44610+MONTEREY+AVENUE%2CPALM+DESERT%2CCA+92260&output=embed',
    'domain'=>'montereypalmshealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Palm Springs Health Care and Rehabilitation Center',
    'tagline'=>'Where Compassion Meets Quality Care',
    'headline'=>'Where Quality Meets Compassion',
    'subheadline'=>'Delivering personalized care and rehabilitation in the heart of Palm Springs.',
    'address'=>'277 S SUNRISE WAY','phone'=>'7603278541','city'=>'PALM SPRINGS','state'=>'CA','zip'=>'92262','beds'=>99,
    'primary_color'=>'#E11D48','secondary_color'=>'#881337','accent_color'=>'#84CC16',
    'location_map'=> 'https://maps.google.com/maps?q=Palm+Springs+Healthcare++and++Rehabilitation+Center,277+S+SUNRISE+WAY%2CPALM+SPRINGS%2CCA+92262&output=embed',
    'domain'=>'palmspringshealthandrehabilitationcenter.com','years'=>'20'
  ],
  [
    'name'=>'Pine Ridge Health Care Center',
    'tagline'=>'Strong Roots in Compassionate Care',
    'headline'=>'Strong Roots, Compassionate Care',
    'subheadline'=>'Supporting health, dignity, and independence with every resident we serve.',
    'address'=>'45 PROFESSIONAL CENTER PKWY','phone'=>'4154793610','city'=>'SAN RAFAEL','state'=>'CA','zip'=>'94903','beds'=>101,
    'primary_color'=>'#2563EB','secondary_color'=>'#1D4ED8','accent_color'=>'#FDE047',
    'location_map'=> 'https://maps.google.com/maps?q=Pine+Ridge+Care+Center,45+PROFESSIONAL+CENTER+PKWY%2CSAN+RAFAEL%2CCA+94903&output=embed',
    'domain'=>'pineridgecarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Santa Monica Health Care Center',
    'tagline'=>'Nurturing Health, Enriching Lives.',
    'headline'=>'Dedicated to Santa Monica Families',
    'subheadline'=>'Providing nurturing care and enriching lives with dignity and compassion.',
    'address'=>'1320 20TH STREET','phone'=>'3102552800','city'=>'SANTA MONICA','state'=>'CA','zip'=>'90404','beds'=>59,
    'primary_color'=>'#64748B','secondary_color'=>'#0F172A','accent_color'=>'#F59E0B',
    'location_map'=> 'https://maps.google.com/maps?q=Santa+Monica+Health+Care+Center,1320+20TH+STREET%2CSANTA+MONICA%2CCA+90404&output=embed',
    'domain'=>'santamonicahealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Skyline Health Care Center',
    'tagline'=>'Compassion Rising Above the Rest',
    'headline'=>'Rising With Compassionate Care',
    'subheadline'=>'Delivering skilled nursing and rehabilitation that elevates quality of life.',
    'address'=>'2065 FOREST AVENUE','phone'=>'4082802500','city'=>'SAN JOSE','state'=>'CA','zip'=>'95128','beds'=>253,
    'primary_color'=>'#0EA5E9','secondary_color'=>'#155E75','accent_color'=>'#F59E0B',
    'location_map'=> 'https://maps.google.com/maps?q=Skyline+Healthcare+Center+-+San+Jose,2065+FOREST+AVENUE%2CSAN+JOSE%2CCA+95128&output=embed',
    'domain'=>'skylinehealthcarecenter-sanjose.com','years'=>'20'
  ],
  [
    'name'=>'Vale Health Care Center',
    'tagline'=>'A Legacy of Care, A Promise of Hope',
    'headline'=>'A Legacy of Care in San Pablo',
    'subheadline'=>'Providing skilled nursing and rehabilitation built on trust, compassion, and excellence.',
    'address'=>'13484 SAN PABLO AVENUE','phone'=>'5102325945','city'=>'SAN PABLO','state'=>'CA','zip'=>'94806','beds'=>202,
    'primary_color'=>'#06B6D4','secondary_color'=>'#155E75','accent_color'=>'#F59E0B',
    'location_map'=> 'https://maps.google.com/maps?q=Vale+Healthcare+Center,13484+SAN+PABLO+AVENUE%2CSAN+PABLO%2CCA+94806&output=embed',
    'domain'=>'valehealthcarecenter.com','years'=>'20'
  ],
  [
    'name'=>'Village Square Health Care Center',
    'tagline'=>'Caring Together, Growing Stronger',
    'headline'=>'Caring Together, Growing Stronger',
    'subheadline'=>'Supporting families with skilled care that fosters strength and independence.',
    'address'=>'1586 W. SAN MARCOS BLVD','phone'=>'7604712986','city'=>'SAN MARCOS','state'=>'CA','zip'=>'92078','beds'=>118,
    'primary_color'=>'#15803D','secondary_color'=>'#052E16','accent_color'=>'#84CC16',
    'location_map'=> 'https://maps.google.com/maps?q=Village+Square+Healthcare+Center,1586+W.+SAN+MARCOS+BLVD%2CSAN+MARCOS%2CCA+92078&output=embed',
    'domain'=>'villagesquarehealthcarecenter.com','years'=>'20'
  ],
];

    foreach ($items as $i) {
      $facility = Facility::create([
        'name' => $i['name'],
        'tagline' => $i['tagline'],
        'slug' => Str::slug($i['name']),
        'logo_url' => 'https://images.unsplash.com/photo-1588776814546-84ef50a2f7b8?auto=format&fit=crop&w=200&q=80',
        'hero_image_url' => $heroImages[array_rand($heroImages)],
        'headline' =>  $i['headline'],
        'subheadline' =>  $i['subheadline'],
        'about_image_url' => $aboutImages[array_rand($aboutImages)],
        'about_text' => $i['name'].' provides personalized care and support for seniors, ensuring comfort, dignity, and quality of life.',
        'address' => ucwords(strtolower($i['address'])),
        'city' => ucwords(strtolower($i['city'])),
        'state' => $i['state'],
        'zip' => $i['zip'],
        'beds' => $i['beds'],
        'domain' => $i['domain'],
        'phone' => $i['phone'],
        'email' => 'info@example.com',
        'facebook' => 'https://facebook.com',
        'twitter' => 'https://twitter.com',
        'instagram' => 'https://instagram.com',
        'primary_color'   => $i['primary_color'],
        'secondary_color' => $i['secondary_color'],
        'accent_color'    => $i['accent_color'],
        'location_map'    => $i['location_map'],
        'years'    => $i['years'],
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
