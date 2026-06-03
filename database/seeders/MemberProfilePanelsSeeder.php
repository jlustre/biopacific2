<?php

namespace Database\Seeders;

use App\Models\MemberProfileExpiringItem;
use App\Models\MemberProfileRecognition;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Support\ResolvesPortalSeedData;
use Illuminate\Database\Seeder;

class MemberProfilePanelsSeeder extends Seeder
{
    use ResolvesPortalSeedData;

    public function run(): void
    {
        $today = Carbon::today();

        $panelsByRole = [
            'facility_admin' => [
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
            'facility_dsd' => [
                'expiring' => [
                    ['label' => 'BLS Certification', 'days' => 55],
                    ['label' => 'Driver License', 'days' => 100],
                ],
                'recognitions' => [
                    ['icon' => '🏆', 'label' => 'Excellence in Staff Development', 'kind' => 'award', 'recognized_on' => '2025-06-01'],
                ],
            ],
            'rdhr' => [
                'expiring' => [
                    ['label' => 'CPR Certification', 'days' => 75],
                ],
                'recognitions' => [
                    ['icon' => '📜', 'label' => 'Regional Leadership Certificate', 'kind' => 'certification', 'recognized_on' => '2022-04-10'],
                ],
            ],
            'don' => [
                'expiring' => [
                    ['label' => 'BLS Certification', 'days' => 60],
                ],
                'recognitions' => [
                    ['icon' => '🏆', 'label' => 'Nursing Leadership Award', 'kind' => 'award', 'recognized_on' => '2025-01-10'],
                ],
            ],
        ];

        foreach ($panelsByRole as $roleKey => $config) {
            $user = $this->demoUser($roleKey);
            if (!$user) {
                continue;
            }

            $this->seedPanelsForUser($user, $config, $today);
        }
    }

    /**
     * @param  array{expiring: list<array<string, mixed>>, recognitions: list<array<string, mixed>>}  $config
     */
    protected function seedPanelsForUser(User $user, array $config, Carbon $today): void
    {
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
