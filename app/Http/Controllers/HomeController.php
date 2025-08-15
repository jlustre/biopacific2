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
            'years'=> '25',
            'maps' => 'https://www.google.com/maps/embed/v1/place?key=AIzaSyBPOyvtiKxDKEGDNaL8k8hxnCh_42iNDDk&q=13484+San+Pablo+Avenue,+San+Pablo,+CA+94806',
            'social' => ['facebook' => '#', 'linkedin' => '#', 'youtube' => '#'],
            'meta_description' => 'Nursing home in California offering skilled nursing, rehab, memory care, long-term care, and more.',
            'hero_main_heading' => 'Compassionate care, clinical excellence.',
            'hero_sub_heading' => 'Personalized, compassionate care with evidence-based practices in a warm, family-centered environment.',
        ];
        // $colors = ['primary'=>'#2563EB','secondary'=>'#1E293B','accent'=>'#F59E0B']; //navy blue
        // $colors = ['primary'=>'#0EA5E9','secondary'=>'#155E75','accent'=>'#F59E0B']; //light blue
        $colors = ['primary'=>'#059669','secondary'=>'#064E3B','accent'=>'#FACC15']; //dark green
        // $colors = ['primary'=>'#0EA5E9','secondary'=>'#0369A1','accent'=>'#FBBF24']; //light blue
        // $colors = ['primary'=>'#10B981','secondary'=>'#065F46','accent'=>'#FDBA74']; //light green
        // $colors = ['primary'=>'#22D3EE','secondary'=>'#0E7490','accent'=>'#F472B6']; //cyan
        // $colors = ['primary'=>'#7C3AED','secondary'=>'#4C1D95','accent'=>'#60A5FA']; //purple
        // $colors = ['primary'=>'#EF4444','secondary'=>'#991B1B','accent'=>'#F59E0B']; // red
        // $colors = ['primary'=>'#F59E42','secondary'=>'#B45309','accent'=>'#2563EB']; // orange
        // $colors = ['primary'=>'#FBBF24','secondary'=>'#92400E','accent'=>'#2563EB']; // yellow
        // $colors = ['primary'=>'#22C55E','secondary'=>'#166534','accent'=>'#F472B6']; // emerald
        // $colors = ['primary'=>'#3B82F6','secondary'=>'#1E40AF','accent'=>'#F59E0B']; // blue
        // $colors = ['primary'=>'#6366F1','secondary'=>'#3730A3','accent'=>'#FBBF24']; // indigo
        // $colors = ['primary'=>'#A21CAF','secondary'=>'#581C87','accent'=>'#F59E0B']; // violet
        $colors = ['primary'=>'#F43F5E','secondary'=>'#BE185D','accent'=>'#FBBF24']; // pink
        // $colors = ['primary'=>'#E11D48','secondary'=>'#881337','accent'=>'#F59E0B']; // rose
        // $colors = ['primary'=>'#14B8A6','secondary'=>'#134E4A','accent'=>'#FBBF24']; // teal
        // $colors = ['primary'=>'#16A34A','secondary'=>'#14532D','accent'=>'#F59E0B']; //green

        return view('welcome', compact('facility','colors'));
    }
}
