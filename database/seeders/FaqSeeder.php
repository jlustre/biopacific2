<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void 
    {
        Faq::insert([
            [
                'question' => 'What insurances do you accept?',
                'answer' => 'We accept Medicare, Medicaid, and many private insurance plans including Blue Cross Blue Shield, Aetna, Humana, and others. Contact our billing department for specific plan verification and coverage details.',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 2.3-.72 4.396-1.888 6.168-3.38.522-.439 1.022-.9 1.5-1.38.145-.146.288-.294.43-.444A11.956 11.956 0 0021 9a12.02 12.02 0 00.382-3.016z',
                'category' => 'insurance', 'is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Do you accept long-term care insurance?',
                'answer' => 'Yes, we accept most long-term care insurance policies. Please contact our admissions team for details and assistance with claims.',
                'icon' => 'M5 13l4 4L19 7',
                'category' => 'insurance', 'is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'How do I verify my insurance coverage?',
                'answer' => 'You can call our billing office or bring your insurance card during your visit. We will verify your coverage and explain any out-of-pocket costs.',
                'icon' => 'M9 17v-6h6v6',
                'category' => 'insurance', 'is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Can families visit daily?',
                'answer' => 'Yes, families are welcome to visit daily during our visiting hours (9:00 AM - 7:00 PM). Please check in at the front desk and follow our visitor guidelines. We also offer extended hours for special circumstances.',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'category' => 'visits', 'is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Are children allowed to visit?',
                'answer' => 'Children are welcome to visit with adult supervision. Please ensure they follow all facility guidelines for safety and respect.',
                'icon' => 'M12 4v16m8-8H4',
                'category' => 'visits', 'is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Can I visit outside regular hours?',
                'answer' => 'Special arrangements for visits outside regular hours can be made with prior approval from the facility administrator.',
                'icon' => 'M15 12h6m-6 0H3',
                'category' => 'visits','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Do you offer specialized diets?',
                'answer' => 'Yes, our registered dietitians work closely with residents and families to create personalized meal plans accommodating dietary restrictions, allergies, cultural preferences, and medical requirements such as diabetic, low-sodium, or pureed diets.',
                'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zM3 9a2 2 0 012-2h14a2 2 0 012 2v.01A2 2 0 0119 11H5a2 2 0 01-2-1.99V9z',
                'category' => 'dining','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Can I request vegetarian or vegan meals?',
                'answer' => 'Yes, we offer vegetarian, vegan, and other special meal options. Please inform our dietary team of your preferences.',
                'icon' => 'M5 8h14M5 16h14',
                'category' => 'dining','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'How are meals served?',
                'answer' => 'Meals are served in our dining room or can be delivered to resident rooms upon request. Family members may join with advance notice.',
                'icon' => 'M4 6h16M4 18h16',
                'category' => 'dining','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'What activities and programs do you offer?',
                'answer' => 'We provide a comprehensive activities program including physical therapy, arts and crafts, music therapy, social events, religious services, educational programs, and outdoor activities. Our activity calendar is updated monthly.',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
                'category' => 'activities','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Are there outdoor activities?',
                'answer' => 'Yes, we offer outdoor activities such as gardening, walking clubs, and picnics when weather permits.',
                'icon' => 'M3 17v-2a4 4 0 014-4h10a4 4 0 014 4v2',
                'category' => 'activities','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Can family members participate in activities?',
                'answer' => 'Family members are welcome to join many of our activities. Please check with our activities coordinator for details.',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857',
                'category' => 'activities','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'What safety measures are in place?',
                'answer' => 'Our facility follows strict infection control protocols, regular staff training, and 24/7 security monitoring to ensure resident safety and well-being.',
                'icon' => 'M12 11c0-1.104.896-2 2-2s2 .896 2 2-.896 2-2 2-2-.896-2-2zm0 0V7m0 4v4m0 0h4m-4 0H8',
                'category' => 'safety','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Is there 24/7 security?',
                'answer' => 'Yes, our facility is monitored by security staff and surveillance systems 24/7.',
                'icon' => 'M9 12l2 2 4-4',
                'category' => 'safety','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'How do you handle emergencies?',
                'answer' => 'We have emergency protocols and staff trained in first aid, CPR, and evacuation procedures.',
                'icon' => 'M12 4v16',
                'category' => 'safety','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'How do I schedule a tour?',
                'answer' => 'You can schedule a tour by clicking our “Book a Tour” button, calling our front desk or visiting us in person. We offer guided tours Monday through Saturday and can accommodate special scheduling needs.',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'category' => 'tours','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Can I book a tour online?',
                'answer' => 'Yes, you can book a tour directly on our website or by calling our front desk.',
                'icon' => 'M15 12h6m-6 0H3',
                'category' => 'tours','is_default' => true, 'is_global' => true
            ],
            [
                'question' => 'Are virtual tours available?',
                'answer' => 'We offer virtual tours for families who cannot visit in person. Contact us to schedule a virtual walkthrough.',
                'icon' => 'M4 6v16h16V6',
                'category' => 'tours','is_default' => true, 'is_global' => true
            ],
        ]);
        
        // Example: make only a couple facility-specific
        $facilityFaqs = [
            [
                'question' => 'Is there a local shuttle service?',
                'answer' => 'Some facilities offer local shuttle service for residents. Please check with your facility administrator.',
                'icon' => 'M5 13l4 4L19 7',
                'category' => 'transport', 'is_default' => false, 'is_global' => false
            ],
            [
                'question' => 'Are pets allowed in this facility?',
                'answer' => 'Pet policies vary by facility. Please contact your facility for details.',
                'icon' => 'M12 4v16m8-8H4',
                'category' => 'visits', 'is_default' => false, 'is_global' => false
            ],
        ];


        // Insert facility-specific FAQs and assign to random facilities
        $facilities = \App\Models\Facility::all();
        foreach ($facilityFaqs as $faqData) {
            $faq = \App\Models\Faq::create($faqData);
            $randomFacilities = $facilities->random(min(2, $facilities->count()));
            foreach ($randomFacilities as $facility) {
                $facility->faqs()->attach($faq->id);
            }
        }
        
    }
}