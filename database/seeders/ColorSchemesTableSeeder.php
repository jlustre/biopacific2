<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSchemesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('color_schemes')->insert([
            [ 'name' => 'Classic Healthcare Blue', 'primary_color' => '#2563EB', 'secondary_color' => '#1E3A8A', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Soothing Green', 'primary_color' => '#059669', 'secondary_color' => '#064E3B', 'accent_color' => '#FACC15' ],
            [ 'name' => 'Sky Calm', 'primary_color' => '#0EA5E9', 'secondary_color' => '#0369A1', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Gentle Violet', 'primary_color' => '#7C3AED', 'secondary_color' => '#4C1D95', 'accent_color' => '#60A5FA' ],
            [ 'name' => 'Healing Green', 'primary_color' => '#16A34A', 'secondary_color' => '#14532D', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Warm Rose', 'primary_color' => '#DB2777', 'secondary_color' => '#831843', 'accent_color' => '#38BDF8' ],
            [ 'name' => 'Fresh Teal', 'primary_color' => '#14B8A6', 'secondary_color' => '#115E59', 'accent_color' => '#F472B6' ],
            [ 'name' => 'Sunset Orange', 'primary_color' => '#F97316', 'secondary_color' => '#9A3412', 'accent_color' => '#22C55E' ],
            [ 'name' => 'Desert Rose', 'primary_color' => '#E11D48', 'secondary_color' => '#881337', 'accent_color' => '#06B6D4' ],
            [ 'name' => 'Calm Slate', 'primary_color' => '#64748B', 'secondary_color' => '#0F172A', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Soft Indigo', 'primary_color' => '#4338CA', 'secondary_color' => '#1E1B4B', 'accent_color' => '#FDE047' ],
            [ 'name' => 'Clean White & Green', 'primary_color' => '#22C55E', 'secondary_color' => '#15803D', 'accent_color' => '#EAB308' ],
            [ 'name' => 'Gentle Lavender', 'primary_color' => '#8B5CF6', 'secondary_color' => '#5B21B6', 'accent_color' => '#F472B6' ],
            [ 'name' => 'Peaceful Aqua', 'primary_color' => '#06B6D4', 'secondary_color' => '#155E75', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Warm Neutral', 'primary_color' => '#78716C', 'secondary_color' => '#292524', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Classic Navy', 'primary_color' => '#1E40AF', 'secondary_color' => '#172554', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Soft Coral', 'primary_color' => '#F43F5E', 'secondary_color' => '#9F1239', 'accent_color' => '#3B82F6' ],
            [ 'name' => 'Calm Olive', 'primary_color' => '#65A30D', 'secondary_color' => '#365314', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Tranquil Turquoise', 'primary_color' => '#0891B2', 'secondary_color' => '#164E63', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Neutral & Blue', 'primary_color' => '#334155', 'secondary_color' => '#1E293B', 'accent_color' => '#3B82F6' ],
            [ 'name' => 'Caring Mint', 'primary_color' => '#34D399', 'secondary_color' => '#047857', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Warm Sunshine', 'primary_color' => '#FBBF24', 'secondary_color' => '#92400E', 'accent_color' => '#2563EB' ],
            [ 'name' => 'Gentle Peach', 'primary_color' => '#FB7185', 'secondary_color' => '#9F1239', 'accent_color' => '#3B82F6' ],
            [ 'name' => 'Cool Forest', 'primary_color' => '#166534', 'secondary_color' => '#052E16', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Ocean Calm', 'primary_color' => '#2563EB', 'secondary_color' => '#1D4ED8', 'accent_color' => '#FCD34D' ],
            [ 'name' => 'Modern Neutral', 'primary_color' => '#475569', 'secondary_color' => '#0F172A', 'accent_color' => '#3B82F6' ],
            [ 'name' => 'Gentle Sand', 'primary_color' => '#D97706', 'secondary_color' => '#92400E', 'accent_color' => '#22C55E' ],
            [ 'name' => 'Calm Lilac', 'primary_color' => '#C084FC', 'secondary_color' => '#6B21A8', 'accent_color' => '#22D3EE' ],
            [ 'name' => 'Trust Blue-Green', 'primary_color' => '#0284C7', 'secondary_color' => '#075985', 'accent_color' => '#10B981' ],
            [ 'name' => 'Earthy Comfort', 'primary_color' => '#92400E', 'secondary_color' => '#78350F', 'accent_color' => '#34D399' ],
            [ 'name' => 'Serene Plum', 'primary_color' => '#9D174D', 'secondary_color' => '#581C3B', 'accent_color' => '#FDE68A' ],
            [ 'name' => 'Bright Citrus', 'primary_color' => '#F59E42', 'secondary_color' => '#B45309', 'accent_color' => '#10B981' ],
            [ 'name' => 'Calm Moss', 'primary_color' => '#A3E635', 'secondary_color' => '#365314', 'accent_color' => '#F472B6' ],
            [ 'name' => 'Deep Sapphire', 'primary_color' => '#0F3460', 'secondary_color' => '#16213E', 'accent_color' => '#E94560' ],
            [ 'name' => 'Gentle Mint', 'primary_color' => '#A7F3D0', 'secondary_color' => '#065F46', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Warm Clay', 'primary_color' => '#B45309', 'secondary_color' => '#78350F', 'accent_color' => '#FDE047' ],
            [ 'name' => 'Soft Lemon', 'primary_color' => '#FDE047', 'secondary_color' => '#CA8A04', 'accent_color' => '#2563EB' ],
            [ 'name' => 'Calm Charcoal', 'primary_color' => '#374151', 'secondary_color' => '#111827', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Gentle Sky', 'primary_color' => '#7DD3FC', 'secondary_color' => '#0369A1', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Fresh Lime', 'primary_color' => '#84CC16', 'secondary_color' => '#365314', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Warm Berry', 'primary_color' => '#F43F5E', 'secondary_color' => '#9D174D', 'accent_color' => '#FDE047' ],
            [ 'name' => 'Calm Stone', 'primary_color' => '#78716C', 'secondary_color' => '#292524', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Gentle Aqua', 'primary_color' => '#67E8F9', 'secondary_color' => '#155E75', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Fresh Coral', 'primary_color' => '#FB7185', 'secondary_color' => '#9F1239', 'accent_color' => '#FBBF24' ],
            [ 'name' => 'Warm Olive', 'primary_color' => '#A3E635', 'secondary_color' => '#365314', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Calm Peach', 'primary_color' => '#FDBA74', 'secondary_color' => '#B45309', 'accent_color' => '#2563EB' ],
            [ 'name' => 'Gentle Blush', 'primary_color' => '#FCA5A5', 'secondary_color' => '#B91C1C', 'accent_color' => '#F59E0B' ],
            [ 'name' => 'Fresh Sky', 'primary_color' => '#0EA5E9', 'secondary_color' => '#0369A1', 'accent_color' => '#FDE047' ],
            [ 'name' => 'Warm Sand', 'primary_color' => '#FCD34D', 'secondary_color' => '#92400E', 'accent_color' => '#22C55E' ],
        ]);
    }
}
