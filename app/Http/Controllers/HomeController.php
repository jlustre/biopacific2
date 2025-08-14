<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $facility = [
            'name' => 'Vale Health Care Center',
            'tagline' => 'Compassionate care, clinical excellence.',
            'address' => '13484 San Pablo Avenue, San Pablo, CA 94806',
            'phone' => '(510) 232-5945',
            'email' => 'info@valehealthcarecenter.com',
            'hours' => 'Daily 9:00 AM – 7:00 PM',
            'maps' => 'https://www.google.com/maps?q=13484+San+Pablo+Avenue,+San+Pablo,+CA+94806',
            'social' => ['facebook' => '#', 'linkedin' => '#', 'youtube' => '#'],
            'meta_description' => 'Nursing home in California offering skilled nursing, rehab, memory care, long-term care, and more.',
            'hero_main_heading' => 'Compassionate care, clinical excellence.',
            'hero_sub_heading' => 'Personalized, compassionate care with evidence-based practices in a warm, family-centered environment.',
        ];
        $colors = ['primary'=>'#2563EB','secondary'=>'#1E293B','accent'=>'#F59E0B'];
        // $colors = ['primary'=>'#0EA5E9','secondary'=>'#155E75','accent'=>'#F59E0B'];

        return view('welcome', compact('facility','colors'));
    }
}
