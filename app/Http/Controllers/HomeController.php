<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // The facility is automatically resolved by ResolveTenant middleware
        // and shared with all views. No need to manually pass it.

        // For backward compatibility, if no current facility is found,
        // fall back to a default facility for development
        // if (!app()->bound('current_facility')) {
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
                'primary_color' => '#059669',
                'secondary_color' => '#064E3B',
                'accent_color' => '#FACC15'
            ];

            $colors = ['primary'=>'#059669','secondary'=>'#064E3B','accent'=>'#FACC15'];

        //     return view('welcome', compact('facility','colors'));
        // }

        // If we have a current facility (normal case), just return the view
        // The facility data is already shared via the middleware
        return view('welcome', compact('facility', 'colors'));
    }
}
