<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $facilityIds = \App\Models\Facility::pluck('id');
        $titleStories = [
            'A Journey of Healing' => 'After a difficult diagnosis, I found hope and strength at this facility. The dedicated staff guided me every step of the way, making my recovery a truly transformative journey.',
            'Exceptional Care Experience' => 'From the moment I arrived, I was treated with kindness and professionalism. Every member of the team went above and beyond to ensure my comfort and well-being.',
            'A Family’s Gratitude' => 'Our family is forever grateful for the compassion and support we received. The care provided here gave us peace of mind and helped our loved one thrive.',
            'Regaining Independence' => 'With the help of skilled therapists and encouraging staff, I regained my independence and confidence. I never imagined I could recover so fully.',
            'Above and Beyond' => 'The staff consistently exceeded our expectations, providing attentive care and thoughtful gestures that made all the difference during a challenging time.',
            'A Place Like Home' => 'This facility truly feels like home. The warm environment and friendly faces made my stay comfortable and reassuring.',
            'Trusted Support' => 'Whenever we had questions or concerns, the team was there to help. Their expertise and empathy made us feel supported every day.',
            'Peace of Mind' => 'Knowing my loved one was in such capable hands gave our family peace of mind. The staff’s dedication is unmatched.',
            'A New Beginning' => 'My experience here marked a new beginning in my life. The encouragement and care I received inspired me to look forward with hope.',
            'Outstanding Team' => 'The entire team works together seamlessly to provide exceptional care. Their commitment to residents is evident in everything they do.',
        ];
        $titleQuotes = [
            'A Journey of Healing' => 'The staff guided me every step of the way, making my recovery truly transformative.',
            'Exceptional Care Experience' => 'I was treated with kindness and professionalism from the moment I arrived.',
            'A Family’s Gratitude' => 'The compassion and support we received gave our family peace of mind.',
            'Regaining Independence' => 'With skilled therapists, I regained my independence and confidence.',
            'Above and Beyond' => 'The staff exceeded our expectations with attentive care and thoughtful gestures.',
            'A Place Like Home' => 'This facility truly feels like home, with a warm environment and friendly faces.',
            'Trusted Support' => 'The team’s expertise and empathy made us feel supported every day.',
            'Peace of Mind' => 'Knowing my loved one was in capable hands gave us peace of mind.',
            'A New Beginning' => 'The encouragement and care I received inspired me to look forward with hope.',
            'Outstanding Team' => 'The team provides exceptional care and is committed to every resident.',
        ];
        $titleHeaders = array_keys($titleStories);
        foreach ($facilityIds as $facilityId) {
            for ($t = 0; $t < 3; $t++) {
                $title_header = fake()->randomElement($titleHeaders);
                Testimonial::create([
                    'facility_id' => $facilityId,
                    'name' => fake()->name(),
                    'title' => fake()->randomElement(['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.', '']),
                    'title_header' => $title_header,
                    'quote' => $titleQuotes[$title_header],
                    'story' => $titleStories[$title_header],
                    'relationship' => fake()->randomElement([
                        'Current Patient', 'Former Patient', 'Patient Family Member', 'Visitor',
                        'Current Staff', 'Former Staff', 'Healthcare Professional', 'Volunteer',
                        'Community Member', 'other'
                    ]),
                    'rating' => fake()->numberBetween(4, 5),
                    'is_active' => true,
                    'is_featured' => fake()->boolean(20),
                    'photo_url' => 'https://randomuser.me/api/portraits/' . (rand(0, 1) ? 'women' : 'men') . '/' . rand(10, 90) . '.jpg',
                ]);
            }
        }
    }
}
