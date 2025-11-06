<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class DefaultFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFaqs = [
            // Admission & General Information
            [
                'question' => 'What are your visiting hours?',
                'answer' => 'Our visiting hours are [visiting hours]. However, we understand that family time is important, so we work with families to accommodate special circumstances. Please check with the front desk for any current restrictions or special arrangements.',
                'category' => 'General Information',
                'icon' => 'fas fa-clock',
                'sort_order' => 1
            ],
            [
                'question' => 'How do I apply for admission?',
                'answer' => 'To begin the admission process, please contact our admissions coordinator at [phone number]. We will schedule a tour, assess care needs, discuss insurance and payment options, and guide you through all necessary paperwork. The process typically takes 1-3 days depending on the urgency of care needed.',
                'category' => 'Admission',
                'icon' => 'fas fa-clipboard-check',
                'sort_order' => 2
            ],
            [
                'question' => 'What insurance plans do you accept?',
                'answer' => 'We accept Medicare, Medicaid, and most major private insurance plans. Our financial coordinator will work with you to verify benefits and explain coverage details. We also offer private pay options and can discuss payment plans if needed.',
                'category' => 'Insurance & Billing',
                'icon' => 'fas fa-file-invoice-dollar',
                'sort_order' => 3
            ],
            
            // Care Services
            [
                'question' => 'What levels of care do you provide?',
                'answer' => 'We provide comprehensive care including skilled nursing, rehabilitation therapy (physical, occupational, and speech), memory care, respite care, and long-term care. Our team creates individualized care plans to meet each resident\'s specific needs and goals.',
                'category' => 'Care Services',
                'icon' => 'fas fa-heart',
                'sort_order' => 4
            ],
            [
                'question' => 'Do you have rehabilitation services?',
                'answer' => 'Yes, we offer comprehensive rehabilitation services including physical therapy, occupational therapy, and speech therapy. Our licensed therapists work with residents to help them regain independence and improve their quality of life through personalized treatment plans.',
                'category' => 'Care Services',
                'icon' => 'fas fa-dumbbell',
                'sort_order' => 5
            ],
            [
                'question' => 'How are medications managed?',
                'answer' => 'All medications are managed by our licensed nursing staff. We maintain detailed medication records, coordinate with physicians for any changes, and ensure medications are administered safely and on time. Families are kept informed of any medication adjustments.',
                'category' => 'Services',
                'icon' => 'fas fa-pills',
                'sort_order' => 6
            ],
            
            // Facility Services
            [
                'question' => 'What services do you offer?',
                'answer' => 'Our facility features comfortable private and semi-private rooms, common areas for socializing, a dining room with nutritious meals, activity rooms, outdoor spaces, and 24/7 nursing care. We also offer Wi-Fi, cable TV, and laundry services.',
                'category' => 'Services',
                'icon' => 'fas fa-building',
                'sort_order' => 7
            ],
            [
                'question' => 'What activities are available for residents?',
                'answer' => 'We offer a variety of engaging activities including arts and crafts, music therapy, exercise programs, social hours, religious services, educational programs, and special events. Our activity coordinator works to ensure programming meets diverse interests and abilities.',
                'category' => 'Activities',
                'icon' => 'fas fa-palette',
                'sort_order' => 8
            ],
            [
                'question' => 'What meals are provided?',
                'answer' => 'We provide three nutritious meals daily plus snacks, prepared by our dietary staff. Our registered dietitian ensures meals meet nutritional needs and dietary restrictions. We accommodate special diets and cultural preferences whenever possible.',
                'category' => 'Dining',
                'icon' => 'fas fa-utensils',
                'sort_order' => 9
            ],
            
            // Policies & Procedures
            [
                'question' => 'What is your COVID-19 policy?',
                'answer' => 'We follow all CDC and state health department guidelines for infection control. This includes health screenings, vaccination requirements, appropriate use of personal protective equipment, and flexible visitation policies that prioritize both safety and family connection.',
                'category' => 'Health & Safety',
                'icon' => 'fas fa-shield-alt',
                'sort_order' => 10
            ],
            [
                'question' => 'Can residents bring personal belongings?',
                'answer' => 'Yes, residents are encouraged to bring personal items to make their room feel like home. This can include furniture (space permitting), photographs, decorations, clothing, and other meaningful items. We recommend labeling all items and discussing valuable items with staff.',
                'category' => 'General Information',
                'icon' => 'fas fa-home',
                'sort_order' => 11
            ],
            [
                'question' => 'How do you handle emergencies?',
                'answer' => 'We have 24/7 nursing staff trained in emergency procedures, direct communication with local emergency services, and comprehensive emergency plans. Families are notified immediately of any significant changes in a resident\'s condition or emergency situations.',
                'category' => 'Health & Safety',
                'icon' => 'fas fa-ambulance',
                'sort_order' => 12
            ],
            [
                'question' => 'How can I contact the facility?',
                'answer' => 'You can reach us at [phone number] or email us at [email]. We are located at [facility address]. Our main phone lines are staffed during [visiting hours], but emergency calls are answered 24/7.',
                'category' => 'Contact Information',
                'icon' => 'fas fa-phone',
                'sort_order' => 13
            ],
            [
                'question' => 'How many residents do you accommodate?',
                'answer' => '[Facility name] is licensed for [bed count] beds. This allows us to maintain a comfortable resident-to-staff ratio while providing personalized care to each individual in our community.',
                'category' => 'General Information',
                'icon' => 'fas fa-bed',
                'sort_order' => 14
            ]
        ];

        foreach ($defaultFaqs as $faq) {
            Faq::create([
                'facility_id' => null, // Default FAQs have no specific facility
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'category' => $faq['category'],
                'icon' => $faq['icon'],
                'is_active' => true,
                'is_featured' => false,
                'is_default' => true,
                'sort_order' => $faq['sort_order']
            ]);
        }
    }
}
