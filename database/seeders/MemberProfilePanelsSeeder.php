<?php

namespace Database\Seeders;

use App\Models\MemberProfileExpiringItem;
use App\Models\MemberProfileRecognition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MemberProfilePanelsSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $panelsByEmail = [
            'facilityadmin@biopacific.com' => [
                'expiring' => [
                    ['label' => 'CPR Certification', 'days' => 45],
                    ['label' => 'Driver License', 'days' => 90],
                    ['label' => 'Professional License', 'days' => 180],
                ],
                'recognitions' => [
                    ['icon' => '🏆', 'label' => 'Nurse of the Year 2024', 'kind' => 'award', 'recognized_on' => '2024-12-01'],
                    ['icon' => '📜', 'label' => 'Advanced Cardiac Life Support', 'kind' => 'certification', 'recognized_on' => '2023-08-15'],
                ],
            ],
            'facilitydsd@biopacific.com' => [
                'expiring' => [
                    ['label' => 'BLS Certification', 'days' => 55],
                    ['label' => 'Driver License', 'days' => 100],
                ],
                'recognitions' => [
                    ['icon' => '🏆', 'label' => 'Excellence in Staff Development', 'kind' => 'award', 'recognized_on' => '2025-06-01'],
                ],
            ],
            'rdhr@biopacific.com' => [
                'expiring' => [
                    ['label' => 'CPR Certification', 'days' => 75],
                ],
                'recognitions' => [
                    ['icon' => '📜', 'label' => 'Regional Leadership Certificate', 'kind' => 'certification', 'recognized_on' => '2022-04-10'],
                ],
            ],
        ];

        foreach ($panelsByEmail as $email => $config) {
            $user = User::query()->where('email', $email)->first();
            if (!$user) {
                continue;
            }

            MemberProfileExpiringItem::query()->where('user_id', $user->id)->delete();
            MemberProfileRecognition::query()->where('user_id', $user->id)->delete();

            foreach ($config['expiring'] as $index => $row) {
                MemberProfileExpiringItem::create([
                    'user_id' => $user->id,
                    'label' => $row['label'],
                    'expires_at' => $today->copy()->addDays($row['days']),
                    'sort_order' => $index,
                ]);
            }

            foreach ($config['recognitions'] as $index => $row) {
                MemberProfileRecognition::create([
                    'user_id' => $user->id,
                    'icon' => $row['icon'],
                    'label' => $row['label'],
                    'kind' => $row['kind'],
                    'recognized_on' => $row['recognized_on'] ?? null,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }
}
