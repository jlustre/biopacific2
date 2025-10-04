<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSchemesTableSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            [ 'name' => 'Bright Horizon', 'primary_color' => '#EAB308', 'secondary_color' => '#713F12', 'accent_color' => '#3B82F6' ],
            [ 'name' => 'Calm Neutral', 'primary_color' => '#64748B', 'secondary_color' => '#1E293B', 'accent_color' => '#10B981' ],
            [ 'name' => 'Caregiver Warmth', 'primary_color' => '#F97316', 'secondary_color' => '#7C2D12', 'accent_color' => '#10B981' ],
            [ 'name' => 'Caring Peach', 'primary_color' => '#FDBA74', 'secondary_color' => '#9A3412', 'accent_color' => '#2563EB' ],
            [ 'name' => 'Caring Rose', 'primary_color' => '#E11D48', 'secondary_color' => '#881337', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Classic Healthcare Blue', 'primary_color' => '#2563EB', 'secondary_color' => '#1E3A8A', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Compassion Sky', 'primary_color' => '#38BDF8', 'secondary_color' => '#1E3A8A', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Compassionate Clay', 'primary_color' => '#EA580C', 'secondary_color' => '#7C2D12', 'accent_color' => '#22C55E' ],
            [ 'name' => 'Compassionate Plum', 'primary_color' => '#9333EA', 'secondary_color' => '#581C87', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Elder Care Calm', 'primary_color' => '#4ADE80', 'secondary_color' => '#166534', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Faithful Navy', 'primary_color' => '#1E40AF', 'secondary_color' => '#1E293B', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Gentle Dawn', 'primary_color' => '#A3E635', 'secondary_color' => '#365314', 'accent_color' => '#0EA5E9' ],
            [ 'name' => 'Gentle Ocean', 'primary_color' => '#3B82F6', 'secondary_color' => '#1E3A8A', 'accent_color' => '#F472B6' ],
            [ 'name' => 'Healing Earth', 'primary_color' => '#65A30D', 'secondary_color' => '#365314', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Healing Green Harmony', 'primary_color' => '#10B981', 'secondary_color' => '#065F46', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Healing Rose Gold', 'primary_color' => '#F43F5E', 'secondary_color' => '#9F1239', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Harmony Violet', 'primary_color' => '#8B5CF6', 'secondary_color' => '#4C1D95', 'accent_color' => '#FACC15' ],
            [ 'name' => 'Hopeful Spring', 'primary_color' => '#22C55E', 'secondary_color' => '#166534', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Light of Hope', 'primary_color' => '#FDE047', 'secondary_color' => '#78350F', 'accent_color' => '#0EA5E9' ],
            [ 'name' => 'Nurturing Rose', 'primary_color' => '#E11D48', 'secondary_color' => '#881337', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Peaceful Mint', 'primary_color' => '#34D399', 'secondary_color' => '#064E3B', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Safe Harbor', 'primary_color' => '#2563EB', 'secondary_color' => '#111827', 'accent_color' => '#EF4444' ],
            [ 'name' => 'Serene Lavender', 'primary_color' => '#7C3AED', 'secondary_color' => '#4C1D95', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Serenity Blue', 'primary_color' => '#0C4A6E', 'secondary_color' => '#1E293B', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Soothing Aqua', 'primary_color' => '#06B6D4', 'secondary_color' => '#164E63', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Sunny Care', 'primary_color' => '#FACC15', 'secondary_color' => '#854D0E', 'accent_color' => '#2563EB' ],
            [ 'name' => 'Trustworthy Teal', 'primary_color' => '#14B8A6', 'secondary_color' => '#134E4A', 'accent_color' => '#FB923C' ],
            [ 'name' => 'Warm Caring Coral', 'primary_color' => '#F87171', 'secondary_color' => '#7F1D1D', 'accent_color' => '#FACC15' ],
            [ 'name' => 'Warm Embrace', 'primary_color' => '#FB7185', 'secondary_color' => '#881337', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Wellness Green', 'primary_color' => '#22C55E', 'secondary_color' => '#064E3B', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Zen Garden', 'primary_color' => '#10B981', 'secondary_color' => '#064E3B', 'accent_color' => '#FBBF24' ],
        ];

        // usort($schemes, function($a, $b) {
        //     return strcmp($a['name'], $b['name']);
        // });
        DB::table('color_schemes')->insert($schemes);

        
    }
}
